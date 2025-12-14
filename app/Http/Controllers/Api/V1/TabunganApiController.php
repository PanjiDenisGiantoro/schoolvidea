<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DataRekening;
use App\Models\Jurnals;
use App\Models\Keuangan_transaksi;
use App\Models\Keuangan_transaksi_logs;
use App\Models\Saldo_keuangan;
use App\Models\setting_akun;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TabunganApiController extends Controller
{
    /**
     * API v1 - Setoran Tabungan
     * POST /api/v1/tabungan/setor
     */
    public function setor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'penerima_id' => 'required|exists:siswas,id',
            'jumlah' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string',
            'metode' => 'nullable|in:CASH,TRANSFER,TUNAI,NONTUNAI',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Ambil data siswa
            $siswa = Siswa::with('user')->findOrFail($request->penerima_id);

            $rekening = Saldo_keuangan::where('user_id', $siswa->user->id)->where('status', 1)->first();
            if (!$rekening) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rekening tabungan belum aktif'
                ], 400);
            }

            // Simpan transaksi utama
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => 'TST' . date('YmdHis') . rand(1000, 9999),
                'penerima_id' => $request->penerima_id,
                'penerima_tipe' => Siswa::class,
                'jenis_transaksi' => 'setoran_tabungan',
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'metode' => $request->metode ?? 'CASH',
                'status_verifikasi' => 'pending', // Default pending, butuh upload bukti
                'token' => null,
                'token_expired_at' => null,
                'status_approval' => 'pending',
                'created_by' => Auth::id() ?? $siswa->user->id,
            ]);

            $settings = setting_akun::where('kategori', 'tabungan')
                ->where('unit_id', $siswa->unit_id)
                ->where('status', '1')
                ->first();

            $akun_id = $settings->akun_id ?? null;

            if (!$akun_id) {
                throw new \Exception("Setting akun untuk kategori tabungan belum lengkap.");
            }

//            // Jurnal Kredit
//            Jurnals::create([
//                'transaksi_id' => $transaksi->id,
//                'akun_id' => $akun_id,
//                'debit' => 0,
//                'kredit' => $request->jumlah,
//                'keterangan' => $request->keterangan,
//            ]);

            $saldoSiswa = Saldo_keuangan::firstOrCreate(
                [
                    'user_id' => $siswa->user->id,
                    'status' => 1
                ],
                ['saldo_akhir' => 0]
            );

            // Log transaksi
            Keuangan_transaksi_logs::create([
                'transaksi_id' => $transaksi->id,
                'aksi' => 'create',
                'data_lama' => null,
                'data_baru' => json_encode($transaksi),
                'dilakukan_oleh' => Auth::id() ?? $siswa->user->id,
                'dilakukan_pada' => now(),
            ]);

            // Update saldo (akan di-approve setelah upload bukti)
            // $saldoSiswa->increment('saldo_akhir', $request->jumlah);

            DB::commit();

            // Get bank account data if payment method is TRANSFER
            $dataRekeningTujuan = null;
            if (strtoupper($transaksi->metode) === 'TRANSFER') {
                $rekening = DataRekening::where('status', '1')->first();
                if ($rekening) {
                    $dataRekeningTujuan = [
                        'id' => $rekening->id,
                        'allotment' => $rekening->allotment,
                        'account_code' => $rekening->account_code,
                        'account_name' => $rekening->account_name,
                        'owner_name' => $rekening->owner_name,
                        'account_number' => $rekening->account_number,
                        'account_pict' => $rekening->account_pict ? url($rekening->account_pict) : null,
                        'kcp_name' => $rekening->kcp_name,
                        'status' => $rekening->status,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Setoran berhasil dibuat. Silakan upload bukti transfer untuk verifikasi.',
                'data' => [
                    'transaksi_id' => $transaksi->id,
                    'code_pembayaran' => $transaksi->code_pembayaran,
                    'nomor_transaksi' => $transaksi->code_pembayaran,
                    'jenis_transaksi' => 'Setoran Tabungan',
                    'jumlah' => $transaksi->jumlah,
                    'biaya' => $transaksi->jumlah,
                    'tanggal_transaksi' => $transaksi->created_at->format('Y-m-d H:i:s'),
                    'tanggal_bayar' => null,
                    'metode_pembayaran' => $transaksi->metode,
                    'metode' => $transaksi->metode,
                    'keterangan' => $transaksi->keterangan,
                    'bukti_transfer' => null,
                    'data_rekening_tujuan' => $dataRekeningTujuan,
                    'deskripsi' => $transaksi->keterangan,
                    'status_pembayaran' => $transaksi->status_verifikasi,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API v1 - Tarik Tabungan
     * POST /api/v1/tabungan/tarik
     */
    public function tarik(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'penerima_id' => 'required|exists:siswas,id',
            'jumlah' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $siswa = Siswa::with('user')->findOrFail($request->penerima_id);

            $rekening = Saldo_keuangan::where('user_id', $siswa->user->id)->where('status', 1)->first();
            if (!$rekening) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rekening tabungan tidak ditemukan'
                ], 400);
            }

            $settings = setting_akun::where('kategori', 'tabungan-tarik')
                ->where('unit_id', $siswa->unit_id)
                ->where('status', '1')
                ->first();

            $akun_id = $settings->akun_id ?? null;

            if (!$akun_id) {
                throw new \Exception("Setting akun untuk kategori tabungan-tarik belum lengkap.");
            }

            // Ambil saldo siswa
            $saldoSiswa = Saldo_keuangan::firstOrCreate(
                [
                    'user_id' => $siswa->user->id,
                ],
                ['saldo_akhir' => 0]
            );

            if ($saldoSiswa->saldo_akhir < $request->jumlah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi untuk penarikan'
                ], 400);
            }

            // Generate token 6 digit
            $token = str_pad(rand(0, 99999), 6, '0', STR_PAD_LEFT);

            // Simpan transaksi utama
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => 'TRK' . date('YmdHis') . rand(1000, 9999),
                'penerima_id' => $request->penerima_id,
                'penerima_tipe' => Siswa::class,
                'jenis_transaksi' => 'penarikan_tabungan',
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'metode' => 'TUNAI',
                'status_verifikasi' => 'pending', // Default pending, butuh upload bukti
                'token' => $token,
                'token_expired_at' => now()->addDay(),
                'status_approval' => 'pending',
                'created_by' => Auth::id() ?? $siswa->user->id,
            ]);

            // Jurnal Debit
//            Jurnals::create([
//                'transaksi_id' => $transaksi->id,
//                'akun_id' => $akun_id,
//                'debit' => $request->jumlah,
//                'kredit' => 0,
//                'keterangan' => $request->keterangan,
//            ]);

            // Update saldo siswa (kurangi saldo)
//            $saldoSiswa->decrement('saldo_akhir', $request->jumlah);

            // Catat log transaksi
            Keuangan_transaksi_logs::create([
                'transaksi_id' => $transaksi->id,
                'aksi' => 'withdraw',
                'data_lama' => json_encode(['saldo_awal' => $saldoSiswa->saldo_akhir + $request->jumlah]),
                'data_baru' => json_encode(['saldo_akhir' => $saldoSiswa->saldo_akhir]),
                'dilakukan_oleh' => Auth::id() ?? $siswa->user->id,
                'dilakukan_pada' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penarikan berhasil dibuat. Silakan upload bukti dan tunggu approval.',
                'data' => [
                    'transaksi_id' => $transaksi->id,
                    'code_pembayaran' => $transaksi->code_pembayaran,
                    'nomor_transaksi' => $transaksi->code_pembayaran,
                    'jenis_transaksi' => 'Penarikan Tabungan',
                    'jumlah' => $transaksi->jumlah,
                    'biaya' => $transaksi->jumlah,
                    'tanggal_transaksi' => $transaksi->created_at->format('Y-m-d H:i:s'),
                    'tanggal_bayar' => null,
                    'metode_pembayaran' => $transaksi->metode,
                    'metode' => $transaksi->metode,
                    'keterangan' => $transaksi->keterangan,
                    'bukti_transfer' => null,
                    'data_rekening_tujuan' => null,
                    'deskripsi' => $transaksi->keterangan,
                    'status_pembayaran' => $transaksi->status_verifikasi,
                    'token' => $transaksi->token,
                    'token_expired_at' => $transaksi->token_expired_at->format('Y-m-d H:i:s'),
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API v1 - Upload Bukti Transfer
     * POST /api/v1/tabungan/{id}/upload-bukti
     */
    public function uploadBukti(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $transaksi = Keuangan_transaksi::findOrFail($id);

//            // Hapus file lama jika ada
//            if ($transaksi->bukti_transfer && file_exists(public_path($transaksi->bukti_transfer))) {
//                unlink(public_path($transaksi->bukti_transfer));
//            }

            // Upload file baru
            $file = $request->file('bukti_transfer');
            $filename = 'bukti_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/bukti_transfer'), $filename);

            // Update database
            $transaksi->update([
                'bukti_transfer' => 'uploads/bukti_transfer/' . $filename,
                'status_verifikasi' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bukti transfer berhasil diupload. Menunggu verifikasi admin.',
                'data' => [
                    'transaksi_id' => $transaksi->id,
                    'code_pembayaran' => $transaksi->code_pembayaran,
                    'bukti_transfer' => url($transaksi->bukti_transfer),
                    'status_verifikasi' => $transaksi->status_verifikasi,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload bukti transfer: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * API v1 - List Transaksi (dengan filter status)
     * GET /api/v1/tabungan/transaksi
     */
    public function transaksi(Request $request)
    {
        try {
            $query = Keuangan_transaksi::with(['penerima', 'creator', 'verifier'])
                ->whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan'])
                ->orderBy('created_at', 'desc');

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status_verifikasi', $request->status);
            }

            // Filter by jenis_transaksi if provided
            if ($request->has('jenis')) {
                $query->where('jenis_transaksi', $request->jenis);
            }

            $transaksis = $query->get();

            $data = $transaksis->map(function ($trx) {
                return [
                    'transaksi_id' => $trx->id,
                    'nomor_transaksi' => $trx->code_pembayaran,
                    'code_pembayaran' => $trx->code_pembayaran,
                    'jenis_transaksi' => $trx->jenis_transaksi,
                    'jumlah' => $trx->jumlah,
                    'tanggal_transaksi' => $trx->created_at->format('Y-m-d H:i:s'),
                    'deskripsi' => $trx->keterangan,
                    'status_pembayaran' => $trx->status_verifikasi,
                    'status_verifikasi' => $trx->status_verifikasi,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'List transaksi berhasil diambil',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil list transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API v1 - Detail Transaksi
     * GET /api/v1/tabungan/{id}/detail
     */
    public function detail($id)
    {
        try {
            $transaksi = Keuangan_transaksi::with(['penerima', 'creator', 'verifier'])
                ->findOrFail($id);


            return response()->json([
                'success' => true,
                'message' => 'Detail transaksi berhasil diambil',
                'data' => [
                    'transaksi_id' => $transaksi->id,
                    'nomor_transaksi' => $transaksi->code_pembayaran,
                    'jenis_transaksi' => $transaksi->jenis_transaksi === 'setoran_tabungan' ? 'Setoran Tabungan' : 'Penarikan Tabungan',
                    'biaya' => $transaksi->jumlah,
                    'jumlah' => $transaksi->jumlah,
                    'tanggal_transaksi' => $transaksi->created_at->format('Y-m-d H:i:s'),
                    'deskripsi' => $transaksi->keterangan,
                    'status_pembayaran' => $transaksi->status_verifikasi,
                    'status_approval' => $transaksi->status_approval,
                    'metode' => $transaksi->metode,
                    'bukti_transfer' => $transaksi->bukti_transfer ? url($transaksi->bukti_transfer) : null,
                    'catatan_verifikasi' => $transaksi->catatan_verifikasi,
                    'verified_by' => $transaksi->verifier ? $transaksi->verifier->name : null,
                    'verified_at' => $transaksi->verified_at ? $transaksi->verified_at->format('Y-m-d H:i:s') : null,
                    'token' => $transaksi->token,
                    'token_expired_at' => $transaksi->token_expired_at ? $transaksi->token_expired_at->format('Y-m-d H:i:s') : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }
    }

    /**
     * API v1 - Approve Transaksi (untuk Web Admin)
     * POST /api/v1/tabungan/{id}/approve
     */
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'catatan_verifikasi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $transaksi = Keuangan_transaksi::findOrFail($id);

            // Cek apakah transaksi sudah diverifikasi
            if ($transaksi->status_verifikasi === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi sudah diapprove sebelumnya'
                ], 400);
            }

            // Update status verifikasi di keuangan_transaksis ONLY
            $transaksi->update([
                'status_verifikasi' => 'approved',
                'status_approval' => 'approved',
                'catatan_verifikasi' => $request->catatan_verifikasi,
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);

            // === TABUNGAN SETOR: Tambah saldo setelah approve ===
            if ($transaksi->jenis_transaksi === 'setoran_tabungan' && $transaksi->penerima_tipe === Siswa::class) {
                $siswa = Siswa::findOrFail($transaksi->penerima_id);
                $saldoSiswa = Saldo_keuangan::where('user_id', $siswa->user->id)->first();
                if ($saldoSiswa) {
                    $saldoSiswa->increment('saldo_akhir', $transaksi->jumlah);
                }
            }

            // === TABUNGAN TARIK: Saldo sudah dikurangi saat create ===
            // Approve hanya mengkonfirmasi, tidak mengubah saldo

            // Log activity
            Keuangan_transaksi_logs::create([
                'transaksi_id' => $transaksi->id,
                'aksi' => 'approve',
                'data_lama' => json_encode(['status_verifikasi' => 'pending']),
                'data_baru' => json_encode(['status_verifikasi' => 'approved']),
                'dilakukan_oleh' => Auth::id(),
                'dilakukan_pada' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diapprove',
                'data' => [
                    'transaksi_id' => $transaksi->id,
                    'code_pembayaran' => $transaksi->code_pembayaran,
                    'status_verifikasi' => $transaksi->status_verifikasi,
                    'status_approval' => $transaksi->status_approval,
                    'verified_at' => $transaksi->verified_at->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API v1 - Reject Transaksi (untuk Web Admin)
     * POST /api/v1/tabungan/{id}/reject
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'catatan_verifikasi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $transaksi = Keuangan_transaksi::findOrFail($id);

            // Cek apakah transaksi sudah diverifikasi
            if ($transaksi->status_verifikasi === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi sudah direject sebelumnya'
                ], 400);
            }

            // Update status verifikasi di keuangan_transaksis ONLY
            $transaksi->update([
                'status_verifikasi' => 'rejected',
                'status_approval' => 'rejected',
                'catatan_verifikasi' => $request->catatan_verifikasi,
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);

            // === TABUNGAN SETOR: Tidak perlu rollback (belum ditambah) ===
            // Saldo belum ditambah karena pending, reject hanya ubah status

            // === TABUNGAN TARIK: Kembalikan saldo yang sudah dikurangi ===
            if ($transaksi->jenis_transaksi === 'penarikan_tabungan' && $transaksi->penerima_tipe === Siswa::class) {
                $siswa = Siswa::findOrFail($transaksi->penerima_id);
                $saldoSiswa = Saldo_keuangan::where('user_id', $siswa->user->id)->first();
                if ($saldoSiswa) {
                    // Kembalikan saldo yang sudah dikurangi saat create
                    $saldoSiswa->increment('saldo_akhir', $transaksi->jumlah);
                }
            }

            // Log activity
            Keuangan_transaksi_logs::create([
                'transaksi_id' => $transaksi->id,
                'aksi' => 'reject',
                'data_lama' => json_encode(['status_verifikasi' => 'pending']),
                'data_baru' => json_encode(['status_verifikasi' => 'rejected']),
                'dilakukan_oleh' => Auth::id(),
                'dilakukan_pada' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil direject',
                'data' => [
                    'transaksi_id' => $transaksi->id,
                    'code_pembayaran' => $transaksi->code_pembayaran,
                    'status_verifikasi' => $transaksi->status_verifikasi,
                    'status_approval' => $transaksi->status_approval,
                    'catatan_verifikasi' => $transaksi->catatan_verifikasi,
                    'verified_at' => $transaksi->verified_at->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal reject transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}
