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
        Schema::table('potongan', function (Blueprint $table) {
            $table->unsignedBigInteger('tagihan_id')->nullable(); // Create new column for tagihan_id
            $table->foreign('tagihan_id')->references('id')->on('tagihan')->onDelete('cascade'); // Add new foreign key constraint


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('potongans', function (Blueprint $table) {
            //
        });
    }
};
