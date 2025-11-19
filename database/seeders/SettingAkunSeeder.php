<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\setting_akun;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class SettingAkunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all units
        $units = Unit::all();

        // Define the setting akun template
        $settingTemplate = $this->getSettingAkunTemplate();

        foreach ($units as $unit) {
            // Check if setting akuns already exist for this unit
            $existingSettingsCount = setting_akun::where('unit_id', $unit->id)->count();

            if ($existingSettingsCount === 0) {
                $this->seedSettingAkunsForUnit($unit->id, $settingTemplate);
            }
        }
    }

    /**
     * Seed setting akuns for a specific unit
     */
    private function seedSettingAkunsForUnit($unitId, $template)
    {
        foreach ($template as $row) {
            // Check if setting akun already exists
            $existingSetting = setting_akun::where('nama_setting', $row['nama_setting'])
                ->where('unit_id', $unitId)
                ->first();

            if (!$existingSetting) {
                // Find akun_id based on kode_akun
                $akun = null;
                if (!empty($row['kode_akun'])) {
                    $akun = Akun::where('kode_akun', $row['kode_akun'])
                        ->where('unit_id', $unitId)
                        ->first();
                }

                if ($akun) {
                    setting_akun::create([
                        'nama_setting' => $row['nama_setting'],
                        'kategori' => $row['kategori'] ?? null,
                        'akun_id' => $akun->id,
                        'debit' => $row['debit'] ?? 0,
                        'kredit' => $row['kredit'] ?? 0,
                        'status' => $row['status'] ?? 1,
                        'unit_id' => $unitId,
                        'keterangan' => $row['keterangan'] ?? null,
                    ]);
                }
            }
        }
    }

    /**
     * Get the setting akun template structure
     * Maps account codes to their transaction settings
     */
    private function getSettingAkunTemplate(): array
    {
        return [
            // TAGIHAN-MASUK (Incoming Invoices/Receipts)
            [
                'nama_setting' => 'PENERIMAAN TAGIHAN SISWA',
                'kategori' => 'tagihan-masuk',
                'kode_akun' => '41.1.01',  // PENDAPATAN TAGIHAN
                'debit' => 0,
                'kredit' => 1,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'PENERIMAAN SPMB',
                'kategori' => 'tagihan-masuk',
                'kode_akun' => '41.1.02',  // PENDAPATAN SPMB
                'debit' => 0,
                'kredit' => 1,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'SETOR KE BANK',
                'kategori' => 'tagihan-masuk',
                'kode_akun' => '11.1.02',  // KAS BANK
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'PENERIMAAN DANA BOS',
                'kategori' => 'tagihan-masuk',
                'kode_akun' => '31.2.01',  // DANA BOS / BOSDA
                'debit' => 0,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'PENGEMBALIAN PINJAMAN',
                'kategori' => 'tagihan-masuk',
                'kode_akun' => '11.2.01',  // PINJAMAN GURU & KARYAWAN
                'debit' => 0,
                'kredit' => 1,
                'status' => 1,
                'keterangan' => null,
            ],

            // TAGIHAN-KELUAR (Outgoing Invoices/Disbursements)
            [
                'nama_setting' => 'PENGELUARAN GAJI',
                'kategori' => 'tagihan-keluar',
                'kode_akun' => '51.1.01',  // BEBAN GAJI GURU & KARYAWAN
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'BIAYA OPERASIONAL SEKOLAH',
                'kategori' => 'tagihan-keluar',
                'kode_akun' => '51.2.01',  // BIAYA OPERASIONAL SEKOLAH
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'PENARIKAN DARI BANK',
                'kategori' => 'tagihan-keluar',
                'kode_akun' => '11.1.02',  // KAS BANK
                'debit' => 0,
                'kredit' => 1,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'PENGELUARAN DANA BOS',
                'kategori' => 'tagihan-keluar',
                'kode_akun' => '31.2.01',  // DANA BOS / BOSDA
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'PENGELUARAN UNTUK PINJAMAN',
                'kategori' => 'tagihan-keluar',
                'kode_akun' => '11.2.01',  // PINJAMAN GURU & KARYAWAN
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],

            // TABUNGAN (Savings - Deposits)
            [
                'nama_setting' => 'SETOR TABUNGAN SISWA',
                'kategori' => 'tabungan',
                'kode_akun' => '21.1.01',  // TABUNGAN SISWA
                'debit' => 0,
                'kredit' => 1,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'SETOR TABUNGAN GURU',
                'kategori' => 'tabungan',
                'kode_akun' => '21.1.02',  // TABUNGAN GURU
                'debit' => 0,
                'kredit' => 1,
                'status' => 1,
                'keterangan' => null,
            ],

            // TABUNGAN-TARIK (Savings - Withdrawals)
            [
                'nama_setting' => 'TARIK TABUNGAN SISWA',
                'kategori' => 'tabungan-tarik',
                'kode_akun' => '21.1.01',  // TABUNGAN SISWA
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'TARIK TABUNGAN GURU',
                'kategori' => 'tabungan-tarik',
                'kode_akun' => '21.1.02',  // TABUNGAN GURU
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
        ];
    }
}
