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
        Schema::table('keuangan_transaksis', function (Blueprint $table) {
            // PostgreSQL: mengubah enum constraint
            // Menggunakan raw SQL untuk modifikasi enum
            DB::statement("ALTER TABLE keuangan_transaksis DROP CONSTRAINT IF EXISTS keuangan_transaksis_status_approval_check");

            // Tambahkan constraint baru dengan nilai 'verified'
            DB::statement("ALTER TABLE keuangan_transaksis ADD CONSTRAINT keuangan_transaksis_status_approval_check CHECK (status_approval IN ('pending', 'verified', 'approved', 'rejected'))");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan_transaksis', function (Blueprint $table) {
            DB::statement("ALTER TABLE keuangan_transaksis DROP CONSTRAINT IF EXISTS keuangan_transaksis_status_approval_check");

            // Kembalikan ke constraint lama tanpa 'verified'
            DB::statement("ALTER TABLE keuangan_transaksis ADD CONSTRAINT keuangan_transaksis_status_approval_check CHECK (status_approval IN ('pending', 'approved', 'rejected'))");
        });
    }
};
