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
        Schema::table('keuangan_transaksis', function (Blueprint $table) {
            $table->string('bukti_transfer')->nullable()->after('keterangan');
            $table->enum('status_verifikasi', ['pending', 'approved', 'rejected'])->default('approved')->after('bukti_transfer');
            $table->text('catatan_verifikasi')->nullable()->after('status_verifikasi');
            $table->unsignedBigInteger('verified_by')->nullable()->after('catatan_verifikasi');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan_transaksis', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['bukti_transfer', 'status_verifikasi', 'catatan_verifikasi', 'verified_by', 'verified_at']);
        });
    }
};
