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
            // 1. USER
            $user = User::where('email', $row[10])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $row[3],
                    'email' => $row[10],
                    'password' => bcrypt($row[2]),
                    'rfid_no' => $row[16] ?? null,
                    'unit_id' => $this->unit_id,
                ]);
            } else {
                $user->update([
                    'name' => $row[3],
                    'rfid_no' => $row[16] ?? null,
                    'unit_id' => $this->unit_id,
                ]);
            }

            // 2. ROLE
            if (!$user->hasRole('siswa')) {
                $user->assignRole('siswa');
            }

            // 3. KELAS
            $kelas = Kelas::where('nama_kelas', $row[14])->first();
            if (!$kelas) {
                DB::rollBack();
                return null;
            }

            // 4. TANGGAL LAHIR
            $tanggalLahir = null;
            if (!empty($row[6])) {
                $date = \DateTime::createFromFormat('d/m/Y', $row[6]);
                if ($date) {
                    $tanggalLahir = $date->format('Y-m-d');
                }
            }

            // 5. SISWA
            $siswa = Siswa::updateOrCreate(
                [
                    'nisn' => $row[0],
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                ],
                [
                    'nis' => $row[1],
                    'user_id' => $user->id,
                    'kelas_id' => $kelas->id,
                    'nik' => $row[4],
                    'jenis_kelamin' => $row[5] === 'Laki-laki' ? 'L' : 'P',
                    'tanggal_lahir' => $tanggalLahir,
                    'agama' => $row[7],
                    'no_hp_siswa' => $row[8] ?? null,
                    'no_hp_ortu' => $row[9],
                    'nama_ortu' => $row[11],
                    'status' => $row[12],
                    'alamat' => $row[13] ?? null,
                    'rfid_no' => $row[16] ?? null,
                    'va_siswa' => $row[17] ?? null,
                    'bank' => $row[18] ?? null,
                    'no_rekening' => $row[19] ?? null,
                ]
            );

            // 6. SALDO
            Saldo_keuangan::firstOrCreate(
                ['user_id' => $user->id],
                ['saldo_akhir' => 0, 'status' => 0]
            );

            DB::commit();
            return $siswa;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
