<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tagihansiswa;
use App\Models\Tagihansiswa_mutasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TagihanSiswaMutasiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/tagihan-siswa-mutasi",
     *     tags={"Tagihan Siswa Mutasi"},
     *     summary="Get list of billing mutations",
     *     description="Returns paginated list of student billing mutations with filters",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="tagihan_siswa_id",
     *         in="query",
     *         description="Filter by student bill ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="jenis_mutasi",
     *         in="query",
     *         description="Filter by mutation type (koreksi, diskon, denda, pembatalan, lainnya)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status_mutasi",
     *         in="query",
     *         description="Filter by mutation status (pending, approved, rejected)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $tagihanSiswaId = $request->get('tagihan_siswa_id');
        $jenisMutasi = $request->get('jenis_mutasi');
        $statusMutasi = $request->get('status_mutasi');

        $query = Tagihansiswa_mutasi::with([
            'tagihanSiswa.siswa',
            'tagihanSiswa.tagihan',
            'createdBy',
            'approvedBy'
        ]);

        if ($tagihanSiswaId) {
            $query->where('tagihan_siswa_id', $tagihanSiswaId);
        }

        if ($jenisMutasi) {
            $query->where('jenis_mutasi', $jenisMutasi);
        }

        if ($statusMutasi) {
            $query->where('status_mutasi', $statusMutasi);
        }

        $query->orderBy('created_at', 'desc');

        $mutasi = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $mutasi
        ]);
    }

    /**
     * @OA\Post(
     *     path="/tagihan-siswa-mutasi",
     *     tags={"Tagihan Siswa Mutasi"},
     *     summary="Create billing mutation",
     *     description="Create a mutation/adjustment for student billing",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tagihan_siswa_id","jenis_mutasi","nominal_perubahan","keterangan"},
     *             @OA\Property(property="tagihan_siswa_id", type="integer", example=1),
     *             @OA\Property(property="jenis_mutasi", type="string", example="diskon", description="koreksi, diskon, denda, pembatalan, lainnya"),
     *             @OA\Property(property="nominal_perubahan", type="number", example=-50000, description="Negative for reduction, positive for addition"),
     *             @OA\Property(property="keterangan", type="string", example="Diskon prestasi akademik"),
     *             @OA\Property(property="alasan", type="string", example="Siswa mendapat juara 1 olimpiade"),
     *             @OA\Property(property="auto_approve", type="boolean", example=true, description="Auto approve without pending status")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Mutation created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tagihan_siswa_id' => 'required|exists:tagihan_siswa,id',
            'jenis_mutasi' => 'required|in:koreksi,diskon,denda,pembatalan,lainnya',
            'nominal_perubahan' => 'required|numeric|not_in:0',
            'keterangan' => 'required|string',
            'alasan' => 'nullable|string',
            'auto_approve' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $tagihanSiswa = Tagihansiswa::with(['siswa', 'tagihan'])->findOrFail($request->tagihan_siswa_id);

            if ($tagihanSiswa->status == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat melakukan mutasi pada tagihan yang sudah lunas. Batalkan pembayaran terlebih dahulu.'
                ], 400);
            }

            $nominalSebelum = $tagihanSiswa->nominal;
            $nominalPerubahan = (float) $request->nominal_perubahan;
            $nominalSesudah = $nominalSebelum + $nominalPerubahan;

            // Validate that nominal_sesudah is not negative
            if ($nominalSesudah < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal tagihan setelah mutasi tidak boleh negatif',
                    'data' => [
                        'nominal_sebelum' => $nominalSebelum,
                        'nominal_perubahan' => $nominalPerubahan,
                        'nominal_sesudah' => $nominalSesudah
                    ]
                ], 400);
            }

            // Generate mutation code
            $codeMutasi = 'MTS' . date('YmdHis') . rand(1000, 9999);

            // Determine status
            $autoApprove = $request->get('auto_approve', true);
            $statusMutasi = $autoApprove ? 'approved' : 'pending';

            // Create mutation record
            $mutasi = Tagihansiswa_mutasi::create([
                'code_mutasi' => $codeMutasi,
                'tagihan_siswa_id' => $tagihanSiswa->id,
                'jenis_mutasi' => $request->jenis_mutasi,
                'nominal_sebelum' => $nominalSebelum,
                'nominal_perubahan' => $nominalPerubahan,
                'nominal_sesudah' => $nominalSesudah,
                'keterangan' => $request->keterangan,
                'alasan' => $request->alasan,
                'created_by' => Auth::id(),
                'approved_by' => $autoApprove ? Auth::id() : null,
                'approved_at' => $autoApprove ? now() : null,
                'status_mutasi' => $statusMutasi,
            ]);

            // If auto approved, update the billing
            if ($autoApprove) {
                $sisaNominalBaru = $tagihanSiswa->sisa_nominal + $nominalPerubahan;

                // Make sure sisa_nominal is not negative
                if ($sisaNominalBaru < 0) {
                    $sisaNominalBaru = 0;
                }

                // Determine new status
                $statusBaru = $tagihanSiswa->status;
                if ($sisaNominalBaru == 0) {
                    $statusBaru = 1; // Lunas
                } elseif ($sisaNominalBaru > 0 && $sisaNominalBaru < $nominalSesudah) {
                    $statusBaru = 2; // Cicilan
                } else {
                    $statusBaru = 0; // Belum Bayar
                }

                $tagihanSiswa->update([
                    'nominal' => $nominalSesudah,
                    'sisa_nominal' => $sisaNominalBaru,
                    'status' => $statusBaru,
                    'catatan' => ($tagihanSiswa->catatan ?? '') . "\n[Mutasi {$codeMutasi}] {$request->keterangan}"
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $autoApprove ? 'Mutasi tagihan berhasil diterapkan' : 'Mutasi tagihan berhasil dibuat dan menunggu approval',
                'data' => [
                    'mutasi' => $mutasi->load(['tagihanSiswa.siswa', 'tagihanSiswa.tagihan', 'createdBy']),
                    'tagihan_siswa' => $tagihanSiswa->fresh()
                ]
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/tagihan-siswa-mutasi/{id}",
     *     tags={"Tagihan Siswa Mutasi"},
     *     summary="Get mutation detail",
     *     description="Get detailed information about a specific mutation",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     )
     * )
     */
    public function show($id)
    {
        $mutasi = Tagihansiswa_mutasi::with([
            'tagihanSiswa.siswa.kelas',
            'tagihanSiswa.siswa.unit',
            'tagihanSiswa.tagihan.kategori',
            'createdBy',
            'approvedBy'
        ])->find($id);

        if (!$mutasi) {
            return response()->json([
                'success' => false,
                'message' => 'Mutasi not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $mutasi
        ]);
    }

    /**
     * @OA\Post(
     *     path="/tagihan-siswa-mutasi/{id}/approve",
     *     tags={"Tagihan Siswa Mutasi"},
     *     summary="Approve mutation",
     *     description="Approve a pending mutation",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mutation approved successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Mutation already processed"
     *     )
     * )
     */
    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $mutasi = Tagihansiswa_mutasi::with(['tagihanSiswa'])->findOrFail($id);

            if ($mutasi->status_mutasi != 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Mutasi ini sudah diproses sebelumnya'
                ], 400);
            }

            $tagihanSiswa = $mutasi->tagihanSiswa;
            $sisaNominalBaru = $tagihanSiswa->sisa_nominal + $mutasi->nominal_perubahan;

            if ($sisaNominalBaru < 0) {
                $sisaNominalBaru = 0;
            }

            // Determine new status
            $statusBaru = $tagihanSiswa->status;
            if ($sisaNominalBaru == 0) {
                $statusBaru = 1; // Lunas
            } elseif ($sisaNominalBaru > 0 && $sisaNominalBaru < $mutasi->nominal_sesudah) {
                $statusBaru = 2; // Cicilan
            } else {
                $statusBaru = 0; // Belum Bayar
            }

            // Update mutation
            $mutasi->update([
                'status_mutasi' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            // Update billing
            $tagihanSiswa->update([
                'nominal' => $mutasi->nominal_sesudah,
                'sisa_nominal' => $sisaNominalBaru,
                'status' => $statusBaru,
                'catatan' => ($tagihanSiswa->catatan ?? '') . "\n[Mutasi {$mutasi->code_mutasi} Approved] {$mutasi->keterangan}"
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mutasi berhasil disetujui',
                'data' => [
                    'mutasi' => $mutasi->fresh()->load(['tagihanSiswa', 'approvedBy']),
                    'tagihan_siswa' => $tagihanSiswa->fresh()
                ]
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/tagihan-siswa-mutasi/{id}/reject",
     *     tags={"Tagihan Siswa Mutasi"},
     *     summary="Reject mutation",
     *     description="Reject a pending mutation",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="alasan_penolakan", type="string", example="Data tidak sesuai")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mutation rejected successfully"
     *     )
     * )
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'alasan_penolakan' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $mutasi = Tagihansiswa_mutasi::findOrFail($id);

            if ($mutasi->status_mutasi != 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Mutasi ini sudah diproses sebelumnya'
                ], 400);
            }

            $mutasi->update([
                'status_mutasi' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'alasan' => ($mutasi->alasan ?? '') . "\n[REJECTED] " . $request->alasan_penolakan
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mutasi ditolak',
                'data' => $mutasi->fresh()->load(['tagihanSiswa', 'approvedBy'])
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/tagihan-siswa-mutasi/siswa/{siswaId}",
     *     tags={"Tagihan Siswa Mutasi"},
     *     summary="Get mutations by student",
     *     description="Get all billing mutations for a specific student",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="siswaId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function getBySiswa($siswaId)
    {
        $mutasi = Tagihansiswa_mutasi::with([
            'tagihanSiswa.tagihan',
            'createdBy',
            'approvedBy'
        ])
        ->whereHas('tagihanSiswa', function($q) use ($siswaId) {
            $q->where('siswa_id', $siswaId);
        })
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $mutasi
        ]);
    }
}
