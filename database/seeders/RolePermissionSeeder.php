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
        // Daftar permissions untuk berbagai entitas
        $permissions = [
            // Permissions untuk Role
            'view_role', 'create_role', 'edit_role', 'delete_role',

            // Permissions untuk Officer
            'view_officer', 'create_officer', 'edit_officer', 'delete_officer',

            // Permissions untuk Unit
            'view_unit', 'create_unit', 'edit_unit', 'delete_unit',

            // Permissions untuk Tahun Ajaran
            'view_tahun_ajaran', 'create_tahun_ajaran', 'edit_tahun_ajaran', 'delete_tahun_ajaran',

            // Permissions untuk Lembaga Unit
            'view_lembagaunit', 'create_lembagaunit', 'edit_lembagaunit', 'delete_lembagaunit',

            // Permissions untuk Kelas
            'view_kelas', 'create_kelas', 'edit_kelas', 'delete_kelas',

            // Permissions untuk Jurusan
            'view_jurusan', 'create_jurusan', 'edit_jurusan', 'delete_jurusan',

            // Permissions untuk Siswa
            'view_siswa', 'create_siswa', 'edit_siswa', 'delete_siswa',

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
        ];

        // Buat permission sesuai dengan yang ada di daftar
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['created_at' => now(), 'updated_at' => now()]  // Optional: set timestamps explicitly
        );
        $userRole = Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
            ['created_at' => now(), 'updated_at' => now()]  // Optional: set timestamps explicitly
        );

        // Assign permissions ke roles
        $adminRole->givePermissionTo(Permission::all()); // Berikan semua permission ke admin
        $userRole->givePermissionTo([
            'view_officer', 'view_role', 'view_unit', 'view_tahun_ajaran',
            'view_lembagaunit', 'view_kelas', 'view_jurusan',
            'view_siswa', 'view_tagihan', 'view_pembayaran',
            'view_report', 'view_potongan', 'view_tabungan',
            'view_akun', 'view_setting_akun'
        ]); // Memberikan sebagian permission untuk user
    }
}
