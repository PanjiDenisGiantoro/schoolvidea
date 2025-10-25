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
            $table->enum('status', ['0', '1','2'])->default(0)->after('siswa_id')->comment('0 = Belum Lunas, 1 = Lunas');
            $table->date('tanggal_bayar')->nullable()->after('status');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan_siswa', function (Blueprint $table) {
            //
        });
    }
};
