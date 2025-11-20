<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * Metode up() ini akan MEMBUAT tabel 'payroll_payments' baru.
     */
    public function up(): void
    {
        Schema::create('payroll_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unit_id')
                  ->constrained('units')
                  ->onDelete('cascade');

            $table->foreignId('officer_id')
                  ->constrained('officers')
                  ->onDelete('cascade');

            $table->foreignId('payroll_setting_id')
                  ->constrained('payroll_settings')
                ->onDelete('cascade');

            $table->foreignId('component_id')
            ->constrained('payroll_components')
                ->onDelete('cascade');

            $table->decimal('teaching_hour_week')->default(0)->comment('Jumlah Jam Ajar Perminggu');
            $table->decimal('teaching_hour_month')->default(0)->comment('Jumlah Jam Ajar Perbulan');
            $table->decimal('total_earnings', 15, 2)->default(0)->comment('Total gaji dan tunjangan (Penerimaan)');
            $table->decimal('total_deductions', 15, 2)->default(0)->comment('Total potongan');
            $table->decimal('net_payment', 15, 2)->default(0)->comment('Penerimaan bersih');

            $table->integer('payment_month')->comment('Bulan pembayaran, cth: 12');
            $table->integer('payment_year')->comment('Tahun pembayaran, cth: 2025');
            $table->text('notes')->nullable()->comment('Catatan atau keterangan pembayaran');
            $table->timestamps();
            $table->index(['officer_id', 'payment_month']);
            $table->enum('status', ['draft', 'pending', 'paid', 'cancelled'])->default('draft');
        });
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_payments');
    }
};
