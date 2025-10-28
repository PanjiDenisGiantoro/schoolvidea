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
        Schema::table('officers', function (Blueprint $table) {
            $table->string('name')->nullable();
        });
        Schema::table('officers', function (Blueprint $table) {
            $table->dropColumn(['jurusan', 'tahun_ajaran_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            $table->dropColumn('name');
        });
        Schema::table('officers', function (Blueprint $table) {
            $table->string('jurusan')->nullable();
            $table->string('tahun_ajaran_id')->nullable();
        });
    }
};
