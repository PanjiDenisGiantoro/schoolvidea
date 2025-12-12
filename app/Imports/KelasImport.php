<?php
namespace App\Imports;

use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Officer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KelasImport implements ToModel, WithHeadingRow
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

        try {
            // Cek apakah officer berdasarkan nama sudah ada
            Log::info('Step 1: Finding Officer');
            Log::info('Looking for Officer with name: ' . ($row['guru_wali_kelas'] ?? 'EMPTY'));

            $officer = Officer::where('name', $row['guru_wali_kelas'])->first();
            $officer_id = $officer ? $officer->id : null;

            if ($officer) {
                Log::info('✓ Officer found | ID: ' . $officer_id . ' | Name: ' . $officer->name);
            } else {
                Log::warning('⚠️ Officer not found: ' . ($row['guru_wali_kelas'] ?? 'EMPTY') . ' - Will use empty string');
            }

            // Cek apakah jurusan berdasarkan nama sudah ada
            Log::info('Step 2: Finding Jurusan');
            Log::info('Looking for Jurusan with nama_jurusan: ' . ($row['jurusan'] ?? 'EMPTY'));

            $jurusan = Jurusan::where('nama_jurusan', $row['jurusan'])->first();
            $jurusan_id = $jurusan ? $jurusan->id : null;

            if ($jurusan) {
                Log::info('✓ Jurusan found | ID: ' . $jurusan_id . ' | Name: ' . $jurusan->nama_jurusan);
            } else {
                Log::warning('⚠️ Jurusan not found: ' . ($row['jurusan'] ?? 'EMPTY') . ' - Will use empty string');
            }

            /**
             * Konversi status: 'aktif' -> '1', 'non_aktif' -> '0'
             */
            Log::info('Step 3: Processing status');
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

            // Cek apakah kelas dengan nama_kelas sudah ada
            Log::info('Step 4: Checking if Kelas exists');
            Log::info('Query: nama_kelas=' . ($row['nama_kelas'] ?? 'EMPTY') . ', unit_id=' . $this->unit_id . ', tahun_ajaran_id=' . $this->tahun_ajaran_id);

            $kelas = Kelas::where('nama_kelas', $row['nama_kelas'])
                ->where('unit_id', $this->unit_id)
                ->where('tahun_ajaran_id', $this->tahun_ajaran_id)->first();

            $kelasData = [
                'kode_kelas'      => $row['kode_kelas'] ?? null,
                'nama_kelas'      => $row['nama_kelas'],
                'unit_id'         => $this->unit_id,
                'tahun_ajaran_id' => $this->tahun_ajaran_id,
                'officer_id'      => $officer_id ?? null,
                'status'          => $status,
                'jurusan_id'      => $jurusan_id ?? null,
            ];

            Log::info('Kelas data to be saved: ' . json_encode($kelasData));

            if ($kelas) {
                // Jika kelas sudah ada, lakukan update
                Log::info('Step 5: Updating existing Kelas | ID: ' . $kelas->id);

                $kelas->update($kelasData);

                Log::info('✓ Kelas updated successfully | ID: ' . $kelas->id);
                DB::commit();
                Log::info('✓ Transaction committed');
                Log::info('========== KELAS IMPORT COMPLETED (UPDATE) ==========');

                return $kelas; // Mengembalikan objek yang diupdate
            } else {
                // Jika kelas belum ada, buat kelas baru
                Log::info('Step 5: Creating new Kelas');

                $newKelas = new Kelas($kelasData);

                Log::info('✓ Kelas created successfully');
                DB::commit();
                Log::info('✓ Transaction committed');
                Log::info('========== KELAS IMPORT COMPLETED (CREATE) ==========');

                return $newKelas;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERROR during kelas import');
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
