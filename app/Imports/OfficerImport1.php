<?php

namespace App\Imports;

use App\Models\Positions;
use App\Models\User;
use App\Models\Officer;
use App\Models\Roles_petugas;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class OfficerImport1 implements ToModel
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
        Log::info('========== OFFICER IMPORT STARTED ==========');
        Log::info('Incoming row data: ' . json_encode($row));

        try {
            // ===== 1. Validasi kolom wajib =====
            if (
                empty($row[0]) || // NIP
                empty($row[3]) || // NAMA
                empty($row[10]) || // EMAIL
                empty($row[11])   // ROLE
            ) {
                Log::warning('⚠️ Required field empty, skipping row');
                return null;
            }

            // ===== 2. Validasi numeric =====
            foreach ([0, 1, 4, 9, 16] as $idx) {
                if (!empty($row[$idx]) && !is_numeric($row[$idx])) {
                    Log::warning("⚠️ Invalid numeric at index {$idx}");
                    return null;
                }
            }

            // ===== 3. Tanggal lahir =====
            $tanggalLahir = null;
            if (!empty($row[7])) {
                $date = \DateTime::createFromFormat('d/m/Y', $row[7]);
                if ($date) {
                    $tanggalLahir = $date->format('Y-m-d');
                }
            }

            DB::beginTransaction();

            // ===== 4. Password =====
            $password = !empty($row[2])
                ? bcrypt($row[2])
                : bcrypt($row[0]); // default NIP

            // ===== 5. Status & akses yayasan =====
            $aksesYayasan = $row[12] ?? '0';

            $yayasanId = null;
            if ($aksesYayasan == '1') {
                $userWithYayasan = User::where('unit_id', $this->unit_id)
                    ->whereNotNull('yayasan_id')
                    ->first();

                $yayasanId = $userWithYayasan->yayasan_id ?? null;
            }

            // ===== 6. USER =====
            $user = User::updateOrCreate(
                [
                    'email' => $row[10],
                    'unit_id' => $this->unit_id,
                ],
                [
                    'name' => $row[3],
                    'password' => $password,
                    'rfid_no' => $row[16] ?? null,
                    'unit_id' => $this->unit_id,
                    'yayasan_id' => $yayasanId,
                ]
            );

            // ===== 7. ROLE =====
            $rolePetugas = Roles_petugas::where('name', $row[11])->first();
            if (!$rolePetugas) {
                DB::rollBack();
                return null;
            }

            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );

            $user->syncRoles([$roleSpatie->name]);

            // ===== 8. Officer =====
            $officer = Officer::updateOrCreate(
                [
                    'nip' => $row[0],
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                ],
                [
                    'name' => $row[3],
                    'tempat_lahir' => $row[6] ?? null,
                    'no_hp' => $row[9] ?? null,
                    'user_id' => $user->id,
                    'role_id' => $rolePetugas->id,
                    'nuptk' => $row[1] ?? null,
                    'nik' => $row[4] ?? null,
                    'jenis_kelamin' => $row[5] ?? null,
                    'agama' => $row[8] ?? null,
                    'tanggal_lahir' => $tanggalLahir,
                    'alamat' => $row[14] ?? null,
                    'bank' => $row[18] ?? null,
                    'no_rekening' => $row[19] ?? null,
                    'no_kartu_rfid' => $row[16] ?? null,
                    'va_guru' => $row[17] ?? null,
                ]
            );

            DB::commit();
            Log::info('✓ Officer import success | ID: ' . $officer->id);
            return $officer;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return null;
        }
    }


    public function chunkSize(): int
    {
        return 1000;
    }
}
