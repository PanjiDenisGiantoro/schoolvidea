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
            $table->string('token', 5)->nullable()->after('metode')->comment('Token verifikasi 5 digit');
            $table->timestamp('token_expired_at')->nullable()->after('token')->comment('Token expired time');
            $table->enum('status_approval', ['pending', 'approved', 'rejected'])->default('pending')->after('token_expired_at')->comment('Status persetujuan transaksi');
            $table->timestamp('approved_at')->nullable()->after('status_approval')->comment('Waktu disetujui/ditolak');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at')->comment('User yang menyetujui/menolak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan_transaksis', function (Blueprint $table) {
            $table->dropColumn(['token', 'token_expired_at', 'status_approval', 'approved_at', 'approved_by']);
        });
    }
};
