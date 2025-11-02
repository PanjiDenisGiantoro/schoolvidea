<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tagihansiswa;
use Illuminate\Http\Request;

class TagihanSiswaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/tagihan-siswa",
     *     tags={"Tagihan Siswa"},
     *     summary="Get list of student bills",
     *     description="Returns paginated list of student bills with filters",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="siswa_id",
     *         in="query",
     *         description="Filter by student ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="tagihan_id",
     *         in="query",
     *         description="Filter by tagihan ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status (0=Belum Bayar, 1=Lunas, 2=Cicilan)",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0,1,2})
     *     ),
     *     @OA\Parameter(
     *         name="kelas_id",
     *         in="query",
     *         description="Filter by class ID",
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
        $siswaId = $request->get('siswa_id');
        $tagihanId = $request->get('tagihan_id');
        $status = $request->get('status');
        $kelasId = $request->get('kelas_id');

        $query = Tagihansiswa::with([
            'siswa.kelas',
            'siswa.unit',
            'tagihan.kategori',
            'pembayaranTagihan'
        ]);

        if ($siswaId) {
            $query->where('siswa_id', $siswaId);
        }

        if ($tagihanId) {
            $query->where('tagihan_id', $tagihanId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($kelasId) {
            $query->whereHas('siswa', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        $query->orderBy('created_at', 'desc');

        $tagihanSiswa = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tagihanSiswa
        ]);
    }

    /**
     * @OA\Get(
     *     path="/tagihan-siswa/{id}",
     *     tags={"Tagihan Siswa"},
     *     summary="Get student bill detail",
     *     description="Get detailed information about a specific student bill",
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
        $tagihanSiswa = Tagihansiswa::with([
            'siswa.kelas',
            'siswa.unit',
            'tagihan.kategori',
            'pembayaranTagihan.user',
            'potonganSiswa'
        ])->find($id);

        if (!$tagihanSiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan siswa not found'
            ], 404);
        }

        // Calculate payment summary
        $totalBayar = $tagihanSiswa->pembayaranTagihan->sum('jumlah_bayar');

        $summary = [
            'nominal_tagihan' => $tagihanSiswa->nominal,
            'total_bayar' => $totalBayar,
            'sisa_nominal' => $tagihanSiswa->sisa_nominal,
            'status' => $tagihanSiswa->status == 1 ? 'Lunas' : ($tagihanSiswa->status == 2 ? 'Cicilan' : 'Belum Bayar'),
            'jumlah_pembayaran' => $tagihanSiswa->pembayaranTagihan->count()
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'tagihan_siswa' => $tagihanSiswa,
                'summary' => $summary
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/tagihan-siswa/siswa/{siswaId}",
     *     tags={"Tagihan Siswa"},
     *     summary="Get all bills for a student",
     *     description="Get all bills for a specific student",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="siswaId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0,1,2})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function getBySiswa(Request $request, $siswaId)
    {
        $status = $request->get('status');

        $query = Tagihansiswa::with([
            'tagihan.kategori',
            'pembayaranTagihan'
        ])->where('siswa_id', $siswaId);

        if ($status !== null) {
            $query->where('status', $status);
        }

        $tagihanSiswa = $query->orderBy('created_at', 'desc')->get();

        // Calculate totals
        $totalTagihan = $tagihanSiswa->sum('nominal');
        $totalBayar = $tagihanSiswa->sum(function($ts) {
            return $ts->nominal - $ts->sisa_nominal;
        });
        $totalSisa = $tagihanSiswa->sum('sisa_nominal');

        return response()->json([
            'success' => true,
            'data' => [
                'tagihan_siswa' => $tagihanSiswa,
                'summary' => [
                    'total_tagihan' => $totalTagihan,
                    'total_bayar' => $totalBayar,
                    'total_sisa' => $totalSisa,
                    'jumlah_tagihan' => $tagihanSiswa->count(),
                    'lunas' => $tagihanSiswa->where('status', 1)->count(),
                    'cicilan' => $tagihanSiswa->where('status', 2)->count(),
                    'belum_bayar' => $tagihanSiswa->where('status', 0)->count()
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/tagihan-siswa/kelas/{kelasId}",
     *     tags={"Tagihan Siswa"},
     *     summary="Get bills by class",
     *     description="Get all bills for students in a specific class",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="kelasId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="tagihan_id",
     *         in="query",
     *         description="Filter by specific tagihan",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0,1,2})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function getByKelas(Request $request, $kelasId)
    {
        $tagihanId = $request->get('tagihan_id');
        $status = $request->get('status');

        $query = Tagihansiswa::with([
            'siswa',
            'tagihan.kategori',
            'pembayaranTagihan'
        ])->whereHas('siswa', function($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        });

        if ($tagihanId) {
            $query->where('tagihan_id', $tagihanId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        $tagihanSiswa = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary
        $totalTagihan = $tagihanSiswa->sum('nominal');
        $totalBayar = $tagihanSiswa->sum(function($ts) {
            return $ts->nominal - $ts->sisa_nominal;
        });
        $totalSisa = $tagihanSiswa->sum('sisa_nominal');

        return response()->json([
            'success' => true,
            'data' => [
                'tagihan_siswa' => $tagihanSiswa,
                'summary' => [
                    'total_tagihan' => $totalTagihan,
                    'total_bayar' => $totalBayar,
                    'total_sisa' => $totalSisa,
                    'jumlah_siswa' => $tagihanSiswa->unique('siswa_id')->count(),
                    'jumlah_tagihan' => $tagihanSiswa->count(),
                    'lunas' => $tagihanSiswa->where('status', 1)->count(),
                    'cicilan' => $tagihanSiswa->where('status', 2)->count(),
                    'belum_bayar' => $tagihanSiswa->where('status', 0)->count()
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/tagihan-siswa/unpaid/{siswaId}",
     *     tags={"Tagihan Siswa"},
     *     summary="Get unpaid bills for student",
     *     description="Get all unpaid bills for a specific student",
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
    public function getUnpaid($siswaId)
    {
        $tagihanSiswa = Tagihansiswa::with([
            'tagihan.kategori',
            'pembayaranTagihan'
        ])
        ->where('siswa_id', $siswaId)
        ->whereIn('status', [0, 2]) // Belum Bayar atau Cicilan
        ->orderBy('created_at', 'desc')
        ->get();

        $totalSisa = $tagihanSiswa->sum('sisa_nominal');

        return response()->json([
            'success' => true,
            'data' => [
                'tagihan_siswa' => $tagihanSiswa,
                'summary' => [
                    'total_sisa' => $totalSisa,
                    'jumlah_tagihan' => $tagihanSiswa->count()
                ]
            ]
        ]);
    }
}
