<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitListController extends Controller
{
    /**
     * Display a listing of units with nested tipe_unit and data_rekenings.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $yayasanId = $request->get('yayasan_id');

        $query = Unit::with(['tipe_unit', 'yayasan', 'dataRekenings']);

        if ($search) {
            $query->where('nama_unit', 'like', "%{$search}%");
        }

        if ($yayasanId) {
            $query->where('yayasan_id', $yayasanId);
        }

        $units = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $units
        ]);
    }
}
