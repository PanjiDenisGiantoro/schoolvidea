<?php
namespace App\Imports;

use App\Models\Positions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;

class PositionImport implements ToModel
{
    public function model(array $row)
    {
        Log::info('========== POSITION IMPORT STARTED ==========');
        Log::info('Incoming row data: ' . json_encode($row));

        // Pastikan semua data string
        $row = array_map('strval', $row);

        try {
            // ===== 1. Validasi wajib =====
            if (empty($row[0])) { // positions_name wajib
                Log::warning('⚠️ positions_name kosong, skip baris');
                return null;
            }

            // ===== 2. Status =====
            $status = '1'; // default aktif
            if (!empty($row[1])) {
                if (strtolower($row[1]) === 'aktif') {
                    $status = '1';
                } elseif (strtolower($row[1]) === 'non_aktif') {
                    $status = '0';
                } else {
                    $status = $row[1];
                }
            }

            DB::beginTransaction();

            // ===== 3. Update / Create Position =====
            $position = Positions::updateOrCreate(
                [
                    'positions_name' => $row[0],
                ],
                [
                    'status' => $status,
                ]
            );

            DB::commit();

            Log::info('✓ Position import success | ID: ' . $position->id);
            Log::info('========== POSITION IMPORT COMPLETED ==========');

            return $position;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERROR during position import');
            Log::error($e->getMessage());
            Log::error('Row data: ' . json_encode($row));
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
