<?php
namespace App\Imports;

use App\Models\User;
use App\Models\Officer;
use App\Models\Roles_petugas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OfficerImport implements ToModel, WithHeadingRow
{
    protected $unit_id;
    protected $tahun_ajaran_id;

    // Constructor untuk unit_id dan tahun_ajaran_id
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
            // Validasi kolom yang dibutuhkan
            if (
                empty($row['name']) || empty($row['email']) || empty($row['password']) ||
                empty($row['role_id']) || empty($row['nip'])
            ) {
                Log::warning('Skipping row due to missing required fields: ' . json_encode($row));
                return null;  // Skip this row if any required fields are missing
            }

            // Validasi bahwa kolom numeric valid
            $numericFields = ['no_hp', 'rfid_no', 'nip', 'nuptk', 'nik'];
            foreach ($numericFields as $field) {
                if (empty($row[$field])) continue;  // Skip empty fields
                if (!is_numeric($row[$field])) {
                    Log::warning("Skipping row due to invalid $field value: " . json_encode($row));
                    return null;  // Skip this row if any numeric field is invalid
                }
            }

            DB::beginTransaction();  // Mulai transaksi untuk memastikan konsistensi

            if($row['password'] == ''){
                $password = bcrypt($row['nip']);
            }else{
                $password = bcrypt($row['password']);
            }
            // 1. Update atau buat User berdasarkan email
            $user = User::updateOrCreate(
                ['email' => $row['email'],
                    'unit_id' => $this->unit_id], // Kondisi pencarian berdasarkan email
                [
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => $password,
                    'rfid_no' => $row['rfid_no'],
                    'unit_id' => $this->unit_id,
                ]
            );

            // 2. Cari role dari Roles_petugas berdasarkan role_id
            $rolePetugas = Roles_petugas::where('name', $row['role_id'])->first();
            if (!$rolePetugas) {
                throw new \Exception('Role not found for role_id: ' . $row['role_id']);
            }

            // 3. Create Spatie role if not already exist
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );

            // Menetapkan role ke user
            $user->assignRole($roleSpatie->name);

            // 4. Update atau buat Officer berdasarkan nip
            $officer = Officer::updateOrCreate(
                ['nip' => $row['nip'],
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id], // Kondisi pencarian berdasarkan nip
                [
                    'name' => $row['name'],
                    'nip' => $row['nip'],
                    'iamge' => $row['image'],
                    'tempat_lahir' => $row['tempat_lahir'],
                    'no_hp' => $row['no_hp'],
                    'unit_id' => $this->unit_id,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                    'user_id' => $user->id,
                    'role_id' => $rolePetugas->id,
                    'nuptk' => $row['nuptk'],
                    'nik' => $row['nik'],
                    'jenis_kelamin' => $row['jenis_kelamin'],
                    'agama' => $row['agama'],
                    'tanggal_lahir' => $row['tanggal_lahir'],
                    'alamat' => $row['alamat'],
                    'bank' => $row['bank'],
                    'no_rekening' => $row['no_rekening'],
                    'no_kartu_rfid' => $row['no_kartu_rfid'],
                    'qr_code' => $row['qr_code'],
                    'va_guru' => $row['va_guru'],
                ]
            );

            DB::commit();  // Commit transaksi

            return $officer;  // Mengembalikan model officer yang berhasil diproses

        } catch (\Exception $e) {
            DB::rollBack();  // Rollback transaksi jika ada error
            Log::error('Error during officer import: ' . $e->getMessage());
            return null;  // Skip this row in case of error
        }
    }

    public function chunkSize(): int
    {
        return 100; // Proses 100 baris data per chunk untuk menghindari timeout
    }
}
