<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Jurnals;
use App\Models\Keuangan_transaksi;
use App\Models\Pembayarantagihan;
use App\Models\setting_akun;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Tagihansiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PembayaranController extends Controller
{
    /**
     * @OA\Get(
     *     path="/pembayaran",
     *     tags={"Pembayaran"},
     *     summary="Get list of payments",
     *     description="Returns paginated list of payments with filters",
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
     *         name="kelas_id",
     *         in="query",
     *         description="Filter by class ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
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
        $kelasId = $request->get('kelas_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Pembayarantagihan::with([
            'tagihanSiswa.siswa',
            'tagihanSiswa.tagihan',
            'user',
            'keuanganTransaksi'
        ]);

        if ($siswaId) {
            $query->whereHas('tagihanSiswa', function($q) use ($siswaId) {
                $q->where('siswa_id', $siswaId);
            });
        }

        if ($kelasId) {
            $query->whereHas('tagihanSiswa.siswa', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        if ($startDate) {
            $query->whereDate('tanggal_bayar', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal_bayar', '<=', $endDate);
        }

        $query->orderBy('tanggal_bayar', 'desc');

        $pembayaran = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $pembayaran
        ]);
    }

    /**
     * @OA\Post(
     *     path="/pembayaran",
     *     tags={"Pembayaran"},
     *     summary="Process payment",
     *     description="Process student payment for a bill",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tagihan_siswa_id","jumlah_bayar"},
     *             @OA\Property(property="tagihan_siswa_id", type="integer", example=1),
     *             @OA\Property(property="jumlah_bayar", type="integer", example=500000),
     *             @OA\Property(property="metode", type="string", example="CASH", description="CASH, TRANSFER, QRIS, etc"),
     *             @OA\Property(property="keterangan", type="string", example="Pembayaran SPP Januari 2024")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment successful"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or bill already paid"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tagihan_siswa_id' => 'required|exists:tagihan_siswa,id',
            'jumlah_bayar' => 'required|numeric|min:1',
            'metode' => 'nullable|string|in:CASH,TRANSFER,QRIS,VIRTUAL_ACCOUNT,MOBILE_BANKING',
            'keterangan' => 'nullable|string'
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
                    'message' => 'Tagihan ini sudah lunas'
                ], 400);
            }

            $siswa = $tagihanSiswa->siswa;
            $tagihan = $tagihanSiswa->tagihan;

            $jumlahBayar = (int) $request->jumlah_bayar;
            $sisaNominal = (int) $tagihanSiswa->sisa_nominal;

            if ($jumlahBayar > $sisaNominal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah bayar tidak boleh lebih besar dari sisa tagihan',
                    'data' => [
                        'jumlah_bayar' => $jumlahBayar,
                        'sisa_nominal' => $sisaNominal
                    ]
                ], 400);
            }

            // Calculate remaining balance
            $sisaSetelahBayar = $sisaNominal - $jumlahBayar;

            // Determine new status
            if ($sisaSetelahBayar == 0) {
                $statusTagihan = 1; // Lunas
                $sisaSetelahBayar = 0;
                $keterangan = $request->keterangan ?? "Pembayaran lunas {$tagihan->nama_tagihan} sebesar Rp " . number_format($tagihanSiswa->nominal, 0, ',', '.');
            } else {
                $statusTagihan = 2; // Cicilan
                $keterangan = $request->keterangan ?? "Cicilan {$tagihan->nama_tagihan} bayar Rp " . number_format($jumlahBayar, 0, ',', '.') . " dari Rp " . number_format($tagihanSiswa->nominal, 0, ',', '.');
            }

            // Save payment
            $pembayaran = Pembayarantagihan::create([
                'code_pembayaran' => 'PAY' . date('YmdHis') . rand(1000, 9999),
                'tagihan_siswa_id' => $tagihanSiswa->id,
                'jumlah_bayar' => $jumlahBayar,
                'tanggal_bayar' => now(),
                'metode_bayar' => $request->metode ?? 'CASH',
                'keterangan' => $keterangan,
                'create_by' => Auth::id(),
            ]);

            // Update status and remaining balance
            $tagihanSiswa->update([
                'status' => $statusTagihan,
                'sisa_nominal' => $sisaSetelahBayar,
            ]);

            // Check if all student bills for this tagihan are paid
            $hasUnpaid = Tagihansiswa::where('tagihan_id', $tagihanSiswa->tagihan_id)
                ->where('status', 0)
                ->exists();

            if (!$hasUnpaid) {
                Tagihan::where('id', $tagihanSiswa->tagihan_id)
                    ->update(['status_tagihan' => 1]);
            }

            // Record financial transaction
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => $pembayaran->code_pembayaran,
                'penerima_id' => $siswa->id,
                'penerima_tipe' => Siswa::class,
                'jenis_transaksi' => 'tagihan',
                'jumlah' => $jumlahBayar,
                'metode' => $request->metode ?? 'CASH',
                'referensi_tagihan_id' => $pembayaran->id,
                'tanggal_transaksi' => now(),
                'keterangan' => $keterangan,
                'created_by' => Auth::id(),
            ]);

            // Journal entry - debit
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id' => setting_akun::where('kategori', 'tagihan-keluar')
                    ->where('debit', 1)
                    ->first()?->akun_id,
                'debit' => $jumlahBayar,
                'kredit' => 0,
                'keterangan' => $keterangan,
            ]);

            // Journal entry - credit
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id' => setting_akun::where('kategori', 'tagihan-keluar')
                    ->where('kredit', 1)
                    ->first()?->akun_id,
                'debit' => 0,
                'kredit' => $jumlahBayar,
                'keterangan' => $keterangan,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $statusTagihan == 1 ? 'Pembayaran lunas berhasil' : 'Pembayaran cicilan berhasil',
                'data' => [
                    'pembayaran' => $pembayaran->load(['tagihanSiswa.siswa', 'tagihanSiswa.tagihan']),
                    'tagihan_siswa' => $tagihanSiswa->fresh(),
                    'transaksi' => $transaksi
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
     *     path="/pembayaran/{id}",
     *     tags={"Pembayaran"},
     *     summary="Get payment detail",
     *     description="Get detailed information about a specific payment",
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
     *         description="Payment not found"
     *     )
     * )
     */
    public function show($id)
    {
        $pembayaran = Pembayarantagihan::with([
            'tagihanSiswa.siswa.kelas',
            'tagihanSiswa.siswa.unit',
            'tagihanSiswa.tagihan.kategori',
            'user',
            'keuanganTransaksi'
        ])->find($id);

        if (!$pembayaran) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pembayaran
        ]);
    }

    /**
     * @OA\Get(
     *     path="/pembayaran/siswa/{siswaId}",
     *     tags={"Pembayaran"},
     *     summary="Get payment history by student",
     *     description="Get all payment history for a specific student",
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
        $siswa = Siswa::find($siswaId);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa not found'
            ], 404);
        }

        $pembayaran = Pembayarantagihan::with([
            'tagihanSiswa.tagihan.kategori',
            'keuanganTransaksi'
        ])
        ->whereHas('tagihanSiswa', function($q) use ($siswaId) {
            $q->where('siswa_id', $siswaId);
        })
        ->orderBy('tanggal_bayar', 'desc')
        ->get();

        // Calculate summary
        $totalBayar = $pembayaran->sum('jumlah_bayar');
        $jumlahTransaksi = $pembayaran->count();

        return response()->json([
            'success' => true,
            'data' => [
                'siswa' => $siswa->load(['kelas', 'unit']),
                'pembayaran' => $pembayaran,
                'summary' => [
                    'total_bayar' => $totalBayar,
                    'jumlah_transaksi' => $jumlahTransaksi
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/pembayaran/kelas/{kelasId}",
     *     tags={"Pembayaran"},
     *     summary="Get payments by class",
     *     description="Get all payments for students in a specific class",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="kelasId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function getByKelas(Request $request, $kelasId)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Pembayarantagihan::with([
            'tagihanSiswa.siswa',
            'tagihanSiswa.tagihan.kategori'
        ])
        ->whereHas('tagihanSiswa.siswa', function($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        });

        if ($startDate) {
            $query->whereDate('tanggal_bayar', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal_bayar', '<=', $endDate);
        }

        $pembayaran = $query->orderBy('tanggal_bayar', 'desc')->get();

        // Calculate summary
        $totalBayar = $pembayaran->sum('jumlah_bayar');
        $jumlahTransaksi = $pembayaran->count();

        return response()->json([
            'success' => true,
            'data' => [
                'pembayaran' => $pembayaran,
                'summary' => [
                    'total_bayar' => $totalBayar,
                    'jumlah_transaksi' => $jumlahTransaksi,
                    'periode' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/pembayaran/tagihan-siswa/{tagihanSiswaId}",
     *     tags={"Pembayaran"},
     *     summary="Get payments for specific student bill",
     *     description="Get all payments made for a specific tagihan siswa",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="tagihanSiswaId",
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
    public function getByTagihanSiswa($tagihanSiswaId)
    {
        $tagihanSiswa = Tagihansiswa::with(['siswa', 'tagihan'])->find($tagihanSiswaId);

        if (!$tagihanSiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan siswa not found'
            ], 404);
        }

        $pembayaran = Pembayarantagihan::where('tagihan_siswa_id', $tagihanSiswaId)
            ->with(['user', 'keuanganTransaksi'])
            ->orderBy('tanggal_bayar', 'desc')
            ->get();

        $totalBayar = $pembayaran->sum('jumlah_bayar');

        return response()->json([
            'success' => true,
            'data' => [
                'tagihan_siswa' => $tagihanSiswa,
                'pembayaran' => $pembayaran,
                'summary' => [
                    'nominal_tagihan' => $tagihanSiswa->nominal,
                    'total_bayar' => $totalBayar,
                    'sisa_nominal' => $tagihanSiswa->sisa_nominal,
                    'status' => $tagihanSiswa->status == 1 ? 'Lunas' : ($tagihanSiswa->status == 2 ? 'Cicilan' : 'Belum Bayar')
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/pembayaran/receipt/{id}",
     *     tags={"Pembayaran"},
     *     summary="Get payment receipt",
     *     description="Get payment receipt data for printing",
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
     *     )
     * )
     */
    public function getReceipt($id)
    {
        $pembayaran = Pembayarantagihan::with([
            'tagihanSiswa.siswa.kelas',
            'tagihanSiswa.siswa.unit',
            'tagihanSiswa.tagihan.kategori',
            'user'
        ])->find($id);

        if (!$pembayaran) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran not found'
            ], 404);
        }

        $receipt = [
            'code_pembayaran' => $pembayaran->code_pembayaran,
            'tanggal_bayar' => $pembayaran->tanggal_bayar,
            'siswa' => [
                'nama' => $pembayaran->tagihanSiswa->siswa->nama,
                'nis' => $pembayaran->tagihanSiswa->siswa->nis,
                'kelas' => $pembayaran->tagihanSiswa->siswa->kelas->nama_kelas ?? '-',
                'unit' => $pembayaran->tagihanSiswa->siswa->unit->nama_unit ?? '-',
            ],
            'tagihan' => [
                'nama' => $pembayaran->tagihanSiswa->tagihan->nama_tagihan,
                'kategori' => $pembayaran->tagihanSiswa->tagihan->kategori->nama ?? '-',
                'nominal_tagihan' => $pembayaran->tagihanSiswa->nominal,
                'jumlah_bayar' => $pembayaran->jumlah_bayar,
                'sisa' => $pembayaran->tagihanSiswa->sisa_nominal,
                'status' => $pembayaran->tagihanSiswa->status == 1 ? 'LUNAS' : 'CICILAN'
            ],
            'metode_bayar' => $pembayaran->metode_bayar,
            'keterangan' => $pembayaran->keterangan,
            'petugas' => $pembayaran->user->name ?? '-'
        ];

        return response()->json([
            'success' => true,
            'data' => $receipt
        ]);
    }
}
