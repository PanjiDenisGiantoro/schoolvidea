<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\TagihanSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagihanController extends Controller
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
        $kelasId = $request->get('kelas_id');

        $query = Tagihan::with(['kategori', 'unit', 'kelas', 'tahunAjaran']);

        if ($search) {
            $query->where('nama_tagihan', 'like', "%{$search}%");
        }

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        $tagihan = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tagihan
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
            'nama_tagihan' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori_tagihans,id',
            'unit_id' => 'required|exists:units,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'jumlah' => 'required|numeric|min:0',
            'jenis' => 'required|in:bulanan,bebas',
            'tanggal_jatuh_tempo' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $tagihan = Tagihan::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tagihan created successfully',
            'data' => $tagihan->load(['kategori', 'unit', 'kelas', 'tahunAjaran'])
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
        $tagihan = Tagihan::with(['kategori', 'unit', 'kelas', 'tahunAjaran', 'tagihanSiswa'])->find($id);

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tagihan
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
        $tagihan = Tagihan::find($id);

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_tagihan' => 'sometimes|required|string|max:255',
            'kategori_id' => 'sometimes|required|exists:kategori_tagihans,id',
            'unit_id' => 'sometimes|required|exists:units,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'tahun_ajaran_id' => 'sometimes|required|exists:tahun_ajarans,id',
            'jumlah' => 'sometimes|required|numeric|min:0',
            'jenis' => 'sometimes|required|in:bulanan,bebas',
            'tanggal_jatuh_tempo' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $tagihan->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tagihan updated successfully',
            'data' => $tagihan->load(['kategori', 'unit', 'kelas', 'tahunAjaran'])
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
        $tagihan = Tagihan::find($id);

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan not found'
            ], 404);
        }

        $tagihan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tagihan deleted successfully'
        ]);
    }

    /**
     * Get tagihan by siswa.
     *
     * @param int $siswaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBySiswa($siswaId)
    {
        $tagihan = TagihanSiswa::with(['tagihan', 'siswa', 'pembayaran'])
            ->where('siswa_id', $siswaId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tagihan
        ]);
    }
}