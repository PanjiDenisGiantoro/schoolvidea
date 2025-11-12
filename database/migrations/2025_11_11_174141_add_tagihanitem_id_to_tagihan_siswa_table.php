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
            $table->unsignedBigInteger('tagihanitem_id')->nullable()->after('tagihan_id');
            $table->foreign('tagihanitem_id')->references('id')->on('tagihan_items')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan_siswa', function (Blueprint $table) {
            $table->dropForeign(['tagihanitem_id']);
            $table->dropColumn('tagihanitem_id');
        });
    }
};
