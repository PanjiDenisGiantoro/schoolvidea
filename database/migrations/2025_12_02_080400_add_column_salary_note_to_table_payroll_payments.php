<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        // Schema::table('payroll_payments', function (Blueprint $table) {
        //     $table->integer('salary_note')->nullable();
        // });
    }


    public function down(): void
    {
        // Schema::table('payroll_payments', function (Blueprint $table) {
        //     $table->dropColumn('salary_note');
        // });
    }
};
