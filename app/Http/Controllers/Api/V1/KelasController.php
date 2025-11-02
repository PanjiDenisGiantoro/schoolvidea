<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $unitId = $request->get('unit_id');

        $query = Kelas::with(['unit', 'jurusan']);

        if ($search) {
            $query->where('nama_kelas', 'like', "%{$search}%");
        }

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        $kelas = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $kelas
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|integer',
            'unit_id' => 'required|exists:units,id',
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'kapasitas' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $kelas = Kelas::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kelas created successfully',
            'data' => $kelas->load(['unit', 'jurusan'])
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $kelas = Kelas::with(['unit', 'jurusan', 'siswas'])->find($id);

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $kelas
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $kelas = Kelas::find($id);

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_kelas' => 'sometimes|required|string|max:255',
            'tingkat' => 'sometimes|required|integer',
            'unit_id' => 'sometimes|required|exists:units,id',
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'kapasitas' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $kelas->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kelas updated successfully',
            'data' => $kelas->load(['unit', 'jurusan'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $kelas = Kelas::find($id);

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas not found'
            ], 404);
        }

        $kelas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kelas deleted successfully'
        ]);
    }

    /**
     * Get siswa in kelas.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSiswa($id)
    {
        $kelas = Kelas::with('siswas')->find($id);

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $kelas->siswas
        ]);
    }
}
