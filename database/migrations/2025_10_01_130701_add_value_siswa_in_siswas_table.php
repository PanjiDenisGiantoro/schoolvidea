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
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('nik')->unique()->nullable();       // Nomor Induk Kependudukan
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable(); // L = Laki-laki, P = Perempuan
            $table->string('agama')->nullable();               // Agama
            $table->string('no_hp_ortu', 20)->nullable();           // Nomor HP
            $table->string('nama_ortu')->nullable();           // Nama Orang Tua / Wali
            $table->string('bank')->nullable();                // Nama Bank
            $table->string('no_rekening')->nullable();         // Nomor Rekening Bank
            $table->string('qrcode')->nullable();              // Path / kode QR (bisa disimpan string base64 / filename)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            //
        });
    }
};
