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
                if (Schema::hasColumn('akuns', 'no_akun')) {
                    $table->renameColumn('no_akun', 'kode_akun');
                }
            });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
