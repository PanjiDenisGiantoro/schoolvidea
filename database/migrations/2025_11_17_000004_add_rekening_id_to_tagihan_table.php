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
        Schema::table('tagihan', function (Blueprint $table) {
            // Tambah kolom rekening_id jika belum ada
            if (!Schema::hasColumn('tagihan', 'rekening_id')) {
                $table->unsignedBigInteger('rekening_id')->nullable()->after('tahun_mulai');
                $table->foreign('rekening_id')
                    ->references('id')
                    ->on('data_rekenings')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan', function (Blueprint $table) {
            // Hapus foreign key dan kolom
            if (Schema::hasColumn('tagihan', 'rekening_id')) {
                $table->dropForeign(['rekening_id']);
                $table->dropColumn('rekening_id');
            }
        });
    }
};
