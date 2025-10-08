<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            // 1️⃣ Ubah nama kolom "iamge" menjadi "image" (kalau kolom lama masih ada)
            if (Schema::hasColumn('officers', 'iamge') && !Schema::hasColumn('officers', 'image')) {
                $table->renameColumn('iamge', 'image');
            }

            // 2️⃣ Tambahkan kolom jabatan_id jika belum ada
            if (!Schema::hasColumn('officers', 'position_id')) {
                $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            // Rollback perubahan
            if (Schema::hasColumn('officers', 'image') && !Schema::hasColumn('officers', 'iamge')) {
                $table->renameColumn('image', 'iamge');
            }

            if (Schema::hasColumn('officers', 'position_id')) {
                $table->dropForeign(['position_id']);
                $table->dropColumn('position_id');
            }
        });
    }
};
