<?php
namespace App\Imports;

use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Officer;
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
        // Cek apakah officer berdasarkan nama sudah ada
        $officer = Officer::where('name', $row['guru_wali_kelas'])->first();
        $officer_id = $officer ? $officer->id : null;

        // Cek apakah jurusan berdasarkan nama sudah ada
        $jurusan = Jurusan::where('nama_jurusan', $row['jurusan'])->first();
        $jurusan_id = $jurusan ? $jurusan->id : null;

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

        // Skip jika ada field yang kosong
        if (is_null($officer_id) || is_null($jurusan_id)) {
            return null; // Skip this row if any of these values are null
        }

        // Cek apakah kelas dengan nama_kelas sudah ada
        $kelas = Kelas::where('nama_kelas', $row['nama_kelas'])
            ->where('unit_id', $this->unit_id)
            ->where('tahun_ajaran_id', $this->tahun_ajaran_id)->first();

        if ($kelas) {
            // Jika kelas sudah ada, lakukan update
            $kelas->update([
                'kode_kelas'      => $row['kode_kelas'],
                'nama_kelas'      => $row['nama_kelas'],
                'unit_id'         => $this->unit_id,
                'tahun_ajaran_id' => $this->tahun_ajaran_id,
                'officer_id'      => $officer_id,
                'status'          => $status,
                'jurusan_id'      => $jurusan_id,
            ]);
            return $kelas; // Mengembalikan objek yang diupdate
        } else {
            // Jika kelas belum ada, buat kelas baru
            return new Kelas([
                'kode_kelas'      => $row['kode_kelas'],
                'nama_kelas'      => $row['nama_kelas'],
                'unit_id'         => $this->unit_id,
                'tahun_ajaran_id' => $this->tahun_ajaran_id,
                'officer_id'      => $officer_id,
                'status'          => $status,
                'jurusan_id'      => $jurusan_id,
            ]);
        }
    }

    public function chunkSize(): int
    {
        return 100; // Set ukuran chunk untuk menghindari timeout
    }
}
