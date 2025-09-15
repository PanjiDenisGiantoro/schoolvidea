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
        Schema::create('jurusans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jurusan')->nullable();
            $table->string('kode_jurusan')->nullable();
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->unsignedBigInteger('tahun_ajaran_id')->nullable();
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajarans')->nullOnDelete();
            $table->enum('status', ['1', '0'])->comment('1 = Aktif, 0 = Tidak Aktif')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurusans');
    }
};
