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
        Schema::create('akuns', function (Blueprint $table) {
            $table->id();
            $table->string('kode_akun', 20)->unique();
            $table->string('nama_akun', 100);
            $table->enum('tipe', ['ASET', 'LIABILITAS', 'EKUITAS', 'PENDAPATAN', 'BEBAN']);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('akuns')->nullOnDelete();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->enum('status', ['1', '0'])->comment('1 = Aktif, 0 = Tidak Aktif')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akuns');
    }
};
