<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->unsignedBigInteger('jurusan_id')->nullable()->after('kelas_id');
            $table->string('alamat')->nullable();
            $table->string('name')->nullable();
            $table->dropColumn('jurusan');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->json('jurusan')->nullable();
            $table->dropColumn(['jurusan_id', 'alamat', 'name']);
        });
    }
};
