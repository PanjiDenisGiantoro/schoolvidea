<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AkunUserController extends Controller
{
    public function index(Request $request)
    {

        $units = Unit::all();
        $query = User::with(['units' => function ($q) {
            $q->isactive();
        }]);

        if (Auth::user()->unit_id) {
            $query->where('unit_id', Auth::user()->unit_id);
        } elseif ($request->filled('unit_id')) {
            // Admin user filtering by unit
            $query->where('unit_id', $request->unit_id);
        }


        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhereHas('parent', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('units', function ($q) use ($search) {
                        $q->where('nama_unit', 'like', "%{$search}%");
                    });
            });
        }

        $user = $query->paginate(15)->appends($request->except('page'));

        $headers = [
            'No',
            'Unit',
            'Nama Akun User',
            'Email',
            'Aksi'
        ];

        return view('pages.data_master.akun_user.akun_user', compact('units', 'user', 'headers'));
    }


    public function edit($id)
    {
        $user = User::findOrFail($id);
        $units = Unit::all();

        return view('pages.data_master.akun_user.akun_user_create', [
            'user' => $user,
            'units' => $units,
            'show' => false, // mode edit
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6',
            'rfid_no' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $user->update([
                'unit_id' => $validated['unit_id'],
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => $validated['password']
                    ? bcrypt($validated['password'])
                    : $user->password,
                'rfid_no' => $validated['rfid_no']
            ]);

            DB::commit();

            return redirect()->route('akun-user.index')->with('success', 'Data user berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $units = Unit::all();

        return view('pages.data_master.akun_user.akun_user_create', [
            'user' => $user,
            'units' => $units,
            'show' => true, // readonly mode
        ]);
    }
}
