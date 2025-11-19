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
        Schema::create('data_rekenings', function (Blueprint $table) {
            $table->id();
            $table->string('allotment')->unique();
            $table->string('account_code')->nullable();
            $table->string('account_name')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_pict')->nullable();
            $table->string('kcp_name')->nullable();
            $table->enum('status', ['1', '0'])->comment('1 = Aktif, 0 = Tidak Aktif')->nullable();
            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_rekening');
    }
};
