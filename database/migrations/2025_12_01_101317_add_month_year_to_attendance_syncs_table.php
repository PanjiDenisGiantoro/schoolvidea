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
        Schema::table('attendance_syncs', function (Blueprint $table) {
            // Tambahkan kolom month dan year
            if (! Schema::hasColumn('attendance_syncs', 'month')) {
                $table->integer('month')
                    ->after('videaclass_id')
                    ->nullable()
                    ->index();
            }

            if (! Schema::hasColumn('attendance_syncs', 'year')) {
                $table->integer('year')
                    ->after('month')
                    ->nullable()
                    ->index();
            }
            $table->integer('presence')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_syncs', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_syncs', 'month')) {
                $table->dropColumn('month');
            }
            if (Schema::hasColumn('attendance_syncs', 'year')) {
                $table->dropColumn('year');
            }
            $table->dropColumn('presence');
        });
    }
};
