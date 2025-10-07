<?php
namespace App\Imports;

use App\Models\Kelas;
use App\Models\Roles;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Roles_petugas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SiswaImport implements ToModel, WithHeadingRow
{
    protected $unit_id;
    protected $tahun_ajaran_id;

    public function __construct($unit_id, $tahun_ajaran_id)
    {
        $this->unit_id = $unit_id;
        $this->tahun_ajaran_id = $tahun_ajaran_id;
    }

    /**
     * Map the incoming row to a model
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Log the incoming row data to inspect
        Log::info('Incoming row data: ' . json_encode($row));

        // Convert all row values to string to avoid issues with empty cells
        $row = array_map('strval', $row);

        try {
            // Validate that required fields are not empty or null
            if (
                empty($row['name']) || empty($row['email']) || empty($row['password'])
            ) {
                Log::warning('Skipping row due to missing required fields: ' . json_encode($row));
                return null;  // Skip this row if any required fields are missing
            }

            DB::beginTransaction();  // Start a transaction to ensure consistency

            // 1. Update or Create User based on email and name
            $user = User::updateOrCreate(
                ['email' => $row['email'], 'name' => $row['name'],
                    'unit_id' => $this->unit_id
                    ], // Find by email and name
                [
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => bcrypt($row['password']),
                    'rfid_no' => $row['rfid_no'],
                    'unit_id' => $this->unit_id,
                ]
            );

            // 2. Find the role from Roles_petugas
            $rolePetugas = Roles::where('name', 'siswa')->first();
            if (!$rolePetugas) {
                Log::warning('Role not found: siswa');
                return null;
            }

            // 3. Create or get Spatie role if not already exist
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );

            $user->assignRole($roleSpatie->name);

            // 4. Find Kelas based on 'kelas' name
            $kelas = Kelas::where('nama_kelas', $row['kelas'])->first();
            $kelas_id = $kelas ? $kelas->id : null;

            // Skip if kelas not found
            if (!$kelas_id) {
                Log::warning('Kelas not found: ' . $row['kelas']);
                return null;
            }

            // 5. Update or Create Siswa based on nisn
            $siswa = Siswa::updateOrCreate(
                ['nisn' => $row['nisn'],
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id], // Find by nisn
                [
                    'nisn' => $row['nisn'] ?? '',
                    'kelas_id' => $kelas_id,
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                    'user_id' => $user->id,
                    'rfid_no' => $row['rfid_no'],
                    'va_siswa' => $row['va_siswa'],
                    'jenis_kelamin' => $row['jenis_kelamin'] == 'Laki-laki' ? 'L' : 'P',
                    'agama' => $row['agama'],
                    'no_hp_ortu' => $row['no_hp_orang_tua'],
                    'nama_ortu' => $row['nama_orang_tua'],
                    'bank' => $row['bank'],
                    'no_rekening' => $row['no_rekening'],
                ]
            );

            DB::commit();  // Commit the transaction

            return $siswa;  // Return the model instance for the import to work

        } catch (\Exception $e) {
            DB::rollBack();  // Rollback in case of error
            Log::error('Error during siswa import: ' . $e->getMessage());
            return null;  // Skip this row in case of error
        }
    }

    public function chunkSize(): int
    {
        return 100; // Set ukuran chunk untuk menghindari timeout
    }
}
