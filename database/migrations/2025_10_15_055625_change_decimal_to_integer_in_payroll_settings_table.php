<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_components', function (Blueprint $table) {
            $table->bigInteger('price')->default(0)->change();
        });

        Schema::table('payroll_deductions', function (Blueprint $table) {
            $table->bigInteger('price')->default(0)->change();
        });

        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->bigInteger('teaching_hours')->change();
            $table->bigInteger('salary')->change();
            $table->bigInteger('transport_allowance')->nullable()->default(0)->change();
            $table->bigInteger('meal_allowance')->nullable()->default(0)->change();
            $table->bigInteger('communication_allowance')->nullable()->default(0)->change();
            $table->bigInteger('other_allowance')->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('payroll_components', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->default(0.0)->change();
        });

        Schema::table('payroll_deductions', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->default(0.0)->change();
        });

        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->decimal('teaching_hours', 8, 2)->nullable()->change();
            $table->decimal('salary', 12, 2)->nullable()->change();
            $table->decimal('transport_allowance', 12, 2)->nullable()->change();
            $table->decimal('meal_allowance', 12, 2)->nullable()->change();
            $table->decimal('communication_allowance', 12, 2)->nullable()->change();
            $table->decimal('other_allowance', 12, 2)->nullable()->change();
        });
    }
};
