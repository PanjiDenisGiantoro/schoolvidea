<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('backup_schedules', function (Blueprint $table) {
            $table->id();
            $table->boolean('auto_backup')->default(false);
            $table->enum('backup_frequency', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->time('backup_time')->default('02:00:00');
            $table->integer('keep_backups')->default(30);
            $table->boolean('email_notification')->default(false);
            $table->string('notification_email')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('type')->default('manual');
            $table->bigInteger('size')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('backup_schedules');
    }
};
