<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Officer;
use App\Models\Roles_petugas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
                empty($row['name']) ||
                empty($row['email']) ||
                empty($row['role_id']) ||
                empty($row['nip'])
            ) {
                Log::warning('Skipping row due to missing required fields: ' . json_encode($row));
                return null;
            }

            // ===== 2. Validasi kolom numeric =====
            $numericFields = ['no_hp', 'rfid_no', 'nip', 'nuptk', 'nik'];
            foreach ($numericFields as $field) {
                if (!empty($row[$field]) && !is_numeric($row[$field])) {
                    Log::warning("Skipping row due to invalid numeric value in $field: " . json_encode($row));
                    return null;
                }
            }

            // ===== 3. Konversi tanggal lahir dari format Excel =====
            $tanggalLahir = null;
            if (!empty($row['tanggal_lahir'])) {
                if (is_numeric($row['tanggal_lahir'])) {
                    $tanggalLahir = ExcelDate::excelToDateTimeObject($row['tanggal_lahir'])->format('Y-m-d');
                } else {
                    // Jika sudah berupa string tanggal (misal "1995-02-01")
                    $tanggalLahir = date('Y-m-d', strtotime($row['tanggal_lahir']));
                }
            }

            DB::beginTransaction();

            // ===== 4. Tentukan password =====
            $password = !empty($row['password'])
                ? bcrypt($row['password'])
                : bcrypt($row['nip']); // default pakai NIP jika kosong

            // ===== 5. Update atau buat user =====
            $user = User::updateOrCreate(
                [
                    'email' => $row['email'],
                    'unit_id' => $this->unit_id,
                ],
                [
                    'name' => $row['name'],
                    'password' => $password,
                    'rfid_no' => $row['rfid_no'] ?? null,
                    'unit_id' => $this->unit_id,
                ]
            );

            // ===== 6. Ambil role =====
            $rolePetugas = Roles_petugas::where('name', $row['role_id'])->first();

            if (!$rolePetugas) {
                Log::warning("Role not found for role_id: {$row['role_id']}");
                DB::rollBack();
                return null;
            }

            // ===== 7. Sinkronisasi Spatie Role =====
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );

            // Pastikan user memiliki role yang sesuai
            if (!$user->hasRole($roleSpatie->name)) {
                $user->assignRole($roleSpatie->name);
            }

            // ===== 8. Update atau buat data Officer =====
            $officer = Officer::updateOrCreate(
                [
                    'nip' => $row['nip'],
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                ],
                [
                    'name' => $row['name'],
                    'image' => $row['image'] ?? null,
                    'tempat_lahir' => $row['tempat_lahir'] ?? null,
                    'no_hp' => $row['no_hp'] ?? null,
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                    'user_id' => $user->id,
                    'role_id' => $rolePetugas->id,
                    'nuptk' => $row['nuptk'] ?? null,
                    'nik' => $row['nik'] ?? null,
                    'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
                    'agama' => $row['agama'] ?? null,
                    'tanggal_lahir' => $tanggalLahir,
                    'alamat' => $row['alamat'] ?? null,
                    'bank' => $row['bank'] ?? null,
                    'no_rekening' => $row['no_rekening'] ?? null,
                    'no_kartu_rfid' => $row['no_kartu_rfid'] ?? null,
                    'qr_code' => $row['qr_code'] ?? null,
                    'va_guru' => $row['va_guru'] ?? null,
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
