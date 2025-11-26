<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideaclassApiHelper
{
    private $baseUrl = 'https://videaclass.com/api/v1';
    private $bearerKey = 'd4ZKJcFcACb8oHQKb3VV9MeTfu0VRboDDkypL3GH81lcSMFYqdH8ElTzVuVNdgcusoGP4l9wDwVXWWnltJu3QLHRhFPqzezJHIEbbSopIv3uGeCSsCOdo2r5VRhTGccp';

    /**
     * Get attendance session report dari Videaclass API
     *
     * @param string $unitId Unit ID di Videaclass
     * @param int $page Halaman data (default: 1)
     * @param int $limit Jumlah data per halaman (default: 50)
     * @param string|null $search Search parameter
     * @param bool $isCount Parameter for counting
     * @return array|null
     */
    public function getAttendanceSessionReport($unitId, $page = 1, $limit = 50, $search = null, $isCount = true)
    {
        try {
            $url = "{$this->baseUrl}/{$unitId}/attendance/session/report";

            $params = [
                'page' => $page,
                'limit' => $limit,
                'is_count' => $isCount ? 'true' : 'false',
            ];

            if ($search) {
                $params['search'] = $search;
            }

            $response = Http::timeout(30)->withHeaders([
                'Authorization' => "Bearer {$this->bearerKey}",
                'Accept' => 'application/json',
            ])->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            } else {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $response->body();

                Log::error('Videaclass API Error', [
                    'status' => $response->status(),
                    'message' => $errorMessage,
                    'body' => $response->body(),
                    'url' => $url,
                    'unit_id' => $unitId,
                ]);

                // Return error dengan detail
                return [
                    'error' => true,
                    'status' => $response->status(),
                    'message' => $errorMessage,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Videaclass API Exception: ' . $e->getMessage(), [
                'unit_id' => $unitId,
                'url' => $url ?? 'URL not set',
            ]);

            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get attendance data untuk sinkronisasi presensi
     *
     * @param string $unitId Unit ID di Videaclass
     * @param string|null $search Search parameter (misalnya nama guru)
     * @return array|null
     */
    public function syncAttendanceData($unitId, $search = null)
    {
        return $this->getAttendanceSessionReport($unitId, 1, 50, $search, true);
    }
}
