<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus constraint lama, lalu buat ulang dengan nilai yang diizinkan (0, 1, 2)
        DB::statement("
            ALTER TABLE tagihan_siswa
            DROP CONSTRAINT IF EXISTS tagihan_siswa_status_check,
            ADD CONSTRAINT tagihan_siswa_status_check CHECK (status IN ('0', '1', '2'));
        ");
    }

    public function down(): void
    {
        // Kembalikan ke constraint awal (misalnya hanya 0 dan 1)
        DB::statement("
            ALTER TABLE tagihan_siswa
            DROP CONSTRAINT IF EXISTS tagihan_siswa_status_check,
            ADD CONSTRAINT tagihan_siswa_status_check CHECK (status IN ('0', '1'));
        ");
    }
};
