<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar permissions untuk berbagai entitas
        $permissions = [
            // Dashboard
            'view_dashboard',

            // Permissions untuk Role
            'view_role', 'create_role', 'edit_role', 'delete_role', 'manage_permission_role',

            // Permissions untuk User
            'view_user', 'create_user', 'edit_user', 'delete_user',

            // Permissions untuk Officer
            'view_officer', 'create_officer', 'edit_officer', 'delete_officer', 'upload_officer',

            // Permissions untuk Unit
            'view_unit', 'create_unit', 'edit_unit', 'delete_unit', 'upload_unit',

            // Permissions untuk Tahun Ajaran
            'view_tahun_ajaran', 'create_tahun_ajaran', 'edit_tahun_ajaran', 'delete_tahun_ajaran',

            // Permissions untuk Lembaga Unit
            'view_lembagaunit', 'create_lembagaunit', 'edit_lembagaunit', 'delete_lembagaunit', 'upload_lembagaunit',

            // Permissions untuk Kelas
            'view_kelas', 'create_kelas', 'edit_kelas', 'delete_kelas',

            // Permissions untuk Jurusan
            'view_jurusan', 'create_jurusan', 'edit_jurusan', 'delete_jurusan',

            // Permissions untuk Siswa
            'view_siswa', 'create_siswa', 'edit_siswa', 'delete_siswa', 'upload_siswa',

            // Permissions untuk Tagihan
            'view_tagihan', 'create_tagihan', 'edit_tagihan', 'delete_tagihan',

            // Permissions untuk Pembayaran
            'view_pembayaran', 'create_pembayaran', 'edit_pembayaran', 'delete_pembayaran',

            // Permissions untuk Report
            'view_report', 'generate_report',

            // Permissions untuk Potongan
            'view_potongan', 'create_potongan', 'edit_potongan', 'delete_potongan',

            // Permissions untuk Tabungan
            'view_tabungan', 'create_tabungan', 'edit_tabungan', 'delete_tabungan',

            // Permissions untuk Akun
            'view_akun', 'create_akun', 'edit_akun', 'delete_akun',

            // Permissions untuk Setting Akun
            'view_setting_akun', 'create_setting_akun', 'edit_setting_akun', 'delete_setting_akun',

            // Permissions untuk Rekening
            'view_rekening', 'create_rekening', 'edit_rekening', 'delete_rekening',

            // Permissions untuk Positions
            'view_positions', 'create_positions', 'edit_positions', 'delete_positions',

            // Permissions untuk Payroll Components
            'view_payroll_components', 'create_payroll_components', 'edit_payroll_components', 'delete_payroll_components',

            // Permissions untuk Payroll Deductions
            'view_payroll_deductions', 'create_payroll_deductions', 'edit_payroll_deductions', 'delete_payroll_deductions',

            // Permissions untuk Payroll Settings
            'view_payroll_settings', 'create_payroll_settings', 'edit_payroll_settings', 'delete_payroll_settings',

            // Permissions untuk Kategori Tagihan
            'view_kategoritagihan', 'create_kategoritagihan', 'edit_kategoritagihan', 'delete_kategoritagihan',

            // Permissions untuk Tipe Unit
            'view_tipe_unit', 'create_tipe_unit', 'edit_tipe_unit', 'delete_tipe_unit',

            // Permissions untuk Migrasi/Import Export
            'view_migrasi', 'import_migrasi', 'export_migrasi',

            // Permissions untuk Activity Log
            'view_activity',
        ];

        // Buat permission sesuai dengan yang ada di daftar
        echo "Creating permissions...\n";
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            echo "- {$permission}\n";
        }

        echo "\nCreating roles...\n";

        // === Super Admin Role ===
        $superAdmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());
        echo "✓ Super Admin (all permissions)\n";

        // === Admin Role ===
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminPermissions = Permission::all()->except(['manage_permission_role'])->pluck('name');
        $admin->syncPermissions($adminPermissions);
        echo "✓ Admin (almost all permissions)\n";

        // === Bendahara Role ===
        $bendahara = Role::firstOrCreate(['name' => 'bendahara', 'guard_name' => 'web']);
        $bendahara->syncPermissions([
            'view_dashboard',
            'view_siswa', 'view_kelas',
            'view_tagihan', 'create_tagihan', 'edit_tagihan', 'delete_tagihan',
            'view_pembayaran', 'create_pembayaran', 'edit_pembayaran', 'delete_pembayaran',
            'view_potongan', 'create_potongan', 'edit_potongan', 'delete_potongan',
            'view_tabungan', 'create_tabungan', 'edit_tabungan', 'delete_tabungan',
            'view_akun', 'create_akun', 'edit_akun', 'delete_akun',
            'view_setting_akun', 'create_setting_akun', 'edit_setting_akun', 'delete_setting_akun',
            'view_report', 'generate_report',
            'view_rekening', 'create_rekening', 'edit_rekening', 'delete_rekening',
            'view_kategoritagihan', 'create_kategoritagihan', 'edit_kategoritagihan', 'delete_kategoritagihan',
        ]);
        echo "✓ Bendahara (finance focused)\n";

        // === Operator Role ===
        $operator = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $operator->syncPermissions([
            'view_dashboard',
            'view_siswa', 'create_siswa', 'edit_siswa', 'delete_siswa', 'upload_siswa',
            'view_kelas', 'edit_kelas',
            'view_jurusan', 'edit_jurusan',
            'view_officer', 'create_officer', 'edit_officer',
            'view_unit', 'view_tahun_ajaran',
            'view_migrasi', 'import_migrasi', 'export_migrasi',
        ]);
        echo "✓ Operator (data entry focused)\n";

        // === Kepala Sekolah Role ===
        $kepalaSekolah = Role::firstOrCreate(['name' => 'kepala_sekolah', 'guard_name' => 'web']);
        $viewPermissions = Permission::where('name', 'like', 'view_%')
            ->orWhere('name', 'like', 'generate_%')
            ->pluck('name');
        $kepalaSekolah->syncPermissions($viewPermissions);
        echo "✓ Kepala Sekolah (view all)\n";

        // === User Role (Basic) ===
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->syncPermissions([
            'view_dashboard',
            'view_siswa', 'view_kelas', 'view_jurusan',
            'view_tagihan', 'view_pembayaran',
        ]);
        echo "✓ User (basic view)\n";

        echo "\n✅ Seeder completed successfully!\n";
    }
}
