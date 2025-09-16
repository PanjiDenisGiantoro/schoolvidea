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
        Schema::create('keuangan_transaksis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerima_id')->nullable();
            $table->string('penerima_tipe')->nullable(); // App\Models\Siswa, App\Models\User, App\Models\Vendor, dll
            $table->string('jenis_transaksi', 50); // TABUNGAN_SETOR, TABUNGAN_TARIK, PEMBAYARAN, dll
            $table->decimal('jumlah', 15, 2);
            $table->string('metode', 50)->nullable(); // CASH, TRANSFER, SALDO_TABUNGAN
            $table->unsignedBigInteger('referensi_tagihan_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamp('tanggal_transaksi')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_transaksis');
    }
};
