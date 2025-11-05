<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Saldo_keuangan;
use App\Models\Keuangan_transaksi;
use App\Models\Jurnals;
use App\Models\setting_akun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TabunganController extends Controller
{
    /**
     * Get dashboard tabungan untuk siswa
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard(Request $request)
    {
        $siswaId = $request->get('siswa_id');
        $userId = $request->get('user_id');

        if (!$siswaId && !$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter siswa_id atau user_id diperlukan'
            ], 400);
        }

        // Jika menggunakan siswa_id, ambil user_id dari siswa
        if ($siswaId) {
            $siswa = Siswa::with('user')->find($siswaId);
            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan'
                ], 404);
            }
            $userId = $siswa->user->id ?? null;
        }

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        // Ambil saldo saat ini
        $saldo = Saldo_keuangan::where('user_id', $userId)
            ->where('status', 1)
            ->first();

        $saldoAkhir = $saldo?->saldo_akhir ?? 0;

        // Hitung total setoran
        $totalSetoran = Keuangan_transaksi::where('penerima_id', $siswaId ?? $userId)
            ->where('jenis_transaksi', 'setoran_tabungan')
            ->sum('jumlah');

        // Hitung total penarikan
        $totalPenarikan = Keuangan_transaksi::where('penerima_id', $siswaId ?? $userId)
            ->where('jenis_transaksi', 'penarikan_tabungan')
            ->sum('jumlah');

        // Jumlah transaksi
        $jumlahSetoran = Keuangan_transaksi::where('penerima_id', $siswaId ?? $userId)
            ->where('jenis_transaksi', 'setoran_tabungan')
            ->count();

        $jumlahPenarikan = Keuangan_transaksi::where('penerima_id', $siswaId ?? $userId)
            ->where('jenis_transaksi', 'penarikan_tabungan')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'saldo_saat_ini' => $saldoAkhir,
                'total_setoran' => $totalSetoran,
                'total_penarikan' => $totalPenarikan,
                'jumlah_setoran' => $jumlahSetoran,
                'jumlah_penarikan' => $jumlahPenarikan,
                'status_rekening' => $saldo ? ($saldo->status == 1 ? 'aktif' : 'non-aktif') : 'belum_ada',
            ]
        ]);
    }

    /**
     * Get list transaksi tabungan
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transaksi(Request $request)
    {
        $siswaId = $request->get('siswa_id');
        $userId = $request->get('user_id');
        $jenisTransaksi = $request->get('jenis_transaksi'); // setoran_tabungan, penarikan_tabungan
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $perPage = $request->get('per_page', 15);

        if (!$siswaId && !$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter siswa_id atau user_id diperlukan'
            ], 400);
        }

        // Jika menggunakan siswa_id, ambil user_id dari siswa
        if ($siswaId) {
            $siswa = Siswa::with('user')->find($siswaId);
            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan'
                ], 404);
            }
            $userId = $siswa->user->id ?? null;
        }

        // Query transaksi
        $query = Keuangan_transaksi::where('penerima_id', $siswaId ?? $userId)
            ->whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan'])
            ->with(['createdBy']);

        // Filter jenis transaksi
        if ($jenisTransaksi) {
            $query->where('jenis_transaksi', $jenisTransaksi);
        }

        // Filter tanggal
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transaksi = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Transform data
        $transaksi->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'code_pembayaran' => $item->code_pembayaran,
                'jenis_transaksi' => $item->jenis_transaksi,
                'jumlah' => $item->jumlah,
                'keterangan' => $item->keterangan,
                'tanggal_transaksi' => $item->tanggal_transaksi ?? $item->created_at,
                'created_by' => [
                    'id' => $item->createdBy->id ?? null,
                    'name' => $item->createdBy->name ?? null,
                ],
                'created_at' => $item->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    /**
     * Get detail transaksi tabungan
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailTransaksi($id)
    {
        $transaksi = Keuangan_transaksi::with(['createdBy', 'logs'])
            ->whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan'])
            ->find($id);

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        // Ambil siswa jika penerima_tipe adalah Siswa
        $siswa = null;
        if ($transaksi->penerima_tipe === 'App\Models\Siswa' || $transaksi->penerima_tipe === Siswa::class) {
            $siswa = Siswa::with('kelas')->find($transaksi->penerima_id);
        }

        $response = [
            'id' => $transaksi->id,
            'code_pembayaran' => $transaksi->code_pembayaran,
            'jenis_transaksi' => $transaksi->jenis_transaksi,
            'jumlah' => $transaksi->jumlah,
            'keterangan' => $transaksi->keterangan,
            'tanggal_transaksi' => $transaksi->tanggal_transaksi ?? $transaksi->created_at,
            'siswa' => $siswa ? [
                'id' => $siswa->id,
                'nisn' => $siswa->nisn,
                'nama' => $siswa->name,
                'kelas' => $siswa->kelas->nama_kelas ?? null,
            ] : null,
            'created_by' => [
                'id' => $transaksi->createdBy->id ?? null,
                'name' => $transaksi->createdBy->name ?? null,
            ],
            'created_at' => $transaksi->created_at,
            'updated_at' => $transaksi->updated_at,
        ];

        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    /**
     * Get riwayat saldo (mutasi rekening)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mutasiRekening(Request $request)
    {
        $siswaId = $request->get('siswa_id');
        $userId = $request->get('user_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if (!$siswaId && !$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter siswa_id atau user_id diperlukan'
            ], 400);
        }

        // Jika menggunakan siswa_id, ambil user_id dari siswa
        if ($siswaId) {
            $siswa = Siswa::with('user')->find($siswaId);
            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan'
                ], 404);
            }
            $userId = $siswa->user->id ?? null;
        }

        // Ambil saldo terakhir
        $saldoAkhir = Saldo_keuangan::where('user_id', $userId)
            ->where('status', 1)
            ->value('saldo_akhir') ?? 0;

        // Query transaksi
        $query = Keuangan_transaksi::where('penerima_id', $siswaId ?? $userId)
            ->whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan']);

        // Filter tanggal
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transaksis = $query->orderBy('created_at', 'asc')->get();

        // Hitung saldo berjalan
        $saldoBerjalan = 0;
        $mutasi = [];

        foreach ($transaksis as $trx) {
            if ($trx->jenis_transaksi === 'setoran_tabungan') {
                $saldoBerjalan += $trx->jumlah;
                $tipe = 'kredit';
            } else {
                $saldoBerjalan -= $trx->jumlah;
                $tipe = 'debit';
            }

            $mutasi[] = [
                'id' => $trx->id,
                'tanggal' => $trx->created_at,
                'code_pembayaran' => $trx->code_pembayaran,
                'jenis_transaksi' => $trx->jenis_transaksi,
                'tipe' => $tipe,
                'jumlah' => $trx->jumlah,
                'keterangan' => $trx->keterangan,
                'saldo' => $saldoBerjalan,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'saldo_akhir' => $saldoAkhir,
                'periode' => [
                    'start_date' => $startDate ?? 'semua',
                    'end_date' => $endDate ?? 'semua',
                ],
                'mutasi' => $mutasi
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/tabungan/setor",
     *     tags={"Tabungan"},
     *     summary="Setor tabungan siswa",
     *     description="Melakukan setoran ke tabungan siswa",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"siswa_id","jumlah"},
     *             @OA\Property(property="siswa_id", type="integer", example=1),
     *             @OA\Property(property="jumlah", type="number", example=100000),
     *             @OA\Property(property="metode", type="string", example="CASH", description="CASH, TRANSFER, QRIS, etc"),
     *             @OA\Property(property="keterangan", type="string", example="Setoran rutin bulanan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Setoran berhasil"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Siswa not found"
     *     )
     * )
     */
    public function setor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'siswa_id' => 'required|exists:siswas,id',
            'jumlah' => 'required|numeric|min:1000',
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
            $siswa = Siswa::with('user')->findOrFail($request->siswa_id);

            if (!$siswa->user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa belum memiliki akun user'
                ], 400);
            }

            $userId = $siswa->user->id;
            $jumlah = (float) $request->jumlah;
            $metode = $request->metode ?? 'CASH';

            // Ambil atau buat saldo keuangan
            $saldo = Saldo_keuangan::where('user_id', $userId)->first();

            if (!$saldo) {
                $saldo = Saldo_keuangan::create([
                    'user_id' => $userId,
                    'saldo_akhir' => 0,
                    'status' => 1,
                    'last_updated' => now()
                ]);
            }

            $saldoSebelum = $saldo->saldo_akhir;
            $saldoSesudah = $saldoSebelum + $jumlah;

            // Generate code pembayaran
            $codePembayaran = 'SET' . date('YmdHis') . rand(1000, 9999);

            // Buat transaksi
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => $codePembayaran,
                'penerima_id' => $siswa->id,
                'penerima_tipe' => Siswa::class,
                'jenis_transaksi' => 'setoran_tabungan',
                'jumlah' => $jumlah,
                'metode' => $metode,
                'tanggal_transaksi' => now(),
                'keterangan' => $request->keterangan ?? "Setoran tabungan sebesar Rp " . number_format($jumlah, 0, ',', '.'),
                'created_by' => Auth::id(),
            ]);

            // Update saldo
            $saldo->update([
                'saldo_akhir' => $saldoSesudah,
                'last_updated' => now()
            ]);

            // Create journal entries
            // Debit: Kas/Bank (tergantung metode)
            $akunKas = setting_akun::where('kategori', 'tabungan-masuk')
                ->where('debit', 1)
                ->first();

            if ($akunKas) {
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id' => $akunKas->akun_id,
                    'debit' => $jumlah,
                    'kredit' => 0,
                    'keterangan' => $transaksi->keterangan,
                ]);
            }

            // Kredit: Tabungan Siswa
            $akunTabungan = setting_akun::where('kategori', 'tabungan-masuk')
                ->where('kredit', 1)
                ->first();

            if ($akunTabungan) {
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id' => $akunTabungan->akun_id,
                    'debit' => 0,
                    'kredit' => $jumlah,
                    'keterangan' => $transaksi->keterangan,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Setoran tabungan berhasil',
                'data' => [
                    'transaksi' => $transaksi->load('createdBy'),
                    'saldo_sebelum' => $saldoSebelum,
                    'saldo_sesudah' => $saldoSesudah,
                    'siswa' => [
                        'id' => $siswa->id,
                        'nama' => $siswa->name,
                        'nisn' => $siswa->nisn,
                    ]
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
     * @OA\Post(
     *     path="/tabungan/tarik",
     *     tags={"Tabungan"},
     *     summary="Tarik tabungan siswa",
     *     description="Melakukan penarikan dari tabungan siswa",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"siswa_id","jumlah"},
     *             @OA\Property(property="siswa_id", type="integer", example=1),
     *             @OA\Property(property="jumlah", type="number", example=50000),
     *             @OA\Property(property="metode", type="string", example="CASH", description="CASH, TRANSFER, etc"),
     *             @OA\Property(property="keterangan", type="string", example="Penarikan untuk keperluan sekolah")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Penarikan berhasil"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Saldo tidak cukup"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Siswa not found or no savings account"
     *     )
     * )
     */
    public function tarik(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'siswa_id' => 'required|exists:siswas,id',
            'jumlah' => 'required|numeric|min:1000',
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
            $siswa = Siswa::with('user')->findOrFail($request->siswa_id);

            if (!$siswa->user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa belum memiliki akun user'
                ], 400);
            }

            $userId = $siswa->user->id;
            $jumlah = (float) $request->jumlah;
            $metode = $request->metode ?? 'CASH';

            // Ambil saldo keuangan
            $saldo = Saldo_keuangan::where('user_id', $userId)
                ->where('status', 1)
                ->first();

            if (!$saldo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rekening tabungan belum ada atau tidak aktif'
                ], 404);
            }

            $saldoSebelum = $saldo->saldo_akhir;

            // Validasi saldo cukup
            if ($saldoSebelum < $jumlah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi',
                    'data' => [
                        'saldo_tersedia' => $saldoSebelum,
                        'jumlah_penarikan' => $jumlah,
                        'kekurangan' => $jumlah - $saldoSebelum
                    ]
                ], 400);
            }

            $saldoSesudah = $saldoSebelum - $jumlah;

            // Generate code pembayaran
            $codePembayaran = 'TRK' . date('YmdHis') . rand(1000, 9999);

            // Buat transaksi
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => $codePembayaran,
                'penerima_id' => $siswa->id,
                'penerima_tipe' => Siswa::class,
                'jenis_transaksi' => 'penarikan_tabungan',
                'jumlah' => $jumlah,
                'metode' => $metode,
                'tanggal_transaksi' => now(),
                'keterangan' => $request->keterangan ?? "Penarikan tabungan sebesar Rp " . number_format($jumlah, 0, ',', '.'),
                'created_by' => Auth::id(),
            ]);

            // Update saldo
            $saldo->update([
                'saldo_akhir' => $saldoSesudah,
                'last_updated' => now()
            ]);

            // Create journal entries
            // Debit: Tabungan Siswa
            $akunTabungan = setting_akun::where('kategori', 'tabungan-keluar')
                ->where('debit', 1)
                ->first();

            if ($akunTabungan) {
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id' => $akunTabungan->akun_id,
                    'debit' => $jumlah,
                    'kredit' => 0,
                    'keterangan' => $transaksi->keterangan,
                ]);
            }

            // Kredit: Kas/Bank
            $akunKas = setting_akun::where('kategori', 'tabungan-keluar')
                ->where('kredit', 1)
                ->first();

            if ($akunKas) {
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id' => $akunKas->akun_id,
                    'debit' => 0,
                    'kredit' => $jumlah,
                    'keterangan' => $transaksi->keterangan,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penarikan tabungan berhasil',
                'data' => [
                    'transaksi' => $transaksi->load('createdBy'),
                    'saldo_sebelum' => $saldoSebelum,
                    'saldo_sesudah' => $saldoSesudah,
                    'siswa' => [
                        'id' => $siswa->id,
                        'nama' => $siswa->name,
                        'nisn' => $siswa->nisn,
                    ]
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
}
