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
        Schema::table('pembayaran_tagihan', function (Blueprint $table) {
            $table->string('file_bukti')->nullable()->after('keterangan');
            $table->text('keterangan_siswa')->nullable()->after('file_bukti');
            $table->enum('status_approval', ['pending', 'approved', 'rejected'])->default('pending')->after('keterangan_siswa');
            $table->unsignedBigInteger('approved_by')->nullable()->after('status_approval');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('catatan_approval')->nullable()->after('approved_at');

            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_tagihan', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['file_bukti', 'keterangan_siswa', 'status_approval', 'approved_by', 'approved_at', 'catatan_approval']);
        });
    }
};
