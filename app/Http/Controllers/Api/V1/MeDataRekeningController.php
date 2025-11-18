<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DataRekening;
use Illuminate\Http\Request;

class MeDataRekeningController extends Controller
{
    /**
     * Get data rekenings for authenticated user's unit.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();

        if (!$user || !$user->unit_id) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have a unit assigned'
            ], 403);
        }

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        $query = DataRekening::with(['unit'])
            ->where('unit_id', $user->unit_id);

        if ($search) {
            $query->where('nomor_rekening', 'like', "%{$search}%")
                  ->orWhere('nama_rekening', 'like', "%{$search}%");
        }

        $dataRekenings = $query->paginate($perPage);

        $unit = $user->units;

        return response()->json([
            'success' => true,
            'unit' => $unit ? [
                'id' => $unit->id,
                'nama_unit' => $unit->nama_unit
            ] : [
                'id' => $user->unit_id,
                'nama_unit' => null
            ],
            'data' => $dataRekenings
        ]);
    }
}
