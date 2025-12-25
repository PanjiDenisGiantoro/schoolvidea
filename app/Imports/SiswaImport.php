<?php
namespace App\Imports;

use App\Models\Kelas;
use App\Models\Roles;
use App\Models\Saldo_keuangan;
use App\Models\User;
use App\Models\Siswa;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
//use Maatwebsite\Excel\Concerns\WithHeadingRow;


class SiswaImport implements ToModel
{
    protected $unit_id;
    protected $tahun_ajaran_id;

    public function __construct($unit_id, $tahun_ajaran_id)
    {
        $this->unit_id = $unit_id;
        $this->tahun_ajaran_id = $tahun_ajaran_id;
    }

    public function model(array $row)
    {
        $row = array_map('strval', $row);

        DB::beginTransaction();
        try {
            // ===== 1. USER =====
            $user = User::where('email', $row[11])->first(); // EMAIL index 11

            if (!$user) {
                $user = User::create([
                    'name' => $row[4],          // NAMA LENGKAP index 4
                    'email' => $row[11],        // EMAIL
                    'password' => bcrypt($row[2]), // PASSWORD index 2
                    'rfid_no' => $row[18] ?? null, // NO RFID index 18
                    'unit_id' => $this->unit_id,
                ]);
            } else {
                $user->update([
                    'name' => $row[4],
                    'rfid_no' => $row[18] ?? null,
                    'unit_id' => $this->unit_id,
                ]);
            }

            // ===== 2. ROLE =====
            if (!$user->hasRole('siswa')) {
                $user->assignRole('siswa');
            }

            // ===== 3. KELAS =====
            $kelas = Kelas::where('nama_kelas', $row[15])->first(); // KELAS index 15
            if (!$kelas) {
                DB::rollBack();
                return null;
            }

            // ===== 4. TEMPAT & TANGGAL LAHIR =====
            $tempatLahir = $row[6] ?? null; // TEMPAT LAHIR index 6
            $tanggalLahir = null;
            if (!empty($row[7])) { // TANGGAL LAHIR index 7
                $date = \DateTime::createFromFormat('d/m/Y', $row[7]);
                if ($date) {
                    $tanggalLahir = $date->format('Y-m-d');
                }
            }

            // ===== 5. SISWA =====
            $siswa = Siswa::updateOrCreate(
                [
                    'nisn' => $row[0], // NISN index 0
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                ],
                [
                    'nis' => $row[1],                       // NIS index 1
                    'user_id' => $user->id,
                    'kelas_id' => $kelas->id,
                    'nik' => $row[5],                        // NIK index 5
                    'jenis_kelamin' => $row[6] === 'Laki-laki' ? 'L' : 'P', // JENIS KELAMIN index 6 ?? cek nanti
                    'tempat_lahir' => $tempatLahir,
                    'tanggal_lahir' => $tanggalLahir,
                    'agama' => $row[8],                       // AGAMA index 8
                    'no_hp_siswa' => $row[9] ?? null,        // NO HP SISWA index 9
                    'no_hp_ortu' => $row[10],                // NO HP ORTU index 10
                    'nama_ortu' => $row[12],                 // NAMA ORANG TUA index 12
                    'status' => $row[13],                    // STATUS index 13
                    'alamat' => $row[14] ?? null,            // ALAMAT index 14
                    'rfid_no' => $row[18] ?? null,           // NO RFID index 18
                    'va_siswa' => $row[19] ?? null,          // NO VA index 19
                    'bank' => $row[20] ?? null,              // BANK index 20
                    'no_rekening' => $row[21] ?? null,       // NO REKENING index 21
                ]
            );

            // ===== 6. SALDO =====
            Saldo_keuangan::firstOrCreate(
                ['user_id' => $user->id],
                ['saldo_akhir' => 0, 'status' => 0]
            );

            DB::commit();
            return $siswa;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            Log::error('Row data: ' . json_encode($row));
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
