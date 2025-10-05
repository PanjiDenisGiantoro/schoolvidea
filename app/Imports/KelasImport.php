<?php
namespace App\Imports;

use App\Models\Kelas;
use App\Models\Officer;
use App\Models\User; // Assuming officers are in the 'users' table
use App\Models\Jurusan;
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
        $officer = User::with('officer')->where('name', $row['guru_wali_kelas'])->first();
        $officer_id = $officer->officer ? $officer->officer->id : null;

        $jurusan = Jurusan::where('nama_jurusan', $row['jurusan'])->first();
        $jurusan_id = $jurusan ? $jurusan->id : null;

        if (is_null($officer_id) || is_null($jurusan_id) || is_null($row['status'])) {
            return null; // Skip this row if any of these values are null
        }

        $status = (string) $row['status']; // Convert to '1' or '0'

        // Return a new Kelas instance with the correct values
        return new Kelas([
            'nama_kelas'      => $row['nama_kelas'],
            'unit_id'         => $this->unit_id, // Use the unit_id passed from the request
            'tahun_ajaran_id' => $this->tahun_ajaran_id, // Use the tahun_ajaran_id passed from the request
            'officer_id'      => $officer_id,
            'status'          => $status, // Store status as string '1' or '0'
            'jurusan_id'      => $jurusan_id,
        ]);
    }
}
