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
        // Pastikan data diproses mulai dari baris kedua (header diabaikan)
        return new Jurusan([
            'nama_jurusan'    => $row['nama_jurusan'],   // Mengakses berdasarkan nama kolom
            'kode_jurusan'    => $row['kode_jurusan'],
            'keterangan'      => $row['keterangan'],
            'unit_id'         => $this->unit_id, // Use the unit_id passed from the request
            'tahun_ajaran_id' => $this->tahun_ajaran_id, // Use the tahun_ajaran_id passed from the request
            'status'          => $row['status'] ?? 1,  // Status default 1 jika kosong
        ]);
    }
}
