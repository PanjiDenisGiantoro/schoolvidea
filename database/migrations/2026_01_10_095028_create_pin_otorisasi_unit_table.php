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
        Schema::create('pin_otorisasi_unit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id');
            $table->string('type'); // pembatalan
            $table->string('pin_hash');
            $table->timestamps();

            $table->unique(['unit_id', 'type']);

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pin_otorisasi_unit');
    }
};
