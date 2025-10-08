<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            // Tambah kolom QR Code (jika belum ada)
            if (!Schema::hasColumn('siswas', 'qrcode')) {
                $table->string('qrcode')->nullable()->after('nis');
            }

            // Tambah kolom path gambar QR (jika belum ada)
            if (!Schema::hasColumn('siswas', 'qrcode_image')) {
                $table->string('qrcode_image')->nullable()->after('qrcode');
            }

            // Tambah kolom username (jika belum ada)
            if (!Schema::hasColumn('siswas', 'username')) {
                $table->string('username')->nullable()->after('qrcode_image');
            }

            // Tambah kolom password (jika belum ada)
            if (!Schema::hasColumn('siswas', 'password')) {
                $table->string('password')->nullable()->after('username');
            }

            // Hapus kolom tahun_ajaran_id (jika ada)
            if (Schema::hasColumn('siswas', 'tahun_ajaran_id')) {
                $table->dropColumn('tahun_ajaran_id');
            }
        });

        // Ubah kolom tanggal_lahir menjadi tipe DATE (PostgreSQL-style)
        DB::statement('ALTER TABLE siswas ALTER COLUMN tanggal_lahir TYPE date USING tanggal_lahir::date');
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            // Hapus kolom QR
            if (Schema::hasColumn('siswas', 'qrcode_image')) {
                $table->dropColumn('qrcode_image');
            }
            if (Schema::hasColumn('siswas', 'qrcode')) {
                $table->dropColumn('qrcode');
            }

            // Hapus kolom username dan password
            if (Schema::hasColumn('siswas', 'username')) {
                $table->dropColumn('username');
            }
            if (Schema::hasColumn('siswas', 'password')) {
                $table->dropColumn('password');
            }

            // Tambahkan kembali kolom tahun_ajaran_id kalau rollback
            if (!Schema::hasColumn('siswas', 'tahun_ajaran_id')) {
                $table->unsignedBigInteger('tahun_ajaran_id')->nullable()->after('kelas_id');
            }
        });

        // Ubah kembali kolom tanggal_lahir ke varchar
        DB::statement('ALTER TABLE siswas ALTER COLUMN tanggal_lahir TYPE varchar USING tanggal_lahir::text');
    }
};
