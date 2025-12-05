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
        Schema::table('payroll_payments', function (Blueprint $table) {
            $table->tinyInteger('presence')->nullable()->default(0);
            $table->tinyInteger('presence_count')->nullable()->default(0);
            $table->tinyInteger('absence_count')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payments', function (Blueprint $table) {
            $table->dropColumn(['presence', 'presence_count', 'absence_count']);
        });
    }
};
