<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            ALTER TABLE merchants
            ADD CONSTRAINT saldo_non_negative
            CHECK (saldo_aktif >= 0)
        ');
    }

    public function down(): void
    {
        DB::statement('
            ALTER TABLE merchants
            DROP CONSTRAINT IF EXISTS saldo_non_negative
        ');
    }
};
