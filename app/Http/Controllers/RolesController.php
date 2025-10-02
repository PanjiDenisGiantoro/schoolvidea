<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roles_petugas;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function index(){
        $roles = Roles_petugas::get();
        $headers = [
            'No',
            'Nama Role',
            'Action'
        ];

        return view('pages.data_master.roles.roles', compact('roles','headers'));
    }
    public function create()
    {
        return view('pages.data_master.roles.roles_create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        Roles_petugas::create([
            'name' => strtolower($request->name),
            'guard_name' =>'web',
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Data berhasil ditambahkan dengan Tahun Ajaran' . $request->tahun_ajaran);
    }
    public function edit($id)
    {
        $roles = Roles_petugas::findOrFail($id);
        return view('pages.data_master.roles.roles_create', compact('roles'));
    }
    public function update(Request $request, $id)
    {
        $roles = Roles_petugas::findOrFail($id);
        $data = $request->all();
        $data['name'] = strtolower($request->name);
        $roles->update($data);
        return redirect()->route('roles.index')
            ->with('success', 'Data berhasil diupdate');
    }
    public function destroy($id)
    {
        $roles = Roles_petugas::findOrFail($id);
        $roles->delete();
        return redirect()->route('roles.index')
            ->with('success', 'Data berhasil dihapus');
    }
    public function show($id)
    {
        $roles = Roles_petugas::findOrFail($id);
        $show = true;
        return view('pages.data_master.roles.roles_create', compact('roles','show'));
    }
    public function permissions($id)
    {
        // Ambil role berdasarkan ID
        $role = Role::findOrFail($id);

        // Ambil semua permissions
        $permissions = Permission::all();

        // Kelompokkan permissions berdasarkan modul
        $modules = [
            'role' => $permissions->whereIn('name', ['view_role', 'create_role', 'edit_role', 'delete_role']),
            'user' => $permissions->whereIn('name', ['view_user', 'create_user', 'edit_user', 'delete_user']),
            'officer' => $permissions->whereIn('name', ['view_officer', 'create_officer', 'edit_officer', 'delete_officer']),
            'unit' => $permissions->whereIn('name', ['view_unit', 'create_unit', 'edit_unit', 'delete_unit']),
            'tahun_ajaran' => $permissions->whereIn('name', ['view_tahun_ajaran', 'create_tahun_ajaran', 'edit_tahun_ajaran', 'delete_tahun_ajaran']),
            'lembagaunit' => $permissions->whereIn('name', ['view_lembagaunit', 'create_lembagaunit', 'edit_lembagaunit', 'delete_lembagaunit']),
            'kelas' => $permissions->whereIn('name', ['view_kelas', 'create_kelas', 'edit_kelas', 'delete_kelas']),
            'jurusan' => $permissions->whereIn('name', ['view_jurusan', 'create_jurusan', 'edit_jurusan', 'delete_jurusan']),
            'siswa' => $permissions->whereIn('name', ['view_siswa', 'create_siswa', 'edit_siswa', 'delete_siswa']),
            'tagihan' => $permissions->whereIn('name', ['view_tagihan', 'create_tagihan', 'edit_tagihan', 'delete_tagihan']),
            'pembayaran' => $permissions->whereIn('name', ['view_pembayaran', 'create_pembayaran', 'edit_pembayaran', 'delete_pembayaran']),
            'report' => $permissions->whereIn('name', ['view_report', 'generate_report']),
            'potongan' => $permissions->whereIn('name', ['view_potongan', 'create_potongan', 'edit_potongan', 'delete_potongan']),
            'tabungan' => $permissions->whereIn('name', ['view_tabungan', 'create_tabungan', 'edit_tabungan', 'delete_tabungan']),
            'akun' => $permissions->whereIn('name', ['view_akun', 'create_akun', 'edit_akun', 'delete_akun']),
            'setting_akun' => $permissions->whereIn('name', ['view_setting_akun', 'create_setting_akun', 'edit_setting_akun', 'delete_setting_akun']),

        ];

        // Mengambil permissions yang sudah dimiliki oleh role ini
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        // Mengembalikan view dengan data role, permissions, dan modul
        return view('pages.data_master.roles.permissions', compact('role', 'modules', 'rolePermissions'));
    }

    public function updatePermissions(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id', // Pastikan semua permission yang dipilih ada di database
        ]);

        // Ambil role berdasarkan ID
        $role = Role::findOrFail($id);

        // Sinkronkan permissions yang dipilih dengan role
        $role->permissions()->sync($request->permissions);

        // Redirect kembali ke halaman permissions dengan pesan sukses
        return redirect()->route('roles.permissions', $id)->with('success', 'Permissions updated successfully');
    }
}
