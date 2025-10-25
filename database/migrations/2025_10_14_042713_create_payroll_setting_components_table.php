<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_setting_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_setting_id')->constrained('payroll_settings')->onDelete('cascade');
            $table->foreignId('component_id')->constrained('payroll_components')->onDelete('cascade');
            $table->decimal('value', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_setting_components');
    }
};

