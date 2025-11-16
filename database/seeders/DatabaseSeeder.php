<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(AkunSeeder::class);
        $this->call(SettingAkunSeeder::class);
        $this->call(KategoritagihanSeeder::class);

    }
}
