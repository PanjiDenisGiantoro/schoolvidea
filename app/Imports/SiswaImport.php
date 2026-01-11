<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Roles;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Spatie\Permission\Models\Role as SpatieRole;

class SiswaImport implements ToModel, WithChunkReading, WithHeadingRow
{
    protected int $unitId;
    protected int $tahunAjaranId;

    public function __construct($unitId, $tahunAjaranId)
    {
        $this->unitId = $unitId;
        $this->tahunAjaranId = $tahunAjaranId;
    }

    public function model(array $row)
    {
        Log::info('========== SISWA IMPORT START ==========');

        $row = $this->normalizeRow($row);

        if (! $this->hasRequiredFields($row)) {
            Log::warning('⚠️ Missing required fields, skipped');
            return null;
        }

        try {
            DB::beginTransaction();

            $user  = $this->upsertUser($row);
            $this->assignSiswaRole($user);

            $kelas = Kelas::where('nama_kelas', $row['kelas'])->firstOrFail();

            $siswa = Siswa::updateOrCreate(
                [
                    'nisn' => $row['nisn'],
                    'unit_id' => $this->unitId,
                    'tahun_ajaran_id' => $this->tahunAjaranId,
                ],
                [
                    'nis'            => $row['nis'],
                    'kelas_id'       => $kelas->id,
                    'user_id'        => $user->id,
                    'rfid_no'        => $row['no_rfid'],
                    'va_siswa'       => $row['no_va'],
                    'jenis_kelamin'  => $this->parseJenisKelamin($row['jenis_kelamin']),
                    'agama'          => $row['agama'],
                    'tempat_lahir'   => $row['tempat_lahir'],
                    'tanggal_lahir'  => $this->parseTanggal($row['tanggal_lahir_ddmmyyyy']),
                    'no_hp_siswa'    => $row['no_hp_siswa'],
                    'no_hp_ortu'     => $row['no_hp_ortu'],
                    'nama_ortu'      => $row['nama_orang_tua'],
                    'alamat'         => $row['alamat'],
                    'bank'           => $row['bank'],
                    'no_rekening'    => $row['no_rekening'],
                    'nik'            => $row['nik'],
                    'status'         => in_array($row['status'], ['0', '1']) ? (int) $row['status'] : 1,
                ]
            );

            DB::commit();
            Log::info("✓ Siswa saved | ID: {$siswa->id}");

            return $siswa;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ Import failed: ' . $e->getMessage());
            return null;
        }
    }

    /* ========================= HELPERS ========================= */

    private function normalizeRow(array $row): array
    {
        $row = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row);

        return array_merge([
            'nik' => null,
            'no_va' => null,
            'no_rekening' => null,
            'no_rfid' => null,
            'agama' => null,
            'alamat' => null,
            'bank' => null,
            'status' => 1,
        ], $row);
    }

    private function hasRequiredFields(array $row): bool
    {
        return ! empty($row['nama_lengkap'])
            && ! empty($row['email'])
            && ! empty($row['password'])
            && ! empty($row['nisn'])
            && ! empty($row['nis'])
            && ! empty($row['kelas']);
    }

    private function upsertUser(array $row): User
    {
        return User::updateOrCreate(
            ['email' => $row['email']],
            [
                'name'     => $row['nama_lengkap'],
                'password' => bcrypt($row['password']),
                'rfid_no'  => $row['no_rfid'],
                'unit_id'  => $this->unitId,
            ]
        );
    }

    private function assignSiswaRole(User $user): void
    {
        $role = Roles::where('name', 'siswa')->firstOrFail();

        $spatieRole = SpatieRole::firstOrCreate(
            ['name' => $role->name],
            ['guard_name' => 'web']
        );

        if (! $user->hasRole($spatieRole->name)) {
            $user->assignRole($spatieRole->name);
        }
    }

    private function parseTanggal($value): ?string
    {
        if (! $value) return null;

        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        $date = \DateTime::createFromFormat('d/m/Y', $value);
        return $date ? $date->format('Y-m-d') : null;
    }

    private function parseJenisKelamin($value): ?string
    {
        if (! $value) return null;

        return match (strtolower(trim($value))) {
            'l', 'laki-laki', 'laki laki' => 'L',
            'p', 'perempuan'             => 'P',
            default                      => null,
        };
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
