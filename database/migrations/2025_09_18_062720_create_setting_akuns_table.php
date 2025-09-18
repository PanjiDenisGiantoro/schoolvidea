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
        Schema::create('setting_akuns', function (Blueprint $table) {
            $table->id();
            $table->string('nama_setting')->nullable();
            $table->string('kategori')->nullable();
            $table->unsignedBigInteger('akun_id')->nullable();
            $table->integer('debit')->nullable();
            $table->integer('kredit')->nullable();
            $table->foreign('akun_id')->references('id')->on('akuns')->nullOnDelete();
            $table->enum('status', ['1', '0'])->comment('1 = Aktif, 0 = Tidak Aktif')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_akuns');
    }
};
