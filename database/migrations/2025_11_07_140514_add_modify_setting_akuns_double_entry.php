<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('setting_akuns', function (Blueprint $table) {
            // Tambah kolom akun_debit_id dan akun_kredit_id untuk double-entry
            $table->unsignedBigInteger('akun_debit_id')->nullable()->after('akun_id');
            $table->unsignedBigInteger('akun_kredit_id')->nullable()->after('akun_debit_id');

            // Tambah foreign key constraints
            $table->foreign('akun_debit_id')->references('id')->on('akuns')->nullOnDelete();
            $table->foreign('akun_kredit_id')->references('id')->on('akuns')->nullOnDelete();

            // Hapus kolom debit dan kredit yang lama (yang berupa flag)
            $table->dropColumn(['debit', 'kredit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setting_akuns', function (Blueprint $table) {
            // Kembalikan kolom debit dan kredit
            $table->integer('debit')->nullable();
            $table->integer('kredit')->nullable();

            // Hapus foreign keys terlebih dahulu
            $table->dropForeign(['akun_debit_id']);
            $table->dropForeign(['akun_kredit_id']);

            // Hapus kolom akun_debit_id dan akun_kredit_id
            $table->dropColumn(['akun_debit_id', 'akun_kredit_id']);
        });
    }
};
