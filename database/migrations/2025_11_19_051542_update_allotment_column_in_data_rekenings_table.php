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
        Schema::table('data_rekenings', function (Blueprint $table) {
            $table->dropUnique(['allotment']); // hapus unique
            $table->string('allotment')->nullable()->change(); // ubah jadi nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_rekenings', function (Blueprint $table) {
            //
        });
    }
};
