<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('units_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('officers_id')->constrained('officers')->onDelete('cascade');
            $table->decimal('teaching_hours', 8, 2)->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->decimal('transport_allowance', 12, 2)->nullable();
            $table->decimal('meal_allowance', 12, 2)->nullable();
            $table->decimal('communication_allowance', 12, 2)->nullable();
            $table->decimal('other_allowance', 12, 2)->nullable();
            $table->string('billing_period')->nullable();
            $table->string('start_month')->nullable();
            $table->integer('start_year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};
