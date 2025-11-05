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
        Schema::create('tagihan_siswa_mutasi', function (Blueprint $table) {
            $table->id();
            $table->string('code_mutasi')->unique();
            $table->unsignedBigInteger('tagihan_siswa_id');
            $table->enum('jenis_mutasi', ['koreksi', 'diskon', 'denda', 'pembatalan', 'lainnya'])
                ->comment('koreksi=Koreksi Nominal, diskon=Diskon/Potongan, denda=Denda, pembatalan=Pembatalan Tagihan, lainnya=Lainnya');
            $table->decimal('nominal_sebelum', 15, 2);
            $table->decimal('nominal_perubahan', 15, 2)->comment('Positif untuk penambahan, negatif untuk pengurangan');
            $table->decimal('nominal_sesudah', 15, 2);
            $table->text('keterangan');
            $table->text('alasan')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->enum('status_mutasi', ['pending', 'approved', 'rejected'])->default('approved');
            $table->timestamps();

            $table->foreign('tagihan_siswa_id')
                ->references('id')
                ->on('tagihan_siswa')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_siswa_mutasi');
    }
};