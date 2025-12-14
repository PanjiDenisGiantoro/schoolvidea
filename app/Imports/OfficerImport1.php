<?php

namespace App\Imports;

use App\Models\Positions;
use App\Models\User;
use App\Models\Officer;
use App\Models\Roles_petugas;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class OfficerImport1 implements ToModel, WithStartRow
{
    protected $unit_id;
    protected $tahun_ajaran_id;

    public function __construct($unit_id, $tahun_ajaran_id)
    {
        $this->unit_id = $unit_id;
        $this->tahun_ajaran_id = $tahun_ajaran_id;
    }

    public function startRow(): int
    {
        return 2; // Skip header row
    }

    public function model(array $row)
    {
        Log::info('========== OFFICER IMPORT STARTED ==========');
        Log::info('Incoming row data: ' . json_encode($row));

        try {
            // ===== 1. Validasi kolom wajib =====
            Log::info('Step 1: Validating required fields');
            Log::info('nama_lengkap: ' . ($row[3] ?? 'EMPTY')); // Index 3: NAMA LENGKAP
            Log::info('email: ' . ($row[10] ?? 'EMPTY')); // Index 10: EMAIL
            Log::info('role: ' . ($row[11] ?? 'EMPTY')); // Index 11: ROLE
            Log::info('nip: ' . ($row[0] ?? 'EMPTY')); // Index 0: NIP

            if (
                empty($row[3]) || // NAMA LENGKAP
                empty($row[10]) || // EMAIL
                empty($row[11]) || // ROLE
                empty($row[0]) // NIP
            ) {
                Log::warning('⚠️ Skipping row due to missing required fields: ' . json_encode($row));
            }
            Log::info('✓ Required fields validated');

            // ===== 2. Validasi kolom numeric =====
            Log::info('Step 2: Validating numeric fields');
            $numericFields = [
                9 => 'no_hp',    // Index 9: NO HP
                16 => 'no_rfid',  // Index 16: NO RFID
                0 => 'nip',       // Index 0: NIP
                1 => 'nuptk',     // Index 1: NUPTK
                4 => 'nik'        // Index 4: NIK
            ];
            foreach ($numericFields as $index => $fieldName) {
                if (!empty($row[$index]) && !is_numeric($row[$index])) {
                    Log::warning("⚠️ Skipping row due to invalid numeric value in $fieldName: " . json_encode($row));
                    return null;
                }
            }
            Log::info('✓ Numeric fields validated');

            // ===== 3. Konversi tanggal lahir dari DD/MM/YYYY ke YYYY-MM-DD =====
            Log::info('Step 3: Converting date of birth');
            $tanggalLahir = null;
            if (!empty($row[7])) { // Index 7: TANGGAL LAHIR
                Log::info('tanggal_lahir raw: ' . $row[7]);
                $date = \DateTime::createFromFormat('d/m/Y', $row[7]);
                if ($date) {
                    $tanggalLahir = $date->format('Y-m-d');
                    Log::info('✓ Date converted: ' . $tanggalLahir);
                } else {
                    Log::warning('⚠️ Failed to convert date: ' . $row[7]);
                }
            }

            DB::beginTransaction();
            Log::info('✓ Transaction started');

            // ===== 4. Tentukan password =====
            Log::info('Step 4: Setting password');
            $password = !empty($row[2]) // Index 2: PASSWORD
                ? bcrypt($row[2])
                : bcrypt($row[0]); // default pakai NIP jika kosong (Index 0)
            Log::info('✓ Password set (using ' . (!empty($row[2]) ? 'provided password' : 'NIP as default') . ')');
            // ===== 5. Ambil status dan akses yayasan =====
            Log::info('Step 5: Processing status and akses yayasan');
            $status = !empty($row[13]) ? $row[13] : '1'; // Index 13: STATUS
            $aksesYayasan = !empty($row[12]) ? $row[12] : '0'; // Index 12: AKSES YAYASAN
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
            Log::info('User lookup: email=' . $row[10] . ', unit_id=' . $this->unit_id); // Index 10: EMAIL
            $user = User::updateOrCreate(
                [
                    'email' => $row[10], // Index 10: EMAIL
                    'unit_id' => $this->unit_id,
                ],
                [
                    'name' => $row[3], // Index 3: NAMA LENGKAP
                    'password' => $password,
                    'rfid_no' => $row[16] ?? null, // Index 16: NO RFID
                    'unit_id' => $this->unit_id,
                    'yayasan_id' => $yayasanId,
                ]
            );
            Log::info('✓ User created/updated | ID: ' . $user->id);

            // ===== 8. Ambil role =====
            Log::info('Step 8: Finding role');
            Log::info('Looking for role: ' . $row[11]); // Index 11: ROLE
            $rolePetugas = Roles_petugas::where('name', $row[11])->first(); // Index 11: ROLE

            if (!$rolePetugas) {
                $rolePetugas = null;
                Log::warning("⚠️ Role not found for role: {$row[11]}");
            }
            Log::info('✓ Role found | ID: ' . $rolePetugas->id . ' | Name: ' . $rolePetugas->name);

            // ===== 9. Sinkronisasi Spatie Role =====
            Log::info('Step 9: Syncing Spatie Role');
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );
            Log::info('✓ Spatie role synced: ' . $roleSpatie->name);

            if (!$user->hasRole($roleSpatie->name)) {
                $user->assignRole($roleSpatie->name);
                Log::info('✓ Role assigned to user');
            } else {
                Log::info('User already has this role');
            }

            // ===== 10. Jenis kelamin - gunakan format asli dari Excel =====
            Log::info('Step 10: Processing gender');
            $jenisKelamin = null;
            if (!empty($row[5])) { // Index 5: JENIS KELAMIN
                // Gunakan format asli dari Excel (Laki-laki / Perempuan)
                $jenisKelamin = $row[5];
                Log::info('✓ Gender: ' . $jenisKelamin);
            }

            // ===== 11. Update atau buat Officer =====
            Log::info('Step 11: Creating/Updating Officer');
            Log::info('Officer lookup: nip=' . $row[0] . ', unit_id=' . $this->unit_id . ', tahun_ajaran_id=' . $this->tahun_ajaran_id); // Index 0: NIP


            $position = Positions::where('positions_name', $row[15])->first(); // Index 15: JABATAN


            $officer = Officer::updateOrCreate(
                [
                    'nip' => $row[0], // Index 0: NIP
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                ],
                [
                    'name' => $row[3], // Index 3: NAMA LENGKAP
                    'tempat_lahir' => $row[6] ?? null, // Index 6: TEMPAT LAHIR
                    'no_hp' => $row[9] ?? null, // Index 9: NO HP
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                    'user_id' => $user->id,
                    'role_id' => $rolePetugas->id,
                    'nuptk' => $row[1] ?? null, // Index 1: NUPTK
                    'nik' => $row[4] ?? null, // Index 4: NIK
                    'jenis_kelamin' => $jenisKelamin,
                    'agama' => $row[8] ?? null, // Index 8: AGAMA
                    'tanggal_lahir' => $tanggalLahir,
                    'alamat' => $row[14] ?? null, // Index 14: ALAMAT
                    'bank' => $row[18] ?? null, // Index 18: BANK
                    'no_rekening' => $row[19] ?? null, // Index 19: NO REKENING
                    'no_kartu_rfid' => $row[16] ?? null, // Index 16: NO RFID
                    'va_guru' => $row[17] ?? null, // Index 17: NO VA
                    'position_id' => $position->id ?? null
                ]
            );

            Log::info('✓ Officer created/updated | ID: ' . $officer->id . ' | NIP: ' . $officer->nip);

            DB::commit();
            Log::info('✓ Transaction committed successfully');
            Log::info('========== OFFICER IMPORT COMPLETED ==========');

            return $officer;

        } catch (\Exception $e) {
            Log::error('❌ ERROR during officer import');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Row data: ' . json_encode($row));
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
