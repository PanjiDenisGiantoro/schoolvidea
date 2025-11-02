<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roles_petugas;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function index(Request $request){
        // Build query
        $query = Role::withCount(['permissions', 'users']);

        // Search functionality across all columns
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Paginate results
        $roles = $query->paginate(15)->appends($request->except('page'));

        $headers = [
            'No',
            'Nama Role',
            'Total Permissions',
//            'Total Users',
//            'Action'
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
            'name' => 'required|unique:roles,name',
        ]);

        Role::create([
            'name' => strtolower($request->name),
            'guard_name' => 'web',
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil ditambahkan');
    }

    public function edit($id)
    {
        $roles = Role::findOrFail($id);
        return view('pages.data_master.roles.roles_create', compact('roles'));
    }

    public function update(Request $request, $id)
    {
        $roles = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
        ]);

        $roles->update([
            'name' => strtolower($request->name),
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil diupdate');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deleting superadmin role
        if ($role->name === 'superadmin') {
            return redirect()->route('roles.index')
                ->with('error', 'Tidak dapat menghapus role superadmin');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Role masih digunakan oleh ' . $role->users()->count() . ' user');
        }

        $role->delete();
        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil dihapus');
    }

    public function show($id)
    {
        $roles = Role::with(['permissions', 'users'])->findOrFail($id);
        $show = true;
        return view('pages.data_master.roles.roles_create', compact('roles','show'));
    }
    public function permissions($id)
    {
        // Ambil role berdasarkan ID
        $role = Role::findOrFail($id);

        // Ambil semua permissions
        $allPermissions = Permission::all();

        // Kelompokkan permissions berdasarkan modul dengan label yang lebih friendly
        $permissionGroups = [
            'Dashboard' => [
                'permissions' => $allPermissions->filter(fn($p) => str_contains($p->name, 'dashboard')),
            ],
            'Master Data' => [
                'Role' => $allPermissions->filter(fn($p) => str_contains($p->name, '_role')),
                'User' => $allPermissions->filter(fn($p) => str_contains($p->name, '_user')),
                'Officer' => $allPermissions->filter(fn($p) => str_contains($p->name, '_officer')),
                'Unit' => $allPermissions->filter(fn($p) => str_contains($p->name, '_unit') && !str_contains($p->name, 'lembaga') && !str_contains($p->name, 'tipe')),
                'Tipe Unit' => $allPermissions->filter(fn($p) => str_contains($p->name, '_tipe_unit')),
                'Lembaga Unit' => $allPermissions->filter(fn($p) => str_contains($p->name, '_lembagaunit')),
                'Tahun Ajaran' => $allPermissions->filter(fn($p) => str_contains($p->name, '_tahun_ajaran')),
                'Positions' => $allPermissions->filter(fn($p) => str_contains($p->name, '_positions')),
                'Jurusan' => $allPermissions->filter(fn($p) => str_contains($p->name, '_jurusan')),
                'Kelas' => $allPermissions->filter(fn($p) => str_contains($p->name, '_kelas')),
                'Siswa' => $allPermissions->filter(fn($p) => str_contains($p->name, '_siswa')),
            ],
            'Keuangan' => [
                'Akun' => $allPermissions->filter(fn($p) => str_contains($p->name, '_akun') && !str_contains($p->name, 'setting')),
                'Setting Akun' => $allPermissions->filter(fn($p) => str_contains($p->name, '_setting_akun')),
                'Kategori Tagihan' => $allPermissions->filter(fn($p) => str_contains($p->name, '_kategoritagihan')),
                'Rekening' => $allPermissions->filter(fn($p) => str_contains($p->name, '_rekening')),
            ],
            'Transaksi' => [
                'Tagihan' => $allPermissions->filter(fn($p) => str_contains($p->name, '_tagihan') && !str_contains($p->name, 'kategori')),
                'Pembayaran' => $allPermissions->filter(fn($p) => str_contains($p->name, '_pembayaran')),
                'Potongan' => $allPermissions->filter(fn($p) => str_contains($p->name, '_potongan')),
                'Tabungan' => $allPermissions->filter(fn($p) => str_contains($p->name, '_tabungan')),
            ],
            'Penggajian' => [
                'Payroll Components' => $allPermissions->filter(fn($p) => str_contains($p->name, '_payroll_components')),
                'Payroll Deductions' => $allPermissions->filter(fn($p) => str_contains($p->name, '_payroll_deductions')),
                'Payroll Settings' => $allPermissions->filter(fn($p) => str_contains($p->name, '_payroll_settings')),
            ],
            'Reports & Others' => [
                'Report' => $allPermissions->filter(fn($p) => str_contains($p->name, '_report')),
                'Migrasi' => $allPermissions->filter(fn($p) => str_contains($p->name, '_migrasi')),
                'Activity Log' => $allPermissions->filter(fn($p) => str_contains($p->name, '_activity')),
            ],
        ];

        // Mengambil permissions yang sudah dimiliki oleh role ini
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        // Mengembalikan view dengan data role, permissions, dan modul
        return view('pages.data_master.roles.permissions', compact('role', 'permissionGroups', 'rolePermissions'));
     }

    public function updatePermissions(Request $request, $id)
    {
        // Ambil role berdasarkan ID
        $role = Role::findOrFail($id);

        // Prevent updating superadmin permissions (for safety)
        if ($role->name === 'superadmin') {
            return redirect()->route('roles.permissions', $id)
                ->with('error', 'Tidak dapat mengubah permissions role superadmin');
        }

        // Validasi input
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Sinkronkan permissions yang dipilih dengan role
        // Jika tidak ada permissions yang dipilih, akan clear semua permissions
        $role->syncPermissions($request->permissions ?? []);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Redirect kembali ke halaman permissions dengan pesan sukses
        return redirect()->route('roles.permissions', $id)
            ->with('success', 'Permissions berhasil diupdate untuk role ' . $role->name);
    }

    /**
     * Show users with this role
     */
    public function users($id)
    {
        $role = Role::with('users')->findOrFail($id);
        $users = $role->users;
        $allUsers = User::doesntHave('roles')->orWhereHas('roles', function($q) use ($id) {
            $q->where('roles.id', '!=', $id);
        })->get();

        return view('pages.data_master.roles.users', compact('role', 'users', 'allUsers'));
    }

    /**
     * Assign role to user
     */
    public function assignUser(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $role = Role::findOrFail($id);
        $user = User::findOrFail($request->user_id);

        // Remove existing roles and assign new one
        $user->syncRoles([$role->name]);

        return redirect()->route('roles.users', $id)
            ->with('success', 'User berhasil di-assign ke role ' . $role->name);
    }

    /**
     * Remove user from role
     */
    public function removeUser(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $role = Role::findOrFail($id);
        $user = User::findOrFail($request->user_id);

        $user->removeRole($role->name);

        return redirect()->route('roles.users', $id)
            ->with('success', 'User berhasil diremove dari role ' . $role->name);
    }

    /**
     * Clone/duplicate a role with its permissions
     */
    public function clone($id)
    {
        $sourceRole = Role::with('permissions')->findOrFail($id);

        // Create new role with "Copy of" prefix
        $newRole = Role::create([
            'name' => 'copy_of_' . $sourceRole->name,
            'guard_name' => 'web',
        ]);

        // Copy all permissions
        $newRole->syncPermissions($sourceRole->permissions);

        return redirect()->route('roles.edit', $newRole->id)
            ->with('success', 'Role berhasil di-clone. Silakan ubah nama role.');
    }
}
