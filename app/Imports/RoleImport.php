<?php
namespace App\Imports;

use App\Models\Roles;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RoleImport implements ToModel
{
    public function model(array $row)
    {
        Log::info('========== ROLE IMPORT STARTED ==========');
        Log::info('Incoming row data: ' . json_encode($row));

        // Pastikan semua data string
        $row = array_map('strval', $row);

        try {
            // ===== 1. Ambil role dari index 0 =====
            $roleName = $row[0] ?? null;

            if (empty($roleName)) {
                Log::warning('⚠️ Role kosong, skip baris');
                return null;
            }

            Log::info('Processing Role: ' . $roleName);

            /**
             * 2. Update atau Create Role
             * guard_name = web
             */
            $role = Roles::firstOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );

            Log::info('✓ Role created/exists | ID: ' . $role->id);
            Log::info('========== ROLE IMPORT COMPLETED ==========');

            return $role;

        } catch (\Exception $e) {
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
