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
        Schema::create('pembayaran_tagihan_detail', function (Blueprint $table) {
            $table->id();
            $table->string('head_tagihan')->comment('Referensi ke head pembayaran');
            $table->unsignedBigInteger('pembayaran_id')->comment('FK ke pembayaran_tagihan (master)');
            $table->unsignedBigInteger('tagihan_siswa_id')->comment('FK ke tagihan_siswa');
            $table->decimal('jumlah_bayar_detail', 15, 2)->comment('Jumlah bayar untuk tagihan ini');
            $table->integer('urutan')->default(1)->comment('Nomor urut dalam group pembayaran');
            $table->string('periode')->nullable()->comment('Periode tagihan');
            $table->integer('tahun')->nullable()->comment('Tahun tagihan');
            $table->timestamps();

            // Foreign keys
            $table->foreign('pembayaran_id')
                ->references('id')
                ->on('pembayaran_tagihan')
                ->onDelete('cascade');

            $table->foreign('tagihan_siswa_id')
                ->references('id')
                ->on('tagihan_siswa')
                ->onDelete('cascade');

            // Unique constraint
            $table->unique(['head_tagihan', 'tagihan_siswa_id']);

            // Index untuk performa query
            $table->index('head_tagihan');
            $table->index('pembayaran_id');
            $table->index('tagihan_siswa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_tagihan_detail');
    }
};
