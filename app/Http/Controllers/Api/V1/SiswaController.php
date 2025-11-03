<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiswaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/siswa",
     *     tags={"Siswa"},
     *     summary="Get list of siswa",
     *     description="Returns paginated list of siswa with optional filters",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, NIS, or NISN",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="kelas_id",
     *         in="query",
     *         description="Filter by class ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="unit_id",
     *         in="query",
     *         description="Filter by unit ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $kelasId = $request->get('kelas_id');
        $unitId = $request->get('unit_id');

        $query = Siswa::with(['kelas', 'unit', 'jurusan']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        $siswa = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $siswa
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
            'nama' => 'required|string|max:255',
            'nis' => 'required|string|unique:siswas,nis',
            'nisn' => 'nullable|string|unique:siswas,nisn',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'kelas_id' => 'required|exists:kelas,id',
            'unit_id' => 'required|exists:units,id',
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'tahun_masuk' => 'nullable|string|max:4',
            'status' => 'nullable|in:aktif,non-aktif,lulus,keluar',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $siswa = Siswa::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Siswa created successfully',
            'data' => $siswa->load(['kelas', 'unit', 'jurusan'])
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
        $siswa = Siswa::with(['kelas', 'unit', 'jurusan', 'user'])->find($id);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $siswa
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
        $siswa = Siswa::find($id);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'nis' => 'sometimes|required|string|unique:siswas,nis,' . $id,
            'nisn' => 'nullable|string|unique:siswas,nisn,' . $id,
            'jenis_kelamin' => 'sometimes|required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'kelas_id' => 'sometimes|required|exists:kelas,id',
            'unit_id' => 'sometimes|required|exists:units,id',
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'tahun_masuk' => 'nullable|string|max:4',
            'status' => 'nullable|in:aktif,non-aktif,lulus,keluar',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $siswa->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Siswa updated successfully',
            'data' => $siswa->load(['kelas', 'unit', 'jurusan'])
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
        $siswa = Siswa::find($id);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa not found'
            ], 404);
        }

        $siswa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Siswa deleted successfully'
        ]);
    }

    /**
     * Get siswa by kelas.
     *
     * @param int $kelasId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByKelas($kelasId)
    {
        $siswa = Siswa::with(['kelas', 'unit', 'jurusan'])
            ->where('kelas_id', $kelasId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $siswa
        ]);
    }
}