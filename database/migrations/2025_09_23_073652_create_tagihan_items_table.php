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
        Schema::create('tagihan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tagihan_id');
            $table->unsignedBigInteger('kategori_id'); // dari kategori_tagihan
            $table->integer('nominal')->nullable(); // kalau ada override biaya
            $table->timestamps();

            $table->foreign('tagihan_id')->references('id')->on('tagihan')->cascadeOnDelete();
            $table->foreign('kategori_id')->references('id')->on('kategoritagihans')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_items');
    }
};
