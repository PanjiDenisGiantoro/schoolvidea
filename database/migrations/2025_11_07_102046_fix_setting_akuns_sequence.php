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
        \Illuminate\Support\Facades\DB::statement("SELECT setval('setting_akuns_id_seq', (SELECT MAX(id) FROM setting_akuns))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
