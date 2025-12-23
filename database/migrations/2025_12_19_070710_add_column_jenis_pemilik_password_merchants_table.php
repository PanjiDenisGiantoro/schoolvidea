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
        Schema::table('merchants', function (Blueprint $table) {
            $table->unsignedBigInteger('saldo_aktif')->nullable();
            $table->string('jenis')->nullable()->after('saldo_aktif');
            $table->string('password')->nullable()->after('jenis');
            $table->string('pemilik')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn(['saldo_aktif', 'jenis', 'password', 'pemilik']);
        });
    }
};
