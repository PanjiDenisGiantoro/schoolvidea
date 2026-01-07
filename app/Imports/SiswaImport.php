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
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SiswaImport implements ToModel, WithHeadingRow, WithChunkReading
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
        Log::info('========== SISWA IMPORT STARTED ==========');
        Log::info('Incoming row data: ' . json_encode($row));

        // Pastikan semua data string
$row = array_map(function ($value) {
    return is_string($value) ? trim($value) : $value;
}, $row);


        // Force kolom besar menjadi string
        $row['nik'] = isset($row['nik']) ? trim($row['nik']) : null;
        $row['no_va'] = isset($row['no_va']) ? trim($row['no_va']) : null;
        $row['no_rekening'] = isset($row['no_rekening']) ? trim($row['no_rekening']) : null;
        $row['nisn'] = isset($row['nisn']) ? trim($row['nisn']) : null;
        $row['nis'] = isset($row['nis']) ? trim($row['nis']) : null;

        try {
            // Validasi field required
if (
    empty($row['nama_lengkap']) ||
    empty($row['email']) ||
    empty($row['password']) ||
    empty($row['nisn']) ||
    empty($row['nis']) ||
    empty($row['kelas'])
) {
    Log::warning('⚠️ Missing required fields, skipping...');
    return null;
}

            DB::beginTransaction();
            // Step 1: User
            $user = User::where('email', $row['email'])->first();
            if (!$user) {
                $user = User::create([
                    'name' => $row['nama_lengkap'],
                    'email' => $row['email'],
                    'password' => bcrypt($row['password']),
                    'rfid_no' => $row['no_rfid'] ?? null,
                    'unit_id' => $this->unit_id,
                ]);
            } else {
                $user->update([
                    'name' => $row['nama_lengkap'],
                    'rfid_no' => $row['no_rfid'] ?? null,
                    'unit_id' => $this->unit_id,
                ]);
            }

            // Step 2: Role
            $rolePetugas = Roles::where('name', 'siswa')->first();
            if (!$rolePetugas) {
                DB::rollBack();
                return null;
            }
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );
            if (!$user->hasRole($roleSpatie->name)) {
                $user->assignRole($roleSpatie->name);
            }

            // Step 3: Kelas
            $kelas = Kelas::where('nama_kelas', $row['kelas'])->first();
            if (!$kelas) {
                DB::rollBack();
                return null;
            }

            // Step 4: Convert tanggal lahir
            $tanggalLahir = null;
            if (!empty($row['tanggal_lahir_ddmmyyyy'])) {
                if (is_numeric($row['tanggal_lahir_ddmmyyyy'])) {
                    // Excel serial number
                    $tanggalLahir = Date::excelToDateTimeObject($row['tanggal_lahir_ddmmyyyy'])->format('Y-m-d');
                } else {
                    $date = \DateTime::createFromFormat('d/m/Y', $row['tanggal_lahir_ddmmyyyy']);
                    if ($date) {
                        $tanggalLahir = $date->format('Y-m-d');
                    }
                }
            }

            // Status default 1
$status = isset($row['status']) && in_array($row['status'], ['0', '1'])
    ? (int) $row['status']
    : 1;

$jenisKelamin = null;
if (!empty($row['jenis_kelamin'])) {
    $jk = strtolower(trim($row['jenis_kelamin']));
    if (in_array($jk, ['l', 'laki-laki', 'laki laki'])) {
        $jenisKelamin = 'L';
    } elseif (in_array($jk, ['p', 'perempuan'])) {
        $jenisKelamin = 'P';
    }
}


            // Step 5: Update or create siswa
$siswa = Siswa::updateOrCreate(
    [
        'nisn' => $row['nisn'],
        'unit_id' => $this->unit_id,
        'tahun_ajaran_id' => $this->tahun_ajaran_id,
    ],
    [
        'nis' => $row['nis'],
        'kelas_id' => $kelas->id,
        'user_id' => $user->id,
        'rfid_no' => $row['no_rfid'] ?? null,
        'va_siswa' => $row['no_va'] ?? null,
        'jenis_kelamin' => $jenisKelamin,
        'agama' => $row['agama'] ?? null,
        'tempat_lahir' => $row['tempat_lahir'] ?? null,
        'tanggal_lahir' => $tanggalLahir,
        'no_hp_siswa' => $row['no_hp_siswa'] ?? null,
        'no_hp_ortu' => $row['no_hp_ortu'] ?? null,
        'nama_ortu' => $row['nama_orang_tua'] ?? null,
        'alamat' => $row['alamat'] ?? null,
        'bank' => $row['bank'] ?? null,
        'no_rekening' => $row['no_rekening'] ?? null,
        'nik' => $row['nik'] ?? null,
        'status' => $status,
    ]
);


            Log::info('✓ Siswa created/updated | ID: ' . $siswa->id);

            // Step 6: Saldo
            $saldo = Saldo_keuangan::firstOrCreate(
                ['user_id' => $user->id],
                ['saldo_akhir' => 0, 'status' => 0]
            );

            DB::commit();
            Log::info('✓ Transaction committed successfully');

            return $siswa;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERROR during siswa import');
            Log::error($e->getMessage());
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}