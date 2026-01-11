<?php

namespace App\Http\Controllers;

use App\Models\PinOtorisasiUnit;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PinOtorisasiUnitController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('officer.role');

        $role = $user->officer->role->name ?? 'admin';

        abort_if(
            ! in_array($role, ['admin', 'super_admin', 'bendahara', 'kepala_sekolah']),
            403
        );

        if ($role === 'super_admin') {
            $units = Unit::with('pinPembatalan')->get();
        } else {
            abort_if(! $user->officer || ! $user->officer->unit_id, 403);

            $units = Unit::with('pinPembatalan')
                ->where('id', $user->officer->unit_id)
                ->get();
        }

        return view('pages.set_pin.index', compact('units', 'role'));
    }

    public function store(Request $request)
    {
        $user = auth()->user()->load('officer.role');
        $role = $user->officer->role->name ?? 'super_admin';

        /**
         * 🔐 ROLE YANG BOLEH SET PIN
         */
        abort_if(
            ! in_array($role, ['super_admin', 'kepala_sekolah']),
            403
        );

        /**
         * 🔐 PENENTUAN UNIT (FINAL)
         */
        if ($role === 'super_admin') {
            // super_admin BOLEH tanpa officer
            $request->validate([
                'unit_id' => 'required|exists:unit,id',
            ]);

            $unitId = $request->unit_id;
        } else {
            // ADMIN WAJIB punya officer + unit
            abort_if(
                ! $user->officer || ! $user->officer->unit_id,
                403
            );

            $unitId = $user->officer->unit_id;
        }

        /**
         * ✅ VALIDASI PIN (6 DIGIT FIX)
         */
        $request->validate([
            'pin' => 'required|digits:6|confirmed',
            'pin_lama' => 'nullable|digits:6',
            'password_admin' => 'required',
        ]);

        /**
         * 🔐 VERIFIKASI PASSWORD
         */
        if (! Hash::check($request->password_admin, $user->password)) {
            return back()->withErrors([
                'password_admin' => 'Password admin tidak valid',
            ]);
        }

        /**
         * 🔁 CEK PIN LAMA
         */
        $existingPin = PinOtorisasiUnit::where('unit_id', $unitId)
            ->where('type', 'pembatalan')
            ->first();

        if ($existingPin) {
            if (! $request->filled('pin_lama')) {
                return back()->withErrors([
                    'pin_lama' => 'PIN lama wajib diisi',
                ]);
            }

            if (! Hash::check($request->pin_lama, $existingPin->pin_hash)) {
                return back()->withErrors([
                    'pin_lama' => 'PIN lama tidak sesuai',
                ]);
            }
        }

        /**
         * 🚫 BLOK PIN LEMAH
         */
        if (in_array($request->pin, ['000000', '111111', '123456'])) {
            return back()->withErrors([
                'pin' => 'PIN terlalu lemah',
            ]);
        }

        /**
         * 💾 SIMPAN
         */
        PinOtorisasiUnit::updateOrCreate(
            [
                'unit_id' => $unitId,
                'type' => 'pembatalan',
            ],
            [
                'pin_hash' => Hash::make($request->pin),
            ]
        );

        activity()->log(
            ($existingPin ? 'Update' : 'Set') .
            ' PIN Pembatalan | Unit ID: ' . $unitId
        );

        return back()->with('success', 'PIN pembatalan berhasil disimpan');
    }
}
