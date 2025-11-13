<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // Tabel tagihan_items
        Schema::table('tagihan_items', function (Blueprint $table) {
            if (!Schema::hasColumn('tagihan_items', 'rekening_id')) {
                $table->foreignId('rekening_id')
                    ->nullable()
                    ->constrained('data_rekening')
                    ->onDelete('set null');
            }
        });

        // Tabel tagihan_siswa
        Schema::table('tagihan_siswa', function (Blueprint $table) {
            if (!Schema::hasColumn('tagihan_siswa', 'rekening_id')) {
                $table->foreignId('rekening_id')
                    ->nullable()
                    ->constrained('data_rekening')
                    ->onDelete('set null');
            }
        });

        // Tabel keuangan_transaksis
        Schema::table('keuangan_transaksis', function (Blueprint $table) {
            if (!Schema::hasColumn('keuangan_transaksis', 'rekening_id')) {
                $table->foreignId('rekening_id')
                    ->nullable()
                    ->constrained('data_rekening')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('tagihan_items', function (Blueprint $table) {
            if (Schema::hasColumn('tagihan_items', 'rekening_id')) {
                $table->dropForeign(['rekening_id']);
                $table->dropColumn('rekening_id');
            }
        });

        Schema::table('tagihan_siswa', function (Blueprint $table) {
            if (Schema::hasColumn('tagihan_siswa', 'rekening_id')) {
                $table->dropForeign(['rekening_id']);
                $table->dropColumn('rekening_id');
            }
        });

        Schema::table('keuangan_transaksis', function (Blueprint $table) {
            if (Schema::hasColumn('keuangan_transaksis', 'rekening_id')) {
                $table->dropForeign(['rekening_id']);
                $table->dropColumn('rekening_id');
            }
        });
    }
};
