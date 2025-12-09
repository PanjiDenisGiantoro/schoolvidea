<?php
namespace App\Imports;

use App\Models\Roles;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RoleImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        Log::info('========== ROLE IMPORT STARTED ==========');
        Log::info('Incoming row data: ' . json_encode($row));

        // Pastikan semua data string
        $row = array_map('strval', $row);

        try {
            // Header sekarang adalah 'ROLE', bukan 'name'
            $roleName = $row['nama_roles'] ?? null;

            if (empty($roleName)) {
                Log::warning('⚠️ Missing required fields (role), skipping...');
                return null;
            }

//            DB::beginTransaction();
            Log::info('✓ Transaction started');

            /**
             * Update atau Create Role
             * guard_name statis = 'web'
             */
            Log::info('Processing Role Data');

            $role = Roles::where('name', $roleName)->first();
            if (!$role) {
                $role = Roles::create([
                    'name' => $roleName,
                    'guard_name' => 'web',
                ]);
            }
//            // Juga buat di Spatie Permission jika belum ada
//            \Spatie\Permission\Models\Role::firstOrCreate(
//                ['name' => $roleName],
//                ['guard_name' => 'web']
//            );

            Log::info('✓ Role created/updated | ID: ' . $role->id);

//            DB::commit();
            Log::info('✓ Transaction committed successfully');
            Log::info('========== ROLE IMPORT COMPLETED ==========');

            return $role;

        } catch (\Exception $e) {
//            DB::rollBack();
            Log::error('❌ ERROR during role import');
            Log::error($e->getMessage());
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
