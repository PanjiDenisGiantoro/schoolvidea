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
        Schema::create('trial_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');          // Nama sekolah
            $table->string('npsn');                 // NPSN sekolah
            $table->text('address');                // Alamat sekolah
            $table->string('full_name');            // Nama lengkap pengisi form
            $table->string('email');                // Email
            $table->string('no_hp');                // No HP
            $table->unsignedBigInteger('tipe_unit_id'); // Tipe unit
            $table->unsignedBigInteger('yayasan_id')->nullable(); // Yayasan ID (nullable)
            $table->enum('status', ['1', '0'])->default('1'); // Status pendaftaran

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trial_registrations');
    }
};
