<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->dropColumn('communication_allowance');
            $table->integer('staff_allowance')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->integer('communication_allowance')->nullable()->default(0);
            $table->dropColumn('staff_allowance');
        });
    }
};
