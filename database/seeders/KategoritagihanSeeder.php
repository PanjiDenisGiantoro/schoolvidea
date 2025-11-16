<?php

namespace Database\Seeders;

use App\Models\Kategoritagihan;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class KategoritagihanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all units
        $units = Unit::all();

        // Define the kategori tagihan template
        $kategoriTemplate = $this->getKategoritagihanTemplate();

        foreach ($units as $unit) {
            // Check if kategori tagihans already exist for this unit
            $existingKategoriCount = Kategoritagihan::where('unit_id', $unit->id)->count();

            if ($existingKategoriCount === 0) {
                $this->seedKategoritagihanForUnit($unit->id, $kategoriTemplate);
            }
        }
    }

    /**
     * Seed kategori tagihans for a specific unit
     */
    private function seedKategoritagihanForUnit($unitId, $template)
    {
        foreach ($template as $row) {
            // Check if kategori tagihan already exists
            $existingKategori = Kategoritagihan::where('kode_kategori', $row['kode_kategori'])
                ->where('unit_id', $unitId)
                ->first();

            if (!$existingKategori) {
                Kategoritagihan::create([
                    'nama_kategori' => $row['nama_kategori'],
                    'kode_kategori' => $row['kode_kategori'],
                    'keterangan' => $row['keterangan'] ?? null,
                    'unit_id' => $unitId,
                    'biaya_tagihan' => $row['biaya_tagihan'] ?? 0,
                    'tahun_ajaran_id' => null,
                    'status' => $row['status'] ?? '1',
                ]);
            }
        }
    }

    /**
     * Get the kategori tagihan template structure
     */
    private function getKategoritagihanTemplate(): array
    {
        return [
            [
                'nama_kategori' => 'TAGIHAN SPP',
                'kode_kategori' => '01',
                'biaya_tagihan' => 500000,
                'status' => '1',
                'keterangan' => 'Tagihan Sumbangan Pendidikan Peserta Didik bulanan',
            ],
            [
                'nama_kategori' => 'TAGIHAN BUKU',
                'kode_kategori' => '02',
                'biaya_tagihan' => 200000,
                'status' => '1',
                'keterangan' => 'Biaya pembelian buku sekolah dan referensi',
            ],
            [
                'nama_kategori' => 'TAGIHAN SERAGAM',
                'kode_kategori' => '03',
                'biaya_tagihan' => 400000,
                'status' => '1',
                'keterangan' => 'Biaya pengadaan seragam sekolah',
            ],
            [
                'nama_kategori' => 'STUDY TOUR',
                'kode_kategori' => '04',
                'biaya_tagihan' => 2000000,
                'status' => '1',
                'keterangan' => 'Biaya kunjungan belajar / study tour',
            ],
            [
                'nama_kategori' => 'BIAYA UJIAN PRAKTEK',
                'kode_kategori' => '05',
                'biaya_tagihan' => 0,
                'status' => '1',
                'keterangan' => 'Biaya ujian praktek',
            ],
        ];
    }
}
