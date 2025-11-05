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
        Schema::table('akuns', function (Blueprint $table) {
            $table->dropUnique(['kode_akun']);
            $table->string('kode_akun')->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('akuns', function (Blueprint $table) {
            //
        });
    }
};
