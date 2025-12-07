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

class SiswaImport implements ToModel, WithHeadingRow
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
        $row = array_map('strval', $row);

        try {
            if (empty($row['name']) || empty($row['email']) || empty($row['password'])) {
                Log::warning('⚠️ Missing required fields, skipping...');
                return null;
            }

            DB::beginTransaction();
            Log::info('✓ Transaction started');

            /**
             * 1. Cek atau buat user berdasarkan email saja
             */
            Log::info('Step 1: Processing User Data');

            $user = User::where('email', $row['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => bcrypt($row['password']),
                    'rfid_no' => $row['rfid_no'],
                    'unit_id' => $this->unit_id,
                ]);
                Log::info('✓ New user created: ' . $user->id);
            } else {
                // update data existing user
                $user->update([
                    'name' => $row['name'],
                    'rfid_no' => $row['rfid_no'],
                    'unit_id' => $this->unit_id,
                ]);
                Log::info('✓ Existing user updated: ' . $user->id);
            }

            /**
             * 2. Role assignment
             */
            Log::info('Step 2: Assigning Role');
            $rolePetugas = Roles::where('name', 'siswa')->first();
            if (!$rolePetugas) {
                Log::warning('⚠️ Role "siswa" not found');
                DB::rollBack();
                return null;
            }

            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );

            if (!$user->hasRole($roleSpatie->name)) {
                $user->assignRole($roleSpatie->name);
                Log::info('✓ Role assigned: ' . $roleSpatie->name);
            } else {
                Log::info('Role already assigned to user');
            }

            /**
             * 3. Kelas lookup
             */
            Log::info('Step 3: Finding Kelas');
            $kelas = Kelas::where('nama_kelas', $row['kelas'])->first();

            if (!$kelas) {
                Log::warning('⚠️ Kelas not found: "' . $row['kelas'] . '"');
                DB::rollBack();
                return null;
            }

            /**
             * 4. Update/Create Siswa
             */
            Log::info('Step 4: Processing Siswa Data');

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
                    'rfid_no' => $row['rfid_no'],
                    'va_siswa' => $row['va_siswa'],
                    'jenis_kelamin' => $row['jenis_kelamin'] === 'Laki-laki' ? 'L' : 'P',
                    'agama' => $row['agama'],
                    'no_hp_ortu' => $row['no_hp_orang_tua'],
                    'nama_ortu' => $row['nama_orang_tua'],
                    'bank' => $row['bank'],
                    'no_rekening' => $row['no_rekening'],
                    'status' => '1'
                ]
            );

            Log::info('✓ Siswa created/updated | ID: ' . $siswa->id);

            /**
             * 5. Create or check Saldo_keuangan
             */
            Log::info('Step 5: Processing Saldo_keuangan');

            $saldo = Saldo_keuangan::firstOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'saldo_akhir' => 0,
                    'status' => 0,
                ]
            );

            DB::commit();
            Log::info('✓ Transaction committed successfully');
            Log::info('========== SISWA IMPORT COMPLETED ==========');

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
