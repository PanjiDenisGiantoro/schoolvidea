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
        Schema::create('saldo_keuangan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();  // saldo per user (misal siswa/petugas)
            $table->unsignedBigInteger('akun_id')->nullable();  // saldo per akun
            $table->decimal('saldo_akhir', 15, 2)->default(0);
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('akun_id')->references('id')->on('akuns')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo_keuangan');
    }
};
