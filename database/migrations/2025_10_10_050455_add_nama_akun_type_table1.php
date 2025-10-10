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
        Schema::table('akuns', function (Blueprint $table) {
            // Tambah kolom baru
            $table->string('nama_akun')->nullable()->after('kode_akun');
            $table->string('tipe')->nullable()->after('nama_akun');
            $table->unsignedBigInteger('parent_id')->nullable()->after('tipe');

            // Tambahkan relasi ke akuns.id jika parent_id mereferensikan akun lain
            $table->foreign('parent_id')->references('id')->on('akuns')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
