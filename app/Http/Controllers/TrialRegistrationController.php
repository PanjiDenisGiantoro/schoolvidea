<?php

namespace App\Http\Controllers;

use App\Mail\TrialRegistrationConfirmation;
use App\Mail\TrialRegistrationConfirmationnext;
use App\Models\Roles;
use App\Models\Roles_petugas;
use App\Models\TrialRegistration;
use App\Models\Tipeunit;
use App\Models\Unit;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class TrialRegistrationController extends Controller
{
    public function showForm()
    {
        // Mengambil data tipe unit dan yayasan untuk dropdown
        $tipeunit = Tipeunit::all();
        $yayasans = Yayasan::all();
        return view('registerpublic', compact('tipeunit', 'yayasans'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'school_name'    => 'required|string|max:150',
                'npsn'           => 'required|string|max:30',
                'address'        => 'required|string|max:255',
                'full_name'      => 'required|string|max:150',
                'email'          => 'required|email|max:150|unique:trial_registrations,email',
                'no_hp'          => 'required|string|max:30',
                'agree'          => 'accepted',
                'tipe_unit_id'   => 'required|integer',
                'yayasan_id'     => 'nullable',
            ]);

            $yayasan_id = $data['yayasan_id'] ?? null;
            $trialRegistration =  TrialRegistration::create([
                'school_name'    => $data['school_name'],
                'npsn'           => $data['npsn'],
                'address'        => $data['address'],
                'full_name'      => $data['full_name'],
                'email'          => $data['email'],
                'no_hp'          => $data['no_hp'],
                'tipe_unit_id'   => $data['tipe_unit_id'],
                'yayasan_id'     => $yayasan_id,
                'status'         => '0',  // Status default
            ]);

            Mail::to($trialRegistration->email)->send(new TrialRegistrationConfirmation($trialRegistration));
            return redirect()
                ->route('landing.registerpublic') // Sesuaikan dengan route yang sesuai
                ->with('success', 'Pendaftaran berhasil! Kami akan segera menghubungi Anda.');

        }catch (Exception $e) {
            Log::error('Error in TrialRegistrationController@store: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
        // Validasi input data

        }
    public function registrationPortal($id)
    {
        $trialRegistration = TrialRegistration::findOrFail($id);
        return view('registration_portal', compact('trialRegistration'));
    }


    public function storePortal(Request $request, $id)
    {
        try {
            DB::beginTransaction();


            $trialUser = TrialRegistration::where('id', $id)->firstOrFail();

            $yayasan_id = null;

            if (!empty($trialUser->yayasan_id)) {
                $codeyayasan = 'Y' . strtoupper(Str::random(7));

                $yayasan = Yayasan::create([
                    'central_code' => $codeyayasan,
                    'nama_yayasan' => $trialUser->yayasan_id,
                    'nama_pimpinan' => $trialUser->full_name ?? '',
                    'no_hp' => $trialUser->no_hp ?? '',
                    'email' => $trialUser->email ?? '',
                    'alamat' => $trialUser->address ?? '',
                    'website' => '',
                    'status' => '0',
                ]);

                $yayasan_id = $yayasan->id; // simpan ID-nya di variabel terpisah
            }

            $centralCode = 'U' . strtoupper(Str::random(7));

            $unit = Unit::create([
                'nama_unit' => $request->school_name,
                'code' => $centralCode,
                'image' => null,
                'no_hp' => $request->no_hp,
                'email' => $request->email,
                'alamat' => $request->address,
                'website' => '',
                'tipe_unit_id' => $trialUser->tipe_unit_id,
                'status' => 1,
                'yayasan_id' => $yayasan_id, // ✅ pakai variabel yang aman
            ]);

            $usercek = User::where('email', $request->email)->first();

            if ($usercek) {
                return redirect()->back()->with('error', 'Email sudah terdaftar!');
            }

            // Buat user admin
            $user = User::create([
                'name' => $request->username,
                'unit_id' => $unit->id,
                'password' => Hash::make('123456'),
                'email' => $request->email,
            ]);

            // Role Spatie
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => 'admin'],
                ['guard_name' => 'web']
            );
            $user->assignRole($roleSpatie->name);

            // Update trial registration
            $trialUser->update([
                'status' => '1',
                'updated_at' => now(),
            ]);

            // Kirim email
            Mail::to($user->email)->send(new TrialRegistrationConfirmationnext($user, $unit));

            DB::commit();

            return redirect()->route('landing.success')
                ->with('success', 'Penyiapan portal berhasil!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error storePortal: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyiapkan portal: ' . $e->getMessage());
        }
    }
}
