<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Officer;
use App\Models\Roles_petugas;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class OfficerImport implements ToModel, WithHeadingRow
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
        Log::info('Incoming row data: ' . json_encode($row));

        try {
            // ===== 1. Validasi kolom wajib =====
            if (
                empty($row['nama_lengkap']) ||
                empty($row['email']) ||
                empty($row['role']) ||
                empty($row['nip'])
            ) {
                Log::warning('Skipping row due to missing required fields: ' . json_encode($row));
                return null;
            }

            // ===== 2. Validasi kolom numeric =====
            $numericFields = ['no_hp', 'no_rfid', 'nip', 'nuptk', 'nik'];
            foreach ($numericFields as $field) {
                if (!empty($row[$field]) && !is_numeric($row[$field])) {
                    Log::warning("Skipping row due to invalid numeric value in $field: " . json_encode($row));
                    return null;
                }
            }

            // ===== 3. Konversi tanggal lahir dari DD/MM/YYYY ke YYYY-MM-DD =====
            $tanggalLahir = null;
            if (!empty($row['tanggal_lahir_ddmmyyyy'])) {
                $date = \DateTime::createFromFormat('d/m/Y', $row['tanggal_lahir_ddmmyyyy']);
                if ($date) {
                    $tanggalLahir = $date->format('Y-m-d');
                }
            }

            DB::beginTransaction();

            // ===== 4. Tentukan password =====
            $password = !empty($row['password'])
                ? bcrypt($row['password'])
                : bcrypt($row['nip']); // default pakai NIP jika kosong

            // ===== 5. Ambil status dan akses yayasan =====
            $status = !empty($row['status_1_aktif_0_tidak_aktif']) ? $row['status_1_aktif_0_tidak_aktif'] : '1';
            $aksesYayasan = !empty($row['akses_yayasan_1_ya_0_tidak']) ? $row['akses_yayasan_1_ya_0_tidak'] : '0';

            // ===== 6. Jika akses yayasan = 1, ambil yayasan_id dari user lain dengan unit_id yang sama =====
            $yayasanId = null;
            if ($aksesYayasan == '1') {
                $userWithYayasan = User::where('unit_id', $this->unit_id)
                    ->whereNotNull('yayasan_id')
                    ->first();

                if ($userWithYayasan) {
                    $yayasanId = $userWithYayasan->yayasan_id;
                    Log::info("Yayasan ID found: {$yayasanId} for unit_id: {$this->unit_id}");
                } else {
                    Log::warning("No yayasan_id found for unit_id: {$this->unit_id}");
                }
            }

            // ===== 7. Update atau buat user =====
            $user = User::updateOrCreate(
                [
                    'email' => $row['email'],
                    'unit_id' => $this->unit_id,
                ],
                [
                    'name' => $row['nama_lengkap'],
                    'password' => $password,
                    'rfid_no' => $row['no_rfid'] ?? null,
                    'unit_id' => $this->unit_id,
                    'yayasan_id' => $yayasanId,
                ]
            );

            // ===== 8. Ambil role =====
            $rolePetugas = Roles_petugas::where('name', $row['role'])->first();

            if (!$rolePetugas) {
                Log::warning("Role not found for role: {$row['role']}");
                DB::rollBack();
                return null;
            }

            // ===== 9. Sinkronisasi Spatie Role =====
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );

            if (!$user->hasRole($roleSpatie->name)) {
                $user->assignRole($roleSpatie->name);
            }

            // ===== 10. Konversi jenis kelamin dari format lengkap ke L/P =====
            $jenisKelamin = null;
            if (!empty($row['jenis_kelamin'])) {
                $jenisKelamin = $row['jenis_kelamin'] === 'Laki-laki' ? 'L' : 'P';
            }

            // ===== 11. Update atau buat Officer =====
            $officer = Officer::updateOrCreate(
                [
                    'nip' => $row['nip'],
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                ],
                [
                    'name' => $row['nama_lengkap'],
                    'tempat_lahir' => $row['tempat_lahir'] ?? null,
                    'no_hp' => $row['no_hp'] ?? null,
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                    'user_id' => $user->id,
                    'role_id' => $rolePetugas->id,
                    'nuptk' => $row['nuptk'] ?? null,
                    'nik' => $row['nik'] ?? null,
                    'jenis_kelamin' => $jenisKelamin,
                    'agama' => $row['agama'] ?? null,
                    'tanggal_lahir' => $tanggalLahir,
                    'alamat' => $row['alamat'] ?? null,
                    'bank' => $row['bank'] ?? null,
                    'no_rekening' => $row['no_rekening'] ?? null,
                    'no_kartu_rfid' => $row['no_rfid'] ?? null,
                    'va_guru' => $row['no_va'] ?? null,
                    'jabatan' => $row['jabatan'] ?? null,
                    'akses_yayasan' => $aksesYayasan,
                    'status' => $status
                ]
            );

            DB::commit();
            return $officer;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during officer import: ' . $e->getMessage() . ' | Row: ' . json_encode($row));
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
