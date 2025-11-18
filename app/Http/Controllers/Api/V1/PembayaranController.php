<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
            'tagihanSiswa.tagihan.rekening',
            'tagihanSiswa.tagihanItem.kategori',
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

        // Format data with additional information
        $data = $pembayaran->map(function ($p) {
            // Calculate bulan and tahun info
            $bulanArray = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $bulanKe = $p->tagihanSiswa ? $p->tagihanSiswa->bulan_ke : null;
            $tahunTagihan = $p->tagihanSiswa && $p->tagihanSiswa->tagihan ? $p->tagihanSiswa->tagihan->tahun_mulai : null;
            $bulanText = $bulanKe && $tahunTagihan ? ($bulanArray[$bulanKe] ?? $bulanKe) . ' ' . $tahunTagihan : 'N/A';

            // Get kategori info
            $kategoriCode = null;
            $namaKategori = 'N/A';
            if ($p->tagihanSiswa && $p->tagihanSiswa->tagihanItem && $p->tagihanSiswa->tagihanItem->kategori) {
                $kategoriCode = $p->tagihanSiswa->tagihanItem->kategori->kode_kategori ?? null;
                $namaKategori = $p->tagihanSiswa->tagihanItem->kategori->nama_kategori;
            }

            // Get potongan total
            $totalPotongan = 0;
            $potonganList = [];
            if ($p->tagihanSiswa && $p->tagihanSiswa->potonganSiswa) {
                $potonganList = $p->tagihanSiswa->potonganSiswa->map(function ($potongan) {
                    return [
                        'id' => $potongan->id,
                        'nominal' => (float)$potongan->nominal,
                        'keterangan' => $potongan->keterangan,
                    ];
                })->toArray();

                foreach ($potonganList as $potongan) {
                    $totalPotongan += $potongan['nominal'];
                }
            }

            // Get kelas info
            $kelasNama = 'N/A';
            if ($p->tagihanSiswa && $p->tagihanSiswa->siswa && $p->tagihanSiswa->siswa->kelas) {
                $kelasNama = $p->tagihanSiswa->siswa->kelas->nama_kelas;
            }

            // Get nominal tagihan
            $nominalTagihan = $p->tagihanSiswa && $p->tagihanSiswa->tagihanItem ? (float)$p->tagihanSiswa->tagihanItem->nominal : 0;

            // Calculate tunggakan
            $sisaNominal = $p->tagihanSiswa ? (float)$p->tagihanSiswa->sisa_nominal : 0;

            return [
                'id' => $p->id,
                'code_pembayaran' => $p->code_pembayaran,
                'tagihan_siswa_id' => $p->tagihan_siswa_id,
                'siswa_id' => $p->tagihanSiswa && $p->tagihanSiswa->siswa ? $p->tagihanSiswa->siswa->id : null,
                'siswa_nama' => $p->tagihanSiswa && $p->tagihanSiswa->siswa ? $p->tagihanSiswa->siswa->nama_lengkap : 'N/A',
                'kategori' => [
                    'kode' => $kategoriCode,
                    'nama' => $namaKategori,
                ],
                'periode' => $bulanText,
                'tahun' => $tahunTagihan,
                'tagihan_kelas' => $kelasNama,
                'rincian_tagihan' => $nominalTagihan,
                'jumlah_potongan' => $totalPotongan,
                'jumlah_tagihan' => $nominalTagihan - $totalPotongan,
                'jumlah_dibayar' => (float)$p->jumlah_bayar,
                'jumlah_tunggakan' => $sisaNominal,
                'nominal_pembayaran' => (float)$p->jumlah_bayar,
                'tanggal_bayar' => $p->tanggal_bayar->format('Y-m-d'),
                'metode_bayar' => $p->metode_bayar,
                'status_approval' => $p->status_approval,
                'file_bukti' => $p->file_bukti ? url($p->file_bukti) : null,
                'keterangan' => $p->keterangan,
                'created_by' => $p->user ? $p->user->name : null,
                'approved_by' => isset($p->approvedBy) && $p->approvedBy ? $p->approvedBy->name : null,
                'approved_at' => isset($p->approved_at) && $p->approved_at ? $p->approved_at->format('Y-m-d H:i:s') : null,
                'created_at' => $p->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $pembayaran->total(),
                'per_page' => $pembayaran->perPage(),
                'current_page' => $pembayaran->currentPage(),
                'last_page' => $pembayaran->lastPage(),
            ]
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
            'metode' => 'nullable|string|in:CASH,TRANSFER,QRIS,VIRTUAL_ACCOUNT,MOBILE_BANKING,NONTUNAI,TUNAI',
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
            Log::info('========== PEMBAYARAN STORE STARTED ==========');
            Log::info('Tagihan Siswa ID: ' . $request->tagihan_siswa_id);
            Log::info('Jumlah Bayar: ' . $request->jumlah_bayar);
            Log::info('Metode: ' . ($request->metode ?? 'CASH'));

            $tagihanSiswa = Tagihansiswa::with(['siswa', 'tagihan'])->findOrFail($request->tagihan_siswa_id);

            if ($tagihanSiswa->status == 1) {
                Log::warning('⚠️ Tagihan sudah lunas untuk tagihan siswa ID: ' . $request->tagihan_siswa_id);
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tagihan ini sudah lunas'
                ], 400);
            }

            $siswa = $tagihanSiswa->siswa;
            $tagihan = $tagihanSiswa->tagihan;
            Log::info('✓ Siswa ditemukan: ' . $siswa->user->name . ' | Tagihan: ' . $tagihan->nama_tagihan);

            $jumlahBayar = (int) $request->jumlah_bayar;
            $sisaNominal = (int) $tagihanSiswa->sisa_nominal;

            Log::info('Sisa Nominal Tagihan: ' . $sisaNominal . ' | Jumlah Bayar: ' . $jumlahBayar);

            if ($jumlahBayar > $sisaNominal) {
                Log::warning('⚠️ Jumlah bayar lebih besar dari sisa nominal');
                Log::warning('Jumlah Bayar: ' . $jumlahBayar . ' | Sisa: ' . $sisaNominal);
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah bayar tidak boleh lebih besar dari sisa tagihan',
                    'data' => [
                        'jumlah_bayar' => $jumlahBayar,
                        'sisa_nominal' => $sisaNominal
                    ]
                ], 400);
            }

            // Generate payment code
            $codePembayaran = 'PAY' . date('YmdHis') . rand(1000, 9999);
            Log::info('Payment Code Generated: ' . $codePembayaran);

            // Prepare default description
            $keterangan = $request->keterangan ?? "Pembayaran {$tagihan->nama_tagihan} sebesar Rp " . number_format($jumlahBayar, 0, ',', '.');
            Log::info('Keterangan: ' . $keterangan);

            // Save payment with PENDING status (no calculation yet)
            Log::info('Creating payment record with PENDING status...');
            $pembayaran = Pembayarantagihan::create([
                'code_pembayaran' => $codePembayaran,
                'tagihan_siswa_id' => $tagihanSiswa->id,
                'jumlah_bayar' => $jumlahBayar,
                'tanggal_bayar' => now(),
                'metode_bayar' => $request->metode ?? 'CASH',
                'keterangan' => $keterangan,
                'create_by' => Auth::id(),
                'status_approval' => 'pending', // Set as PENDING - waiting for approval
            ]);

            Log::info('✓ Payment record created | Pembayaran ID: ' . $pembayaran->id);
            Log::info('⏳ Status: PENDING (Menunggu persetujuan admin)');
            Log::info('✓ Sisa nominal BELUM DIKURANGI - akan dikurangi saat di-approve');

            // Create financial transaction record with PENDING status
            Log::info('Creating financial transaction record with PENDING status...');
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => $codePembayaran,
                'penerima_id' => $siswa->id,
                'penerima_tipe' => Siswa::class,
                'jenis_transaksi' => 'tagihan',
                'jumlah' => $jumlahBayar,
                'metode' => $request->metode ?? 'CASH',
                'referensi_tagihan_id' => $pembayaran->id,
                'tanggal_transaksi' => now(),
                'keterangan' => $keterangan,
                'created_by' => Auth::id(),
                'status_approval' => 'pending', // Set as PENDING - waiting for approval
                'status_verifikasi' => 'pending'
            ]);

            Log::info('✓ Financial transaction created | Transaksi ID: ' . $transaksi->id);
            Log::info('⏳ Transaksi Status: PENDING (Menunggu persetujuan admin)');
            Log::info('✓ Journal entries BELUM DIBUAT - akan dibuat saat di-approve');

            DB::commit();

            Log::info('========== PEMBAYARAN STORE COMPLETED - PENDING FOR APPROVAL ==========');

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dicatat dan menunggu persetujuan admin',
                'data' => [
                    'pembayaran' => $pembayaran->load(['tagihanSiswa.siswa', 'tagihanSiswa.tagihan.rekening', 'tagihanSiswa.tagihanItem.kategori']),
                    'transaksi' => $transaksi,
                    'tagihan_info' => [
                        'kode_tagihan' => $pembayaran->kode_tagihan,
                        'nama_tagihan' => $pembayaran->nama_tagihan,
                        'kategori_tagihan' => $pembayaran->kategori_tagihan,
                        'data_rekening' => $pembayaran->data_rekening
                    ],
                    'tagihan_siswa' => [
                        'id' => $tagihanSiswa->id,
                        'sisa_nominal' => $tagihanSiswa->sisa_nominal,
                        'status' => $tagihanSiswa->status,
                        'keterangan' => 'Belum ada perubahan - menunggu approve/reject dari admin'
                    ],
                    'status_approval' => 'pending',
                    'keterangan_status' => [
                        'pembayaran_status' => 'pending',
                        'transaksi_status' => 'pending',
                        'journal_entries' => 'Belum dibuat - akan dibuat saat di-approve'
                    ],
                    'next_action' => 'Tunggu persetujuan dari admin melalui endpoint approve atau reject'
                ]
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('❌ ERROR dalam pembayaran store');
            Log::error('Exception: ' . $th->getMessage());
            Log::error('File: ' . $th->getFile() . ' | Line: ' . $th->getLine());
            Log::error('========== PEMBAYARAN STORE FAILED ==========');

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
            'tagihanSiswa.tagihan.rekening',
            'tagihanSiswa.tagihanItem.kategori',
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
            'tagihanSiswa.tagihan.rekening',
            'tagihanSiswa.tagihanItem.kategori',
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
            'tagihanSiswa.tagihan.kategori',
            'tagihanSiswa.tagihan.rekening',
            'tagihanSiswa.tagihanItem.kategori'
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
            ->with(['user', 'keuanganTransaksi', 'tagihanSiswa.tagihanItem.kategori'])
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

    /**
     * @OA\Post(
     *     path="/pembayaran/{id}/upload-bukti",
     *     tags={"Pembayaran"},
     *     summary="Upload payment proof",
     *     description="Upload file bukti pembayaran dengan keterangan dari siswa",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file_bukti"},
     *                 @OA\Property(property="file_bukti", type="string", format="binary"),
     *                 @OA\Property(property="keterangan_siswa", type="string", example="Bukti transfer via BCA")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Upload successful"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function uploadBukti(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'file_bukti' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'keterangan_siswa' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $pembayaran = Pembayarantagihan::find($id);

        if (!$pembayaran) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran not found'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Delete old file if exists
            if ($pembayaran->file_bukti && Storage::disk('public')->exists($pembayaran->file_bukti)) {
                Storage::disk('public')->delete($pembayaran->file_bukti);
            }

            // Upload new file
            $file = $request->file('file_bukti');
            $fileName = 'bukti_bayar_' . $pembayaran->code_pembayaran . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('bukti_pembayaran', $fileName, 'public');

            // Update pembayaran
            $pembayaran->update([
                'file_bukti' => $filePath,
                'keterangan_siswa' => $request->keterangan_siswa,
                'status_approval' => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diupload',
                'data' => [
                    'pembayaran' => $pembayaran->fresh()->load(['tagihanSiswa.siswa', 'tagihanSiswa.tagihan.rekening', 'tagihanSiswa.tagihanItem.kategori']),
                    'tagihan_info' => [
                        'kode_tagihan' => $pembayaran->fresh()->kode_tagihan,
                        'nama_tagihan' => $pembayaran->fresh()->nama_tagihan,
                        'kategori_tagihan' => $pembayaran->fresh()->kategori_tagihan,
                        'data_rekening' => $pembayaran->fresh()->data_rekening
                    ]
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
     *     path="/pembayaran/{id}/approve",
     *     tags={"Pembayaran"},
     *     summary="Approve payment",
     *     description="Approve pembayaran oleh pihak sekolah",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="catatan_approval", type="string", example="Pembayaran sudah diverifikasi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment approved"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Payment already approved/rejected"
     *     )
     * )
     */
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'catatan_approval' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $pembayaran = Pembayarantagihan::with(['tagihanSiswa.siswa', 'tagihanSiswa.tagihan.rekening', 'tagihanSiswa.tagihanItem.kategori'])->find($id);

        if (!$pembayaran) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran not found'
            ], 404);
        }

        if ($pembayaran->status_approval !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran sudah ' . ($pembayaran->status_approval === 'approved' ? 'disetujui' : 'ditolak')
            ], 400);
        }

        DB::beginTransaction();
        try {
            Log::info('========== PEMBAYARAN APPROVE STARTED ==========');
            Log::info('Pembayaran ID: ' . $id);

            $tagihanSiswa = $pembayaran->tagihanSiswa;
            $siswa = $tagihanSiswa->siswa;
            $tagihan = $tagihanSiswa->tagihan;

            $jumlahBayar = (int) $pembayaran->jumlah_bayar;
            $sisaNominal = (int) $tagihanSiswa->sisa_nominal;

            Log::info('Siswa: ' . $siswa->user->name . ' | Tagihan: ' . $tagihan->nama_tagihan);
            Log::info('Sisa Nominal Sebelum: ' . $sisaNominal . ' | Jumlah Bayar: ' . $jumlahBayar);

            // Calculate remaining balance after payment
            $sisaSetelahBayar = $sisaNominal - $jumlahBayar;
            Log::info('Calculation: ' . $sisaNominal . ' - ' . $jumlahBayar . ' = ' . $sisaSetelahBayar);

            // Determine new status
            if ($sisaSetelahBayar == 0) {
                $statusTagihan = 1; // Lunas
                $sisaSetelahBayar = 0;
                $keterangan = "Pembayaran lunas {$tagihan->nama_tagihan} sebesar Rp " . number_format($tagihanSiswa->nominal, 0, ',', '.');
                Log::info('Status: LUNAS (Sisa = 0)');
            } else {
                $statusTagihan = 2; // Cicilan
                $keterangan = "Cicilan {$tagihan->nama_tagihan} bayar Rp " . number_format($jumlahBayar, 0, ',', '.') . " dari Rp " . number_format($tagihanSiswa->nominal, 0, ',', '.');
                Log::info('Status: CICILAN (Sisa = ' . $sisaSetelahBayar . ')');
            }

            // Update pembayaran approval status
            Log::info('Updating pembayaran approval status to APPROVED...');
            $pembayaran->update([
                'status_approval' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'catatan_approval' => $request->catatan_approval
            ]);
            Log::info('✓ Pembayaran approval updated | Catatan: ' . ($request->catatan_approval ?? 'Tidak ada'));

            // Update tagihan siswa - reduce sisa_nominal and update status
            Log::info('Updating tagihan siswa with new sisa_nominal and status...');
            $tagihanSiswa->update([
                'status' => $statusTagihan,
                'sisa_nominal' => $sisaSetelahBayar,
                'jumlah_dibayar' => ($tagihanSiswa->jumlah_dibayar ?? 0) + $jumlahBayar
            ]);
            Log::info('✓ Tagihan siswa updated | Status: ' . ($statusTagihan == 1 ? 'LUNAS' : 'CICILAN') . ' | Sisa Nominal: ' . $sisaSetelahBayar . ' | Jumlah Dibayar: ' . (($tagihanSiswa->jumlah_dibayar ?? 0) + $jumlahBayar));

            // Check if all student bills for this tagihan are paid
            Log::info('Checking if all bills are paid for tagihan ID: ' . $tagihanSiswa->tagihan_id);
            $hasUnpaid = Tagihansiswa::where('tagihan_id', $tagihanSiswa->tagihan_id)
                ->where('status', 0)
                ->exists();

            if (!$hasUnpaid) {
                Log::info('✓ All bills paid - Updating tagihan status to LUNAS');
                Tagihan::where('id', $tagihanSiswa->tagihan_id)
                    ->update(['status_tagihan' => 1]);
            } else {
                Log::info('⏳ Still have unpaid bills for this tagihan');
            }

            // Find and update existing financial transaction (created in store)
            Log::info('Finding financial transaction created during store...');
            $transaksi = Keuangan_transaksi::where('code_pembayaran', $pembayaran->code_pembayaran)
                ->where('referensi_tagihan_id', $pembayaran->id)
                ->first();

            if ($transaksi) {
                Log::info('✓ Financial transaction found | ID: ' . $transaksi->id);
                Log::info('Updating transaksi status from PENDING to APPROVED...');

                // Update financial transaction status to APPROVED
                $transaksi->update([
                    'status_approval' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);
                Log::info('✓ Financial transaction status updated to APPROVED');

                // Check if journal entries already exist
                $journalExists = Jurnals::where('transaksi_id', $transaksi->id)->exists();

                if (!$journalExists) {
                    Log::info('Creating journal entries...');
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
                    Log::info('✓ Journal entries created');
                } else {
                    Log::info('⏩ Journal entries already exist - skipping creation');
                }
            } else {
                Log::warning('⚠️ Financial transaction not found - creating new one');
                // Fallback: create if not found (should not happen normally)
                $transaksi = Keuangan_transaksi::create([
                    'code_pembayaran' => $pembayaran->code_pembayaran,
                    'penerima_id' => $siswa->id,
                    'penerima_tipe' => Siswa::class,
                    'jenis_transaksi' => 'tagihan',
                    'jumlah' => $jumlahBayar,
                    'metode' => $pembayaran->metode_bayar ?? 'CASH',
                    'referensi_tagihan_id' => $pembayaran->id,
                    'tanggal_transaksi' => $pembayaran->tanggal_bayar ?? now(),
                    'keterangan' => $keterangan,
                    'created_by' => Auth::id(),
                    'status_approval' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);
                Log::info('✓ Financial transaction created (fallback) | ID: ' . $transaksi->id);
            }

            DB::commit();

            Log::info('========== PEMBAYARAN APPROVE COMPLETED SUCCESSFULLY ==========');

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil disetujui dan nominal tagihan telah dikurangi',
                'data' => [
                    'pembayaran' => $pembayaran->fresh()->load(['tagihanSiswa.siswa', 'tagihanSiswa.tagihan.rekening', 'tagihanSiswa.tagihanItem.kategori', 'approvedBy']),
                    'tagihan_info' => [
                        'kode_tagihan' => $pembayaran->fresh()->kode_tagihan,
                        'nama_tagihan' => $pembayaran->fresh()->nama_tagihan,
                        'kategori_tagihan' => $pembayaran->fresh()->kategori_tagihan,
                        'data_rekening' => $pembayaran->fresh()->data_rekening
                    ],
                    'transaksi' => $transaksi->fresh(),
                    'tagihan_siswa' => $tagihanSiswa->fresh(),
                    'sisa_nominal' => $sisaSetelahBayar,
                    'status' => $statusTagihan == 1 ? 'Lunas' : 'Cicilan',
                    'approval_status' => [
                        'pembayaran_status' => 'approved',
                        'transaksi_status' => 'approved',
                        'journal_entries' => 'created'
                    ]
                ]
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('❌ ERROR dalam pembayaran approve');
            Log::error('Exception: ' . $th->getMessage());
            Log::error('File: ' . $th->getFile() . ' | Line: ' . $th->getLine());
            Log::error('========== PEMBAYARAN APPROVE FAILED ==========');

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/pembayaran/{id}/reject",
     *     tags={"Pembayaran"},
     *     summary="Reject payment",
     *     description="Reject pembayaran oleh pihak sekolah",
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
     *             required={"catatan_approval"},
     *             @OA\Property(property="catatan_approval", type="string", example="Bukti pembayaran tidak jelas, mohon upload ulang")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment rejected"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Payment already approved/rejected"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'catatan_approval' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $pembayaran = Pembayarantagihan::with(['tagihanSiswa.tagihan.rekening', 'tagihanSiswa.tagihanItem.kategori'])->find($id);

        if (!$pembayaran) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran not found'
            ], 404);
        }

        if ($pembayaran->status_approval !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran sudah ' . ($pembayaran->status_approval === 'approved' ? 'disetujui' : 'ditolak')
            ], 400);
        }

        DB::beginTransaction();
        try {
            Log::info('========== PEMBAYARAN REJECT STARTED ==========');
            Log::info('Pembayaran ID: ' . $id);
            Log::info('Code Pembayaran: ' . $pembayaran->code_pembayaran);
            Log::info('Alasan Penolakan: ' . $request->catatan_approval);

            $pembayaran->update([
                'status_approval' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'catatan_approval' => $request->catatan_approval
            ]);

            Log::info('✓ Pembayaran status updated to REJECTED');
            Log::info('✓ Sisa nominal TIDAK diubah - tetap seperti semula');
            Log::info('⚠️ Siswa dapat mengajukan pembayaran ulang');

            DB::commit();

            Log::info('========== PEMBAYARAN REJECT COMPLETED SUCCESSFULLY ==========');

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil ditolak',
                'data' => [
                    'pembayaran' => $pembayaran->fresh()->load(['tagihanSiswa.siswa', 'tagihanSiswa.tagihan.rekening', 'tagihanSiswa.tagihanItem.kategori', 'approvedBy']),
                    'tagihan_info' => [
                        'kode_tagihan' => $pembayaran->fresh()->kode_tagihan,
                        'nama_tagihan' => $pembayaran->fresh()->nama_tagihan,
                        'kategori_tagihan' => $pembayaran->fresh()->kategori_tagihan,
                        'data_rekening' => $pembayaran->fresh()->data_rekening
                    ],
                    'catatan' => 'Pembayaran ditolak dan sisa nominal tidak berubah. Siswa dapat mengajukan pembayaran ulang.'
                ]
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('❌ ERROR dalam pembayaran reject');
            Log::error('Exception: ' . $th->getMessage());
            Log::error('File: ' . $th->getFile() . ' | Line: ' . $th->getLine());
            Log::error('========== PEMBAYARAN REJECT FAILED ==========');

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/pembayaran/pending-approval",
     *     tags={"Pembayaran"},
     *     summary="Get pending approval payments",
     *     description="Get list of payments waiting for approval",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function pendingApproval(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        $pembayaran = Pembayarantagihan::with([
            'tagihanSiswa.siswa.kelas',
            'tagihanSiswa.tagihan.kategori',
            'tagihanSiswa.tagihan.rekening',
            'tagihanSiswa.tagihanItem.kategori',
            'user'
        ])
        ->where('status_approval', 'pending')
        ->whereNotNull('file_bukti')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $pembayaran
        ]);
    }
}
