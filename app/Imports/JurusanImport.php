<?php
namespace App\Imports;

use App\Models\Jurusan;
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
        // Validasi: pastikan nama_jurusan dan kode_jurusan tidak kosong
        if (empty($row['nama_jurusan']) || empty($row['kode_jurusan'])) {
            return null;
        }

        /**
         * Konversi status: 'aktif' -> '1', 'non_aktif' -> '0'
         */
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

        // Cek apakah kode_jurusan sudah ada di database
        $jurusan = Jurusan::where('kode_jurusan', $row['kode_jurusan'])
            ->where('unit_id', $this->unit_id)
            ->where('tahun_ajaran_id', $this->tahun_ajaran_id)
            ->first();

        if ($jurusan) {
            // Jika kode_jurusan sudah ada, lakukan update
            $jurusan->update([
                'nama_jurusan'    => $row['nama_jurusan'],
                'kode_jurusan'    => $row['kode_jurusan'],
                'keterangan'      => $row['keterangan'],
                'unit_id'         => $this->unit_id,
                'tahun_ajaran_id' => $this->tahun_ajaran_id,
                'status'          => $status,
            ]);

            return $jurusan; // Mengembalikan objek yang di-update
        } else {
            // Jika kode_jurusan belum ada, insert data baru
            return new Jurusan([
                'nama_jurusan'    => $row['nama_jurusan'],
                'kode_jurusan'    => $row['kode_jurusan'],
                'keterangan'      => $row['keterangan'],
                'unit_id'         => $this->unit_id,
                'tahun_ajaran_id' => $this->tahun_ajaran_id,
                'status'          => $status,
            ]);
        }
    }

    public function chunkSize(): int
    {
        return 100; // Set ukuran chunk untuk menghindari timeout
    }

}
