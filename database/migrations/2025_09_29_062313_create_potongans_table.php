<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('potongan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->unsignedBigInteger('siswa_id')->nullable();
            $table->unsignedBigInteger('kategori_tagihan_id')->nullable();
            $table->enum('tipe_potongan', ['persentase', 'nominal']);
            $table->decimal('nilai', 12, 2);
            $table->string('keterangan', 255)->nullable();

            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('siswa_id')->references('id')->on('siswas')->onDelete('cascade');
            $table->foreign('kategori_tagihan_id')->references('id')->on('kategoritagihans')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('potongan_siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('potongan_id');
            $table->unsignedBigInteger('tagihan_id')->nullable();        // fallback, kalau tagihan siswa belum ada
            $table->unsignedBigInteger('tagihan_siswa_id')->nullable();  // jika sudah terbentuk
            $table->decimal('nominal', 12, 2);
            $table->timestamps();

            $table->foreign('potongan_id')->references('id')->on('potongan')->onDelete('cascade');
            $table->foreign('tagihan_id')->references('id')->on('tagihan')->onDelete('cascade');
            $table->foreign('tagihan_siswa_id')->references('id')->on('tagihan_siswa')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('potongans');
        Schema::dropIfExists('potongan_siswa');
    }
};
