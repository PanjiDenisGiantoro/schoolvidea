<?php
namespace App\Imports;

use App\Models\Positions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PositionImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        Log::info('========== POSITION IMPORT STARTED ==========');
        Log::info('Incoming row data: ' . json_encode($row));

        // Pastikan semua data string
        $row = array_map('strval', $row);

        try {
            if (empty($row['positions_name'])) {
                Log::warning('⚠️ Missing required fields (positions_name), skipping...');
                return null;
            }

            DB::beginTransaction();
            Log::info('✓ Transaction started');

            /**
             * Konversi status: 'aktif' -> '1', 'non_aktif' -> '0'
             */
            $status = '1'; // Default aktif
            if (isset($row['status'])) {
                if (strtolower($row['status']) == 'aktif') {
                    $status = '1';
                } elseif (strtolower($row['status']) == 'non_aktif') {
                    $status = '0';
                } else {
                    $status = $row['status']; // Jika sudah angka, gunakan langsung
                }
            }

            /**
             * Update atau Create Position
             */
            Log::info('Processing Position Data');

            $position = Positions::updateOrCreate(
                [
                    'positions_name' => $row['positions_name'],
                ],
                [
                    'status' => $status,
                ]
            );

            Log::info('✓ Position created/updated | ID: ' . $position->id);

            DB::commit();
            Log::info('✓ Transaction committed successfully');
            Log::info('========== POSITION IMPORT COMPLETED ==========');

            return $position;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERROR during position import');
            Log::error($e->getMessage());
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
