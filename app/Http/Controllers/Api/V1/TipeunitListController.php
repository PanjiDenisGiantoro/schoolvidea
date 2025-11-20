<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tipeunit;
use Illuminate\Http\Request;

class TipeunitListController extends Controller
{
    /**
     * Display a listing of tipe_units.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        $query = Tipeunit::query();

        if ($search) {
            $query->where('nama_tipe', 'like', "%{$search}%");
        }

        $tipeunits = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tipeunits
        ]);
    }
}
