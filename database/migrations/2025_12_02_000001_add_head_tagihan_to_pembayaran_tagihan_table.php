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
            $table->string('head_tagihan')->unique()->nullable()->after('code_pembayaran')->comment('Nomor head pembayaran gabungan');
            $table->boolean('is_master')->default(true)->after('head_tagihan')->comment('Flag pembayaran master (true) atau detail (false)');
            $table->unsignedBigInteger('parent_pembayaran_id')->nullable()->after('is_master')->comment('FK ke pembayaran master jika ini detail');
            $table->integer('jumlah_tagihan_detail')->default(1)->after('parent_pembayaran_id')->comment('Jumlah tagihan dalam group pembayaran');

            // Add foreign key untuk parent_pembayaran_id
            $table->foreign('parent_pembayaran_id')
                ->references('id')
                ->on('pembayaran_tagihan')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_tagihan', function (Blueprint $table) {
            $table->dropForeign(['parent_pembayaran_id']);
            $table->dropColumn(['head_tagihan', 'is_master', 'parent_pembayaran_id', 'jumlah_tagihan_detail']);
        });
    }
};
