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
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Spatie\Permission\Models\Role as SpatieRole;

class SiswaImport implements ToModel, WithChunkReading, WithHeadingRow, WithBatchInserts
{
    protected int $unitId;
    protected int $tahunAjaranId;

    protected array $kelasCache = [];
    protected SpatieRole $siswaRole;

    public function __construct(int $unitId, int $tahunAjaranId)
    {
        $this->unitId = $unitId;
        $this->tahunAjaranId = $tahunAjaranId;

        // Cache kelas (hemat ribuan query)
        $this->kelasCache = Kelas::pluck('id', 'nama_kelas')->toArray();

        // Cache role
        $role = Roles::where('name', 'siswa')->firstOrFail();
        $this->siswaRole = SpatieRole::firstOrCreate(
            ['name' => $role->name],
            ['guard_name' => 'web']
        );
    }

    /* ======================== CORE ======================== */

    public function model(array $row)
    {
        Log::info('========== SISWA IMPORT START ==========');

        $row = $this->normalizeRow($row);
        $this->forceString($row);

        // VALIDASI WAJIB
        if (! $this->isValidRow($row)) {
            Log::warning('⛔ SKIP ROW (invalid)', $row);
            return null;
        }

        if (! isset($this->kelasCache[$row['kelas']])) {
            Log::warning("⛔ SKIP: Kelas tidak ditemukan ({$row['kelas']})");
            return null;
        }

        DB::beginTransaction();

        try {
            // UPSERT USER
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name'     => $row['nama_lengkap'],
                    'password' => bcrypt($row['password']),
                    'rfid_no'  => $row['no_rfid'],
                    'unit_id'  => $this->unitId,
                ]
            );

            if (! $user->hasRole($this->siswaRole->name)) {
                $user->assignRole($this->siswaRole->name);
            }

            // UPSERT SISWA BERDASARKAN NIK (AMAN)
            $siswa = Siswa::where('nik', $row['nik'])->first();

            $data = [
                'nisn'            => $row['nisn'],
                'nis'             => $row['nis'],
                'kelas_id'        => $this->kelasCache[$row['kelas']],
                'user_id'         => $user->id,
                'unit_id'         => $this->unitId,
                'tahun_ajaran_id' => $this->tahunAjaranId,
                'rfid_no'         => $row['no_rfid'],
                'va_siswa'        => $row['no_va'],
                'jenis_kelamin'   => $this->parseJenisKelamin($row['jenis_kelamin']),
                'agama'           => $row['agama'],
                'tempat_lahir'    => $row['tempat_lahir'],
                'tanggal_lahir'   => $this->parseTanggal($row['tanggal_lahir_ddmmyyyy']),
                'no_hp_ortu'      => $row['no_hp_ortu'],
                'nama_ortu'       => $row['nama_orang_tua'],
                'alamat'          => $row['alamat'],
                'bank'            => $row['bank'],
                'no_rekening'     => $row['no_rekening'],
                'status'          => in_array($row['status'], ['0', '1']) ? $row['status'] : '1',
            ];

            if ($siswa) {
                $siswa->update($data);
                Log::info("✓ SISWA UPDATED | NIK {$row['nik']}");
            } else {
                $data['nik'] = $row['nik'];
                Siswa::create($data);
                Log::info("✓ SISWA CREATED | NIK {$row['nik']}");
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ IMPORT ERROR: ' . $e->getMessage(), $row);
        }

        return null;
    }

    /* ======================== HELPERS ======================== */

    private function forceString(array &$row): void
    {
        foreach (['nik', 'nisn', 'nis', 'no_va', 'no_rfid'] as $field) {
            if (isset($row[$field])) {
                $row[$field] = trim((string) $row[$field]);
            }
        }
    }

    private function normalizeRow(array $row): array
    {
        return array_merge([
            'nik' => null,
            'nisn' => null,
            'nis' => null,
            'no_va' => null,
            'no_rekening' => null,
            'no_rfid' => null,
            'agama' => null,
            'alamat' => null,
            'bank' => null,
            'status' => '1',
        ], array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row));
    }

    private function isValidRow(array $row): bool
    {
        return
            ! empty($row['nik']) &&
            ! empty($row['nisn']) &&
            ! empty($row['nis']) &&
            ! empty($row['nama_lengkap']) &&
            ! empty($row['email']) &&
            ! empty($row['password']) &&
            ! empty($row['kelas']);
    }

    private function parseTanggal($value): ?string
    {
        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        $date = \DateTime::createFromFormat('d/m/Y', $value);
        return $date ? $date->format('Y-m-d') : null;
    }

    private function parseJenisKelamin($value): ?string
    {
        if (! $value) {
            return null;
        }

        return match (strtolower(trim($value))) {
            'l', 'laki-laki', 'laki laki' => 'L',
            'p', 'perempuan'             => 'P',
            default                      => null,
        };
    }

    /* ======================== PERFORMANCE ======================== */

    public function chunkSize(): int
    {
        return 1000; // 🔥 ideal untuk 50k
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
