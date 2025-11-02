<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Officer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfficerController extends Controller
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

        $query = Officer::with(['unit', 'user', 'position']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        $officers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $officers
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
            'nip' => 'required|string|unique:officers,nip',
            'email' => 'nullable|email|unique:officers,email',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'unit_id' => 'required|exists:units,id',
            'position_id' => 'nullable|exists:positions,id',
            'status' => 'nullable|in:aktif,non-aktif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $officer = Officer::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Officer created successfully',
            'data' => $officer->load(['unit', 'user', 'position'])
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
        $officer = Officer::with(['unit', 'user', 'position'])->find($id);

        if (!$officer) {
            return response()->json([
                'success' => false,
                'message' => 'Officer not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $officer
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
        $officer = Officer::find($id);

        if (!$officer) {
            return response()->json([
                'success' => false,
                'message' => 'Officer not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'nip' => 'sometimes|required|string|unique:officers,nip,' . $id,
            'email' => 'nullable|email|unique:officers,email,' . $id,
            'jenis_kelamin' => 'sometimes|required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'unit_id' => 'sometimes|required|exists:units,id',
            'position_id' => 'nullable|exists:positions,id',
            'status' => 'nullable|in:aktif,non-aktif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $officer->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Officer updated successfully',
            'data' => $officer->load(['unit', 'user', 'position'])
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
        $officer = Officer::find($id);

        if (!$officer) {
            return response()->json([
                'success' => false,
                'message' => 'Officer not found'
            ], 404);
        }

        $officer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Officer deleted successfully'
        ]);
    }
}
