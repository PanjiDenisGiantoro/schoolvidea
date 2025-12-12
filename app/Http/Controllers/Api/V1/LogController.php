<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    /**
     * API v1 - Get last 500 lines from laravel.log
     * GET /api/v1/logs/laravel
     */
    public function getLaravelLog(Request $request)
    {
        try {
            $logPath = storage_path('logs/laravel.log');

            // Check if log file exists
            if (!File::exists($logPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log file tidak ditemukan',
                    'data' => []
                ], 404);
            }

            // Read the file
            $logContent = File::get($logPath);

            // Split into lines
            $lines = explode("\n", $logContent);

            // Get last 500 lines
            $last500Lines = array_slice($lines, -500);

            // Remove empty lines at the end if any
            $last500Lines = array_filter($last500Lines, function($line) {
                return trim($line) !== '';
            });

            // Reindex array
            $last500Lines = array_values($last500Lines);

            return response()->json([
                'success' => true,
                'message' => 'Log berhasil diambil',
                'data' => [
                    'total_lines' => count($last500Lines),
                    'logs' => $last500Lines
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil log',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
