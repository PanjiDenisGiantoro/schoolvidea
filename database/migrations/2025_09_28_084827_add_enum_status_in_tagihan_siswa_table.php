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
        Schema::table('tagihan_siswa', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE tagihan_siswa MODIFY COLUMN status ENUM('0', '1', '2')");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan_siswa', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE tagihan_siswa MODIFY COLUMN status ENUM('0', '1')");

        });
    }
};
