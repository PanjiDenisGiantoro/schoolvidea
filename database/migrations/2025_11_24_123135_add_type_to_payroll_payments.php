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
        Schema::table('payroll_payments', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu
            $table->dropForeign(['component_id']);

            // Hapus kolom component_id
            $table->dropColumn('component_id');

            // Tambahkan kolom type
            $table->string('type')->after('payroll_setting_id')->nullable()->default('');
            // Kamu bisa sesuaikan default dan nullable sesuai kebutuhan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payments', function (Blueprint $table) {
            // Hapus kolom type
            $table->dropColumn('type');

            // Tambahkan kembali component_id
            $table->unsignedBigInteger('component_id')->after('payroll_setting_id');

            // Tambahkan foreign key lagi
            $table->foreign('component_id')->references('id')->on('payroll_components')->onDelete('cascade');
        });
    }
};
