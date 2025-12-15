<?php
namespace App\Imports;

use App\Models\Jurusan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JurusanImport implements ToModel, WithHeadingRow
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

        try {
            // Validasi: pastikan nama_jurusan dan kode_jurusan tidak kosong
            Log::info('Step 1: Validating required fields');
            Log::info('nama_jurusan: ' . ($row['nama_jurusan'] ?? 'EMPTY'));
            Log::info('kode_jurusan: ' . ($row['kode_jurusan'] ?? 'EMPTY'));

            if (empty($row['nama_jurusan']) || empty($row['kode_jurusan'])) {
                Log::warning('⚠️ Skipping row due to missing required fields (nama_jurusan or kode_jurusan)');
                return null;
            }
                Log::info('✓ Required fields validated');

            /**
             * Konversi status: 'aktif' -> '1', 'non_aktif' -> '0'
             */
            Log::info('Step 2: Processing status');
            $status = '1'; // Default aktif
            if (isset($row['status'])) {
                if (strtolower($row['status']) == 'aktif') {
                    $status = '1';
                } elseif (strtolower($row['status']) == 'non_aktif') {
                    $status = '0';
                } else {
                    $status = $row['status']; // Jika sudah angka, gunakan langsung
                }
            }
            Log::info('Status: ' . $status);

            DB::beginTransaction();
            Log::info('✓ Transaction started');

            // Cek apakah kode_jurusan sudah ada di database
            Log::info('Step 3: Checking if Jurusan exists');
            Log::info('Query: kode_jurusan=' . $row['kode_jurusan'] . ', unit_id=' . $this->unit_id . ', tahun_ajaran_id=' . $this->tahun_ajaran_id);

            $jurusan = Jurusan::where('kode_jurusan', $row['kode_jurusan'])
                ->where('unit_id', $this->unit_id)
                ->where('tahun_ajaran_id', $this->tahun_ajaran_id)
                ->first();

            if ($jurusan) {
                // Jika kode_jurusan sudah ada, lakukan update
                Log::info('Step 4: Updating existing Jurusan | ID: ' . $jurusan->id);

                $jurusan->update([
                    'nama_jurusan'    => $row['nama_jurusan'],
                    'kode_jurusan'    => $row['kode_jurusan'],
                    'keterangan'      => $row['keterangan'] ?? null,
                    'unit_id'         => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                    'status'          => $status,
                ]);

                Log::info('✓ Jurusan updated successfully | ID: ' . $jurusan->id);
                DB::commit();
                Log::info('✓ Transaction committed');
                Log::info('========== JURUSAN IMPORT COMPLETED (UPDATE) ==========');

                return $jurusan; // Mengembalikan objek yang di-update
            } else {
                // Jika kode_jurusan belum ada, insert data baru
                Log::info('Step 4: Creating new Jurusan');

                $newJurusan = new Jurusan([
                    'nama_jurusan'    => $row['nama_jurusan'],
                    'kode_jurusan'    => $row['kode_jurusan'],
                    'keterangan'      => $row['keterangan'] ?? null,
                    'unit_id'         => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                    'status'          => $status,
                ]);

                Log::info('✓ Jurusan created successfully');
                DB::commit();
                Log::info('✓ Transaction committed');
                Log::info('========== JURUSAN IMPORT COMPLETED (CREATE) ==========');

                return $newJurusan;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERROR during jurusan import');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Row data: ' . json_encode($row));
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 100; // Set ukuran chunk untuk menghindari timeout
    }

}
