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
            $table->unsignedBigInteger('payroll_setting_id')->nullable()->change();
            $table->dropForeign(['payroll_setting_id']);
            $table->foreign('payroll_setting_id')->references('id')->on('payroll_settings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payments', function (Blueprint $table) {
            $table->dropForeign(['payroll_setting_id']);
            $table->foreign('payroll_setting_id')->references('id')->on('payroll_settings')->onDelete('cascade');
            $table->unsignedBigInteger('payroll_setting_id')->nullable(false)->change();
        });
    }
};
