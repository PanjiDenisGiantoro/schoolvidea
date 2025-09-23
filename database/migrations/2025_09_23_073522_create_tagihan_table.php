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
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id');   // Unit Pendidikan
            $table->unsignedBigInteger('kelas_id')->nullable(); // Target kelas
            $table->enum('target', ['all', 'per'])->default('all'); // semua/per siswa
            $table->enum('jenis_tagihan', ['bulanan', 'bebas']); // switch
            $table->integer('periode')->nullable();  // jumlah bulan / tahun
            $table->integer('nominal_bebas')->nullable(); // kalau bebas
            $table->tinyInteger('bulan_mulai')->nullable(); // 1-12
            $table->year('tahun_mulai')->nullable();
            $table->timestamps();

            // relasi
            $table->foreign('unit_id')->references('id')->on('units')->cascadeOnDelete();
            $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
