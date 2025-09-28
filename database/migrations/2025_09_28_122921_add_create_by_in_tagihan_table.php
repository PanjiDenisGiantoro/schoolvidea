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
            $table->unsignedBigInteger('create_by')->nullable()->after('keterangan');
            $table->foreign('create_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_tagihan', function (Blueprint $table) {
            $table->dropForeign(['create_by']);
            $table->dropColumn('create_by');
        });
    }
};
