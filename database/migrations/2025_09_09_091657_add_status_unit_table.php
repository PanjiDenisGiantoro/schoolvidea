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
        Schema::table('units', function (Blueprint $table) {
            $table->enum('status', [0,1])->default(1)->after('website')->comment('0 = Tidak Aktif, 1 = Aktif');
        });

        Schema::table('yayasans', function (Blueprint $table) {
            $table->enum('status', [0,1])->default(1)->after('website')->comment('0 = Tidak Aktif, 1 = Aktif');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('yayasans', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
