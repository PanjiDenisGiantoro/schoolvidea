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
        Schema::create('keuangan_transaksi_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaksi_id');
            $table->string('aksi', 20); // INSERT, UPDATE, DELETE
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->unsignedBigInteger('dilakukan_oleh')->nullable();
            $table->timestamp('dilakukan_pada')->useCurrent();

            $table->foreign('transaksi_id')->references('id')->on('keuangan_transaksis')->cascadeOnDelete();
            $table->foreign('dilakukan_oleh')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_transaksi_logs');
    }
};
