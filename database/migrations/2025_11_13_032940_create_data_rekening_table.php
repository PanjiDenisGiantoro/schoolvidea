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
        Schema::create('data_rekening', function (Blueprint $table) {
            $table->id();
            $table->string('allotment');
            $table->string('account_code');
            $table->string('account_name');
            $table->string('owner_name');
            $table->string('account_number');
            $table->string('account_pict');
            $table->string('kcp_name');
            $table->enum('status', ['1', '0'])->comment('1 = Aktif, 0 = Tidak Aktif')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_rekening');
    }
};
