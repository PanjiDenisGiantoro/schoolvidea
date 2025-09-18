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
        Schema::table('saldo_keuangan', function (Blueprint $table) {
            $table->integer('status')->default(1)->comment('1 = Aktif, 0 = Tidak Aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saldo_keuangan', function (Blueprint $table) {
            //
        });
    }
};
