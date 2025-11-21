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
        Schema::create('attendance_syncs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unit_id')
                  ->constrained('units')
                  ->onDelete('cascade');

            $table->foreignId('officer_id')
                  ->constrained('officers')
                  ->onDelete('cascade');

            $table->integer('videaclass_id')->comment('ID dari Videaclass');
            $table->string('registered_number')->comment('Nomor registrasi dari Videaclass');
            $table->string('fullname')->comment('Nama lengkap dari Videaclass');

            $table->integer('presence_count')->default(0)->comment('Jumlah Hadir');
            $table->integer('absence_count')->default(0)->comment('Jumlah Absen');
            $table->boolean('is_active')->default(true)->comment('Status aktif dari Videaclass');

            $table->timestamp('synced_at')->useCurrent()->comment('Waktu terakhir sinkronisasi');
            $table->timestamps();

            $table->index(['unit_id', 'officer_id']);
            $table->unique(['unit_id', 'officer_id', 'videaclass_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_syncs');
    }
};
