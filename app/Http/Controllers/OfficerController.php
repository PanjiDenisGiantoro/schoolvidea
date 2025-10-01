<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Models\Roles_petugas;
use App\Models\Tahun_ajaran;
use App\Models\Unit;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class OfficerController extends Controller
{
    public function index(){
        $officer = User::with('officer.unit','roles')
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id', $unitId);
            })
            ->get();
        $headers = [
            'No',
            'Nama Unit',
            'Nama',
            'Role',
            'NIP',
            'email',
            'VA Petugas',
            'Action'
        ];
        return view('pages.data_master.officer.officer', compact('officer','headers'));
    }
    public function create()
    {

        $units = Unit::isactive()->get();
        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();
        $roles = Roles_petugas::all();
        return view('pages.data_master.officer.officer_create', compact('roles','units','tahun_ajaran','tahun_ajaran_selected'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6', // ✅ tambah confirmed
            'role_id' => 'required|exists:roles,id',       // ✅ validasi harus ada di Spatie roles
            'nip' => 'required|string|max:50|unique:officers,nip',
            'image' => 'nullable|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'unit_id' => 'required|exists:units,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'rfid_no' => 'nullable|string|max:255',

        ]);

        DB::beginTransaction();
        try {
            // 1. Buat user baru
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'rfid_no' => $request->rfid_no,
                'unit_id' => $request->unit_id,
            ]);

            $rolePetugas = Roles_petugas::findOrFail($request->role_id);
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );
            $user->assignRole($roleSpatie->name);

          $officer =   Officer::create([
                'nip'             => $request->nip,
                'iamge'           => $request->image,
                'tempat_lahir'    => $request->tempat_lahir,
                'no_hp'           => $request->no_hp,
                'unit_id'         => $request->unit_id,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'user_id'         => $user->id,
                'role_id'         => $rolePetugas->id, // ✅ foreign key cocok dengan roles_petugas

            ]);
            DB::commit();

            return redirect()->route('officer.index')
                ->with('success', 'Officer berhasil ditambahkan dengan role ' . $roleSpatie->name);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
    public function edit($id)
    {
        $officer = User::with('officer.unit','roles')
            ->where('id', $id)
            ->first();
        $roles   = Roles_petugas::all();
        $units   = Unit::all();
        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();

        return view('pages.data_master.officer.officer_create', compact('officer', 'roles', 'units', 'tahun_ajaran','tahun_ajaran_selected'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id, // unique kecuali user ini
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|exists:roles_petugas,id',
            'nip' => 'required|string|max:50|unique:officers,nip,' . $id,
            'image' => 'nullable|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'unit_id' => 'required|exists:units,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'rfid_no' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $officer = Officer::findOrFail($id);
            $user    = $officer->user;

            // Update user
            $user->update([
                'name'  => $request->name,
                'email' => $request->email,
                'password' => $request->password ? bcrypt($request->password) : $user->password,
                'rfid_no' => $request->rfid_no,
            ]);

            // Update role user
            $rolePetugas = Roles_petugas::findOrFail($request->role_id);
            $roleSpatie  = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );

            $user->syncRoles([$roleSpatie->name]);

            // Update officer
            $officer->update([
                'nip'             => $request->nip,
                'iamge'           => $request->image,
                'tempat_lahir'    => $request->tempat_lahir,
                'no_hp'           => $request->no_hp,
                'unit_id'         => $request->unit_id,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'role_id'         => $rolePetugas->id,
            ]);

            DB::commit();
            return redirect()->route('officer.index')->with('success', 'Officer berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $officer = Officer::findOrFail($id);
            $user    = $officer->user;

            // Hapus officer
            $officer->delete();

            // Hapus user juga (opsional, kalau user tidak boleh ada tanpa officer)
            if ($user) {
                $user->delete();
            }

            DB::commit();
            return redirect()->route('officer.index')->with('success', 'Data officer berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $officer = Officer::with(['user', 'rolePetugas', 'unit', 'tahunAjaran'])->findOrFail($id);
        $show = true;

        return view('pages.data_master.officer.officer_create', compact('officer', 'show'));
    }

}
