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
        Schema::table('officers', function (Blueprint $table) {
            $table->string('nuptk')->nullable()->after('id');
            $table->string('nik')->nullable()->after('nuptk');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable()->after('nik');
            $table->string('agama')->nullable()->after('jenis_kelamin');
            $table->date('tanggal_lahir')->nullable()->after('agama');
            $table->text('alamat')->nullable()->after('tanggal_lahir');
            $table->string('bank')->nullable()->after('alamat');
            $table->string('no_rekening')->nullable()->after('bank');
            $table->string('no_kartu_rfid')->nullable()->after('no_rekening');
            $table->string('qr_code')->nullable()->after('no_kartu_rfid');
            $table->json('jurusan')->nullable()->after('qr_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            //
        });
    }
};
