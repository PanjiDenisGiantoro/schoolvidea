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
        Schema::table('payroll_setting_deductions', function (Blueprint $table) {
            $table->string('period')->nullable();
            $table->year('year')->nullable();
            $table->index(['period', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_setting_deductions', function (Blueprint $table) {
            $table->dropIndex(['period', 'year']);
            $table->dropColumn(['period', 'year']);
        });
    }
};
