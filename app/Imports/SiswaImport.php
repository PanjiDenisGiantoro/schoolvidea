<?php
namespace App\Imports;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Roles_petugas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
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

        try {
            // Validate that required fields are not empty or null
            if (
                empty($row['name']) || empty($row['email']) || empty($row['password']) ||
                empty($row['role_id']) || empty($row['nip']) || empty($row['nuptk']) || empty($row['nik'])
            ) {
                Log::warning('Skipping row due to missing required fields: ' . json_encode($row));
                return null;  // Skip this row if any required fields are missing
            }

            // Convert numeric values to string
            $row['no_hp'] = (string) $row['no_hp'];
            $row['rfid_no'] = (string) $row['rfid_no'];
            $row['nip'] = (string) $row['nip'];
            $row['nuptk'] = (string) $row['nuptk'];
            $row['nik'] = (string) $row['nik'];
            $row['password'] = (string) $row['password']; // Convert password to string

            DB::beginTransaction();  // Start a transaction to ensure consistency

            // 1. Create User
            $user = User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => bcrypt($row['password']),
                'rfid_no' => $row['rfid_no'],
                'unit_id' => $this->unit_id,
            ]);

            // 2. Find the role from Roles_petugas
            $rolePetugas = Roles_petugas::where('name', $row['role_id'])->first();
            if (!$rolePetugas) {
                throw new \Exception('Role not found for role_id: ' . $row['role_id']);
            }

            // 3. Create Spatie role if not already exist
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );

            $user->assignRole($roleSpatie->name);

            // 4. Create Siswa
            $siswa = Siswa::create([
                'nisn' => $row['nisn'],
                'name' => $row['name'],
                'email' => $row['email'],
                'tempat_lahir' => $row['tempat_lahir'],
                'no_hp' => $row['no_hp'],
                'rfid_no' => $row['rfid_no'],
                'va_siswa' => $row['va_siswa'],
                'nis' => $row['nis'],
                'nik' => $row['nik'],
                'jenis_kelamin' => $row['jenis_kelamin'],
                'agama' => $row['agama'],
                'no_hp_ortu' => $row['no_hp_ortu'],
                'nama_ortu' => $row['nama_ortu'],
                'bank' => $row['bank'],
                'no_rekening' => $row['no_rekening'],
            ]);

            DB::commit();  // Commit the transaction

            return $siswa;  // Return the model instance for the import to work

        } catch (\Exception $e) {
            DB::rollBack();  // Rollback in case of error
            Log::error('Error during siswa import: ' . $e->getMessage());
            return null;  // Skip this row in case of error
        }
    }
}
