<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop constraint jenis_kelamin_check dari tabel officers
        DB::statement('ALTER TABLE officers DROP CONSTRAINT IF EXISTS officers_jenis_kelamin_check');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan constraint jika rollback (opsional)
        DB::statement("ALTER TABLE officers ADD CONSTRAINT officers_jenis_kelamin_check CHECK (jenis_kelamin IN ('Laki-laki', 'Perempuan'))");
    }
};
