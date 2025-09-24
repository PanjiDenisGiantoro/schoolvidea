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
        Schema::create('pembayaran_tagihan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tagihan_siswa_id');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->date('tanggal_bayar');
            $table->string('metode_bayar', 50)->default('cash');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('tagihan_siswa_id')
                ->references('id')
                ->on('tagihan_siswa')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_tagihan');
    }
};
