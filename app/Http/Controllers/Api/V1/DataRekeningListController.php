<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DataRekening;
use Illuminate\Http\Request;

class DataRekeningListController extends Controller
{
    /**
     * Display a listing of data rekenings with nested units.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $unitId = $request->get('unit_id');

        $query = DataRekening::with(['unit']);

        if ($search) {
            $query->where('nomor_rekening', 'like', "%{$search}%")
                  ->orWhere('nama_rekening', 'like', "%{$search}%");
        }

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        $dataRekenings = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $dataRekenings
        ]);
    }
}
