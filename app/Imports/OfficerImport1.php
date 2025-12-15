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

class OfficerImport1 implements ToModel, WithHeadingRow
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
            Log::info('Step 1: Validating required fields');
            Log::info('nama_lengkap: ' . ($row['nama_lengkap'] ?? 'EMPTY'));
            Log::info('email: ' . ($row['email'] ?? 'EMPTY'));
            Log::info('role: ' . ($row['role'] ?? 'EMPTY'));
            Log::info('nip: ' . ($row['nip'] ?? 'EMPTY'));

            if (
                empty($row['nama_lengkap']) ||
                empty($row['email']) ||
                empty($row['role']) ||
                empty($row['nip'])
            ) {
                Log::warning('⚠️ Skipping row due to missing required fields: ' . json_encode($row));
                return null;
            }
            Log::info('✓ Required fields validated');

            // ===== 2. Validasi kolom numeric =====
            Log::info('Step 2: Validating numeric fields');
            $numericFields = ['no_hp', 'no_rfid', 'nip', 'nuptk', 'nik'];
            foreach ($numericFields as $field) {
                if (!empty($row[$field]) && !is_numeric($row[$field])) {
                    Log::warning("⚠️ Skipping row due to invalid numeric value in $field: " . json_encode($row));
                    return null;
                }
            }
            Log::info('✓ Numeric fields validated');

            // ===== 3. Konversi tanggal lahir dari DD/MM/YYYY ke YYYY-MM-DD =====
            Log::info('Step 3: Converting date of birth');
            $tanggalLahir = null;
            if (!empty($row['tanggal_lahir_ddmmyyyy'])) {
                Log::info('tanggal_lahir_ddmmyyyy raw: ' . $row['tanggal_lahir_ddmmyyyy']);
                $date = \DateTime::createFromFormat('d/m/Y', $row['tanggal_lahir_ddmmyyyy']);
                if ($date) {
                    $tanggalLahir = $date->format('Y-m-d');
                    Log::info('✓ Date converted: ' . $tanggalLahir);
                } else {
                    Log::warning('⚠️ Failed to convert date: ' . $row['tanggal_lahir_ddmmyyyy']);
                }
            }

            DB::beginTransaction();
            Log::info('✓ Transaction started');

            // ===== 4. Tentukan password =====
            Log::info('Step 4: Setting password');
            $password = !empty($row['password'])
                ? bcrypt($row['password'])
                : bcrypt($row['nip']); // default pakai NIP jika kosong
            Log::info('✓ Password set (using ' . (!empty($row['password']) ? 'provided password' : 'NIP as default') . ')');

            // ===== 5. Ambil status dan akses yayasan =====
            Log::info('Step 5: Processing status and akses yayasan');
            $status = !empty($row['status_1_aktif_0_tidak_aktif']) ? $row['status_1_aktif_0_tidak_aktif'] : '1';
            $aksesYayasan = !empty($row['akses_yayasan_1_ya_0_tidak']) ? $row['akses_yayasan_1_ya_0_tidak'] : '0';
            Log::info('Status: ' . $status . ' | Akses Yayasan: ' . $aksesYayasan);

            // ===== 6. Jika akses yayasan = 1, ambil yayasan_id dari user lain dengan unit_id yang sama =====
            Log::info('Step 6: Checking yayasan access');
            $yayasanId = null;
            if ($aksesYayasan == '1') {
                Log::info('Akses yayasan = 1, searching for yayasan_id...');
                $userWithYayasan = User::where('unit_id', $this->unit_id)
                    ->whereNotNull('yayasan_id')
                    ->first();

                if ($userWithYayasan) {
                    $yayasanId = $userWithYayasan->yayasan_id;
                    Log::info("✓ Yayasan ID found: {$yayasanId} for unit_id: {$this->unit_id}");
                } else {
                    Log::warning("⚠️ No yayasan_id found for unit_id: {$this->unit_id}");
                }
            } else {
                Log::info('Akses yayasan = 0, skipping yayasan_id lookup');
            }

            // ===== 7. Update atau buat user =====
            Log::info('Step 7: Creating/Updating User');
            Log::info('User lookup: email=' . $row['email'] . ', unit_id=' . $this->unit_id);

            // Log query data before execution
            Log::info('User updateOrCreate conditions: ' . json_encode([
                'email' => $row['email'],
                'unit_id' => $this->unit_id,
            ]));
            Log::info('User updateOrCreate data: ' . json_encode([
                'name' => $row['nama_lengkap'],
                'rfid_no' => $row['no_rfid'] ?? null,
                'unit_id' => $this->unit_id,
                'yayasan_id' => $yayasanId,
            ]));

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
            Log::info('✓ User created/updated | ID: ' . $user->id . ' | Was Recently Created: ' . ($user->wasRecentlyCreated ? 'Yes' : 'No'));

            // ===== 8. Ambil role =====
            Log::info('Step 8: Finding role');
            Log::info('Looking for role: ' . $row['role']);
            $rolePetugas = Roles_petugas::where('name', $row['role'])->first();

            if (!$rolePetugas) {
                Log::warning("⚠️ Role not found for role: {$row['role']}");
            }
            Log::info('✓ Role found | ID: ' . $rolePetugas->id . ' | Name: ' . $rolePetugas->name);

            // ===== 9. Sinkronisasi Spatie Role =====
            Log::info('Step 9: Syncing Spatie Role');
            Log::info('Spatie role firstOrCreate data: ' . json_encode([
                'name' => $rolePetugas->name,
                'guard_name' => 'web'
            ]));

            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );
            Log::info('✓ Spatie role synced: ' . $roleSpatie->name . ' | ID: ' . $roleSpatie->id);

            if (!$user->hasRole($roleSpatie->name)) {
                $user->assignRole($roleSpatie->name);
                Log::info('✓ Role assigned to user | User ID: ' . $user->id . ' | Role: ' . $roleSpatie->name);
            } else {
                Log::info('User already has this role | User ID: ' . $user->id . ' | Role: ' . $roleSpatie->name);
            }

            // ===== 10. Jenis kelamin - gunakan format asli dari Excel =====
            Log::info('Step 10: Processing gender');
            $jenisKelamin = null;
            if (!empty($row['jenis_kelamin'])) {
                // Gunakan format asli dari Excel (Laki-laki / Perempuan)
                $jenisKelamin = $row['jenis_kelamin'];
                Log::info('✓ Gender: ' . $jenisKelamin);
            }

            // ===== 11. Update atau buat Officer =====
            Log::info('Step 11: Creating/Updating Officer');
            Log::info('Officer lookup: nip=' . $row['nip'] . ', unit_id=' . $this->unit_id . ', tahun_ajaran_id=' . $this->tahun_ajaran_id);


            $position = Positions::where('positions_name', $row['jabatan'])->first();

            if (!$position) {
                Log::warning("⚠️ Position not found for position: {$row['jabatan']}");
            } else {
                Log::info('✓ Position found | ID: ' . $position->id . ' | Name: ' . $position->positions_name);
            }

            // Log query data before execution
            Log::info('Officer updateOrCreate conditions: ' . json_encode([
                'nip' => $row['nip'],
                'unit_id' => $this->unit_id,
                'tahun_ajaran_id' => $this->tahun_ajaran_id,
            ]));
            Log::info('Officer updateOrCreate data: ' . json_encode([
                'name' => $row['nama_lengkap'],
                'tempat_lahir' => $row['tempat_lahir'] ?? null,
                'no_hp' => $row['no_hp'] ?? null,
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
//                'position_id' => $position->id ?? null
            ]));

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
//                    'position_id' => empty($position->id) ? null : $position->id,
                ]
            );

            Log::info('✓ Officer created/updated | ID: ' . $officer->id . ' | NIP: ' . $officer->nip . ' | Was Recently Created: ' . ($officer->wasRecentlyCreated ? 'Yes' : 'No'));

            DB::commit();
            Log::info('✓ Transaction committed successfully');
            Log::info('========== OFFICER IMPORT COMPLETED ==========');

            return $officer;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERROR during officer import');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Error file: ' . $e->getFile());
            Log::error('Error line: ' . $e->getLine());
            Log::error('Error code: ' . $e->getCode());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Row data: ' . json_encode($row));

            // Log SQL query if available (for database errors)
            if (method_exists($e, 'getSql')) {
                Log::error('SQL Query: ' . $e->getSql());
            }

            return null;
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
