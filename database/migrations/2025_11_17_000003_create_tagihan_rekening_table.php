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
        Schema::create('tagihan_rekening', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tagihan_id');
            $table->unsignedBigInteger('rekening_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('tagihan_id')
                ->references('id')
                ->on('tagihan')
                ->onDelete('cascade');

            $table->foreign('rekening_id')
                ->references('id')
                ->on('data_rekening')
                ->onDelete('cascade');

            // Unique constraint to prevent duplicate entries
            $table->unique(['tagihan_id', 'rekening_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_rekening');
    }
};
