<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Yayasan;
use Illuminate\Http\Request;

class YayasanListController extends Controller
{
    /**
     * Display a listing of yayasans with nested units and tipe_units.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        $query = Yayasan::with(['units' => function ($query) {
            $query->with(['tipe_unit']);
        }]);

        if ($search) {
            $query->where('nama_yayasan', 'like', "%{$search}%");
        }

        $yayasans = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $yayasans
        ]);
    }
}
