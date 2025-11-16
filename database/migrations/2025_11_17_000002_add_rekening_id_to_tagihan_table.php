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

        // Remove rekening_id from tagihan_items if it exists
        if (Schema::hasColumn('tagihan_items', 'rekening_id')) {
            Schema::table('tagihan_items', function (Blueprint $table) {
                $table->dropForeign(['rekening_id']);
                $table->dropColumn('rekening_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan', function (Blueprint $table) {
            $table->dropForeign(['rekening_id']);
            $table->dropColumn('rekening_id');
        });

        // Re-add rekening_id to tagihan_items if needed
        if (!Schema::hasColumn('tagihan_items', 'rekening_id')) {
            Schema::table('tagihan_items', function (Blueprint $table) {
                $table->unsignedBigInteger('rekening_id')->nullable()->after('nominal');
                $table->foreign('rekening_id')->references('id')->on('data_rekening')->cascadeOnDelete();
            });
        }
    }
};
