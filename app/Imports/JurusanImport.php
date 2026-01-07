<?php
namespace App\Imports;

use App\Models\Jurusan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JurusanImport implements ToModel
{
    protected $unit_id;
    protected $tahun_ajaran_id;

    // Constructor to accept unit_id and tahun_ajaran_id
    public function __construct($unit_id, $tahun_ajaran_id)
    {
        $this->unit_id = $unit_id;
        $this->tahun_ajaran_id = $tahun_ajaran_id;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        Log::info('========== JURUSAN IMPORT STARTED ==========');
        Log::info('Incoming row data: ' . json_encode($row));
        Log::info('Unit ID: ' . $this->unit_id . ' | Tahun Ajaran ID: ' . $this->tahun_ajaran_id);

        // Pastikan string
        $row = array_map('strval', $row);

        try {
            // ===== 1. Validasi wajib =====
            if (empty($row[0]) || empty($row[1])) {
                Log::warning('⚠️ Nama atau Kode Jurusan kosong, skip baris');
                return null;
            }

            // ===== 2. Status =====
            $status = '1'; // default aktif
            if (!empty($row[3])) {
                if (strtolower($row[3]) === 'aktif') {
                    $status = '1';
                } elseif (strtolower($row[3]) === 'non_aktif') {
                    $status = '0';
                } else {
                    $status = $row[3]; // angka
                }
            }

            DB::beginTransaction();

            // ===== 3. Update / Create =====
            $jurusan = Jurusan::updateOrCreate(
                [
                    'kode_jurusan' => $row[1],
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                ],
                [
                    'nama_jurusan' => $row[0],
                    'keterangan' => $row[2] ?? null,
                    'status' => $status,
                ]
            );

            DB::commit();

            Log::info('✓ Jurusan import success | ID: ' . $jurusan->id);
            Log::info('========== JURUSAN IMPORT COMPLETED ==========');

            return $jurusan;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERROR during jurusan import');
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
