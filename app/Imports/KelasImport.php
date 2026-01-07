<?php
namespace App\Imports;

use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Officer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;

class KelasImport implements ToModel
{
    protected $unit_id;
    protected $tahun_ajaran_id;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function __construct($unit_id, $tahun_ajaran_id)
    {
        $this->unit_id = $unit_id;
        $this->tahun_ajaran_id = $tahun_ajaran_id;
    }

    public function model(array $row)
    {
        Log::info('========== KELAS IMPORT STARTED ==========');
        Log::info('Incoming row data: ' . json_encode($row));
        Log::info('Unit ID: ' . $this->unit_id . ' | Tahun Ajaran ID: ' . $this->tahun_ajaran_id);

        // Pastikan string
        $row = array_map('strval', $row);

        try {
            // ===== 1. Validasi wajib =====
            if (empty($row[1])) { // nama kelas wajib
                Log::warning('⚠️ Nama kelas kosong, skip baris');
                return null;
            }

            // ===== 2. Cari Officer (Wali Kelas) =====
            $officer_id = null;
            if (!empty($row[2])) {
                $officer = Officer::where('name', $row[2])->first();
                $officer_id = $officer->id ?? null;
            }

            // ===== 3. Cari Jurusan =====
            $jurusan_id = null;
            if (!empty($row[3])) {
                $jurusan = Jurusan::where('nama_jurusan', $row[3])->first();
                $jurusan_id = $jurusan->id ?? null;
            }

            // ===== 4. Status =====
            $status = '1'; // default aktif
            if (!empty($row[4])) {
                if (strtolower($row[4]) === 'aktif') {
                    $status = '1';
                } elseif (strtolower($row[4]) === 'non_aktif') {
                    $status = '0';
                } else {
                    $status = $row[4];
                }
            }

            DB::beginTransaction();

            // ===== 5. Update / Create Kelas =====
            $kelas = Kelas::updateOrCreate(
                [
                    'nama_kelas' => $row[1],
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                ],
                [
                    'kode_kelas' => $row[0] ?? null,
                    'officer_id' => $officer_id,
                    'jurusan_id' => $jurusan_id,
                    'status' => $status,
                ]
            );

            DB::commit();

            Log::info('✓ Kelas import success | ID: ' . $kelas->id);
            Log::info('========== KELAS IMPORT COMPLETED ==========');

            return $kelas;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERROR during kelas import');
            Log::error($e->getMessage());
            Log::error('Row data: ' . json_encode($row));
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 100; // Set ukuran chunk untuk menghindari timeout
    }
}
