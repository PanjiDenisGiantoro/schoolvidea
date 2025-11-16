<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class AkunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all units
        $units = Unit::all();

        // Define the account template structure
        $akunTemplate = $this->getAkunTemplate();

        foreach ($units as $unit) {
            // Check if akuns already exist for this unit
            $existingAkunsCount = Akun::where('unit_id', $unit->id)->count();

            if ($existingAkunsCount === 0) {
                $this->seedAkunsForUnit($unit->id, $akunTemplate);
            }
        }
    }

    /**
     * Seed akuns for a specific unit
     */
    private function seedAkunsForUnit($unitId, $template)
    {
        $parentMap = []; // Map to store parent_kode => id

        foreach ($template as $row) {
            // Check if akun already exists
            $existingAkun = Akun::where('kode_akun', $row['kode_akun'])
                ->where('unit_id', $unitId)
                ->first();

            if (!$existingAkun) {
                // Find parent_id based on parent_kode if exists
                $parentId = null;
                if (!empty($row['parent_kode'])) {
                    // First check if we have the parent in our map (already created)
                    if (isset($parentMap[$row['parent_kode']])) {
                        $parentId = $parentMap[$row['parent_kode']];
                    } else {
                        // Otherwise query the database for existing parent
                        $parent = Akun::where('kode_akun', $row['parent_kode'])
                            ->where('unit_id', $unitId)
                            ->first();
                        $parentId = $parent?->id;
                    }
                }

                $newAkun = Akun::create([
                    'kode_akun' => $row['kode_akun'],
                    'nama_akun' => $row['nama_akun'],
                    'tipe' => $row['tipe'] ?? 'ASET',
                    'parent_id' => $parentId,
                    'unit_id' => $unitId,
                    'status' => $row['status'] ?? 1,
                    'kategori_akun' => $row['kategori_akun'] ?? null,
                    'keterangan' => $row['keterangan'] ?? null,
                ]);

                // Store in map for future parent lookups
                $parentMap[$row['kode_akun']] = $newAkun->id;
            }
        }
    }

    /**
     * Get the account template structure
     */
    private function getAkunTemplate(): array
    {
        return [
            // ASET (Assets)
            ['kode_akun' => '11', 'nama_akun' => 'ASET LANCAR', 'tipe' => 'ASET', 'parent_kode' => null, 'kategori_akun' => null, 'status' => 1],

            // ASET LANCAR - Kas & Bank
            ['kode_akun' => '11.1', 'nama_akun' => 'KAS & BANK', 'tipe' => 'ASET', 'parent_kode' => '11', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '11.1.01', 'nama_akun' => 'KAS SEKOLAH', 'tipe' => 'ASET', 'parent_kode' => '11.1', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '11.1.02', 'nama_akun' => 'KAS BANK', 'tipe' => 'ASET', 'parent_kode' => '11.1', 'kategori_akun' => 'transaksi', 'status' => 1],

            // ASET LANCAR - Piutang
            ['kode_akun' => '11.2', 'nama_akun' => 'PIUTANG', 'tipe' => 'ASET', 'parent_kode' => '11', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '11.2.01', 'nama_akun' => 'PINJAMAN GURU & KARYAWAN', 'tipe' => 'ASET', 'parent_kode' => '11.2', 'kategori_akun' => 'transaksi', 'status' => 1],

            // ASET LANCAR - Persediaan
            ['kode_akun' => '11.3', 'nama_akun' => 'PERSEDIAAN', 'tipe' => 'ASET', 'parent_kode' => '11', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '11.3.01', 'nama_akun' => 'PERSEDIAAN ATK & CETAKAN', 'tipe' => 'ASET', 'parent_kode' => '11.3', 'kategori_akun' => 'transaksi', 'status' => 1],

            // ASET TIDAK LANCAR
            ['kode_akun' => '12', 'nama_akun' => 'ASET TIDAK LANCAR', 'tipe' => 'ASET', 'parent_kode' => null, 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '12.2', 'nama_akun' => 'ASET TETAP', 'tipe' => 'ASET', 'parent_kode' => '12', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '12.2.02', 'nama_akun' => 'PERALATAN & INVENTARIS', 'tipe' => 'ASET', 'parent_kode' => '12.2', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '12.2.09', 'nama_akun' => 'AKUMULASI PENYUSUTAN ASET TETAP', 'tipe' => 'ASET', 'parent_kode' => '12.2', 'kategori_akun' => 'transaksi', 'status' => 1],

            // LIABILITAS (Kewajiban)
            ['kode_akun' => '21', 'nama_akun' => 'KEWAJIBAN JANGKA PENDEK', 'tipe' => 'LIABILITAS', 'parent_kode' => null, 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '21.1', 'nama_akun' => 'TABUNGAN SEKOLAH', 'tipe' => 'LIABILITAS', 'parent_kode' => '21', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '21.1.01', 'nama_akun' => 'TABUNGAN SISWA', 'tipe' => 'LIABILITAS', 'parent_kode' => '21.1', 'kategori_akun' => 'tabungan', 'status' => 1],
            ['kode_akun' => '21.1.02', 'nama_akun' => 'TABUNGAN GURU', 'tipe' => 'LIABILITAS', 'parent_kode' => '21.1', 'kategori_akun' => 'tabungan', 'status' => 1],
            ['kode_akun' => '21.5', 'nama_akun' => 'DANA TITIPAN', 'tipe' => 'LIABILITAS', 'parent_kode' => '21', 'kategori_akun' => null, 'status' => 1],

            // EKUITAS (Aset Bersih)
            ['kode_akun' => '31', 'nama_akun' => 'ASET BERSIH / EKUITAS', 'tipe' => 'EKUITAS', 'parent_kode' => null, 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '31.1', 'nama_akun' => 'DANA TIDAK TERIKAT', 'tipe' => 'EKUITAS', 'parent_kode' => '31', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '31.1.01', 'nama_akun' => 'DONASI / SUMBANGAN UMUM', 'tipe' => 'EKUITAS', 'parent_kode' => '31.1', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '31.2', 'nama_akun' => 'DANA TERIKAT TEMPORER', 'tipe' => 'EKUITAS', 'parent_kode' => '31', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '31.2.01', 'nama_akun' => 'DANA BOS / BOSDA', 'tipe' => 'EKUITAS', 'parent_kode' => '31.2', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '31.3', 'nama_akun' => 'DANA TERIKAT PERMANEN', 'tipe' => 'EKUITAS', 'parent_kode' => '31', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '31.3.01', 'nama_akun' => 'DANA WAKAF', 'tipe' => 'EKUITAS', 'parent_kode' => '31.3', 'kategori_akun' => 'transaksi', 'status' => 1],

            // PENDAPATAN (Revenue)
            ['kode_akun' => '41', 'nama_akun' => 'PENDAPATAN OPERASIONAL', 'tipe' => 'PENDAPATAN', 'parent_kode' => null, 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '41.1', 'nama_akun' => 'PENDAPATAN SEKOLAH', 'tipe' => 'PENDAPATAN', 'parent_kode' => '41', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '41.1.01', 'nama_akun' => 'PENDAPATAN TAGIHAN', 'tipe' => 'PENDAPATAN', 'parent_kode' => '41.1', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '41.1.02', 'nama_akun' => 'PENDAPATAN SPMB', 'tipe' => 'PENDAPATAN', 'parent_kode' => '41.1', 'kategori_akun' => 'transaksi', 'status' => 1],

            // BEBAN (Expenses)
            ['kode_akun' => '51', 'nama_akun' => 'BEBAN OPERASIONAL', 'tipe' => 'BEBAN', 'parent_kode' => null, 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '51.1', 'nama_akun' => 'BEBAN GAJI', 'tipe' => 'BEBAN', 'parent_kode' => '51', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '51.1.01', 'nama_akun' => 'BEBAN GAJI GURU & KARYAWAN', 'tipe' => 'BEBAN', 'parent_kode' => '51.1', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '51.2', 'nama_akun' => 'BEBAN UMUM & ADMINISTRASI', 'tipe' => 'BEBAN', 'parent_kode' => '51', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '51.2.01', 'nama_akun' => 'BIAYA OPERASIONAL SEKOLAH', 'tipe' => 'BEBAN', 'parent_kode' => '51.2', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '51.5', 'nama_akun' => 'BEBAN LAIN-LAIN', 'tipe' => 'BEBAN', 'parent_kode' => '51', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '51.5.01', 'nama_akun' => 'BEBAN PENYUSUTAN ASET', 'tipe' => 'BEBAN', 'parent_kode' => '51.5', 'kategori_akun' => 'transaksi', 'status' => 1],
        ];
    }
}
