<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanListController extends Controller
{
    /**
     * Display a listing of jurusans with nested units.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $unitId = $request->get('unit_id');

        $query = Jurusan::with(['unit', 'tahun_ajaran']);

        if ($search) {
            $query->where('nama_jurusan', 'like', "%{$search}%");
        }

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        $jurusans = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $jurusans
        ]);
    }
}
