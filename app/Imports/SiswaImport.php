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
            // Validasi field required berdasarkan urutan baru
            if (empty($row['nama_lengkap']) || empty($row['email']) || empty($row['password'])) {
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
                    'name' => $row['nama_lengkap'],
                    'email' => $row['email'],
                    'password' => bcrypt($row['password']),
                    'rfid_no' => $row['no_rfid'] ?? null,
                    'unit_id' => $this->unit_id,
                ]);
                Log::info('✓ New user created: ' . $user->id);
            } else {
                // update data existing user
                $user->update([
                    'name' => $row['nama_lengkap'],
                    'rfid_no' => $row['no_rfid'] ?? null,
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

            // Konversi status: ambil dari excel atau default '1'
            $status = '1'; // Default aktif
            if (isset($row['status_1_aktif_0_tidak_aktif'])) {
                $status = $row['status_1_aktif_0_tidak_aktif'];
            }

            // Konversi tanggal lahir dari DD/MM/YYYY ke YYYY-MM-DD jika ada
            $tanggalLahir = null;
            if (!empty($row['tanggal_lahir_ddmmyyyy'])) {
                $date = \DateTime::createFromFormat('d/m/Y', $row['tanggal_lahir_ddmmyyyy']);
                if ($date) {
                    $tanggalLahir = $date->format('Y-m-d');
                }
            }

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
                    'jenis_kelamin' => $row['jenis_kelamin'] === 'Laki-laki' ? 'L' : 'P',
                    'agama' => $row['agama'] ?? null,
                    'no_hp_ortu' => $row['no_hp_ortu'] ?? null,
                    'nama_ortu' => $row['nama_orang_tua'] ?? null,
                    'bank' => $row['bank'] ?? null,
                    'no_rekening' => $row['no_rekening'] ?? null,
                    'status' => $status,
                    'nik' => $row['nik'] ?? null,
                    'tanggal_lahir' => $tanggalLahir,
                    'no_hp_siswa' => $row['no_hp_siswa'] ?? null,
                    'alamat' => $row['alamat'] ?? null,
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
