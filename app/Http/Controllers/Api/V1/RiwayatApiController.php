<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Keuangan_transaksi;
use App\Models\Keuangan_transaksi_logs;
use App\Models\Pembayarantagihan;
use App\Models\PembayaranTagihanDetail;
use App\Models\Saldo_keuangan;
use App\Models\Tagihansiswa;
use App\Models\Tagihansiswa_mutasi;
use App\Models\Siswa;
use App\Models\DataRekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RiwayatApiController extends Controller
{
    /**
     * API v1 - Riwayat Semua Transaksi (Tabungan + Tagihan + Pembayaran)
     * GET /api/v1/riwayat
     */
    public function index(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $type = $request->get('type'); // tabungan, tagihan, pembayaran
            $status = $request->get('status');

            // Tabungan Transactions
            $tabunganQuery = Keuangan_transaksi::whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan'])
                ->with(['penerima', 'creator']);

            // Pembayaran Transactions
            $pembayaranQuery = Keuangan_transaksi::where('jenis_transaksi', 'pembayaran')
                ->with(['penerima', 'creator']);

            // Apply filters
            if ($startDate && $endDate) {
                $tabunganQuery->whereBetween('created_at', [$startDate, $endDate]);
                $pembayaranQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            if ($status) {
                $tabunganQuery->where('status_verifikasi', $status);
                $pembayaranQuery->where('status_verifikasi', $status);
            }

            // Filter by type
            if ($type === 'tabungan') {
                $transactions = $tabunganQuery->orderBy('created_at', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);
            } elseif ($type === 'pembayaran') {
                $transactions = $pembayaranQuery->orderBy('created_at', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);
            } else {
                // Combined query
                $tabunganData = $tabunganQuery->get();
                $pembayaranData = $pembayaranQuery->get();
                $merged = $tabunganData->concat($pembayaranData)
                    ->sortByDesc('created_at');

                $paginated = $merged->forPage($page, $perPage);
                $total = $merged->count();

                $transactions = new \Illuminate\Pagination\Paginator(
                    $paginated,
                    $perPage,
                    $page,
                    [
                        'path' => $request->url(),
                        'query' => $request->query(),
                    ]
                );
                $transactions->setTotal($total);
            }

            $data = $transactions->map(function ($trx) {
                return $this->formatTransaction($trx);
            });

            return response()->json([
                'success' => true,
                'message' => 'Riwayat transaksi berhasil diambil',
                'data' => $data,
                'meta' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API v1 - Riwayat Tabungan (Setor/Tarik)
     * GET /api/v1/riwayat/tabungan
     */
    public function riwayatTabungan(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);
            $siswaId = $request->get('siswa_id');
            $jenis = $request->get('jenis'); // setor, tarik, semua
            $status = $request->get('status'); // pending, approved, rejected
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = Keuangan_transaksi::whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan'])
                ->with(['penerima', 'creator', 'verifier']);

            // Filter by siswa
            if ($siswaId) {
                $query->where('penerima_id', $siswaId);
            }

            // Filter by jenis
            if ($jenis === 'setor') {
                $query->where('jenis_transaksi', 'setoran_tabungan');
            } elseif ($jenis === 'tarik') {
                $query->where('jenis_transaksi', 'penarikan_tabungan');
            }

            // Filter by status
            if ($status) {
                $query->where('status_verifikasi', $status);
            }

            // Filter by date range
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $data = $transactions->map(function ($trx) {
                // Get rekening data if metode is TRANSFER
                $data_rekenings = null;
                if (strtoupper($trx->metode) === 'TRANSFER') {
                    $rekening = DataRekening::where('status', '1')->first();
                    if ($rekening) {
                        $data_rekenings = [
                            'id' => $rekening->id,
                            'nama_rekening' => $rekening->account_name,
                            'nomor_rekening' => $rekening->account_number,
                            'nama_pemilik' => $rekening->owner_name,
                            'bank' => $rekening->account_code,
                            'kcp_name' => $rekening->kcp_name,
                        ];
                    }
                }

                return [
                    'id' => $trx->id,
                    'nomor_transaksi' => $trx->code_pembayaran,
                    'code' => $trx->code_pembayaran,
                    'siswa_id' => $trx->penerima_id,
                    'siswa_nama' => $trx->penerima ? $trx->penerima->nama_lengkap : 'N/A',
                    'jenis' => $trx->jenis_transaksi === 'setoran_tabungan' ? 'Setor' : 'Tarik',
                    'jumlah' => (float)$trx->jumlah,
                    'tanggal_transaksi' => $trx->created_at,
                    'tanggal_bayar' => $trx->verified_at ? $trx->verified_at : null,
                    'metode_pembayaran' => $trx->metode,
                    'metode' => $trx->metode,
                    'keterangan' => $trx->keterangan,
                    'status' => $trx->status_verifikasi,
                    'bukti_transfer' => $trx->bukti_transfer ? url($trx->bukti_transfer) : null,
                    'data_rekening_tujuan' => $data_rekenings,
                    'data_rekenings' => $data_rekenings,
                    'verified_by' => $trx->verifier ? $trx->verifier->name : null,
                    'verified_at' => $trx->verified_at ? $trx->verified_at : null,
                    'catatan_verifikasi' => $trx->catatan_verifikasi,
                    'token' => $trx->token,
                    'expired_kode' => $trx->token_expired_at ? $trx->token_expired_at : null,
                    'created_at' => $trx->created_at,
                    'updated_at' => $trx->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Riwayat tabungan berhasil diambil',
                'data' => $data,
                'meta' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat tabungan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API v1 - Riwayat Tabungan Setor
     * GET /api/v1/riwayat/tabungan/setor
     */
    public function riwayatTabunganSetor(Request $request)
    {
        $request->merge(['jenis' => 'setor']);
        return $this->riwayatTabungan($request);
    }

    /**
     * API v1 - Riwayat Tabungan Tarik
     * GET /api/v1/riwayat/tabungan/tarik
     */
    public function riwayatTabunganTarik(Request $request)
    {
        $request->merge(['jenis' => 'tarik']);
        return $this->riwayatTabungan($request);
    }

    /**
     * API v1 - Riwayat Tabungan per Siswa
     * GET /api/v1/riwayat/tabungan/siswa/{siswaId}
     */
    public function riwayatTabunganSiswa(Request $request, $siswaId)
    {
        $request->merge(['siswa_id' => $siswaId]);
        return $this->riwayatTabungan($request);
    }

    /**
     * API v1 - Detail Riwayat Tabungan dengan Audit Trail
     * GET /api/v1/riwayat/tabungan/{id}
     */
    public function detailTabungan($id)
    {
        try {
            $transaksi = Keuangan_transaksi::with(['penerima', 'creator', 'verifier'])
                ->findOrFail($id);

            // Get audit logs
            $logs = Keuangan_transaksi_logs::where('transaksi_id', $id)
                ->with('pelaku')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Detail riwayat tabungan berhasil diambil',
                'data' => [
                    'id' => $transaksi->id,
                    'code' => $transaksi->code_pembayaran,
                    'siswa_id' => $transaksi->penerima_id,
                    'siswa_nama' => $transaksi->penerima ? $transaksi->penerima->nama_lengkap : 'N/A',
                    'jenis' => $transaksi->jenis_transaksi === 'setoran_tabungan' ? 'Setor' : 'Tarik',
                    'jumlah' => (float)$transaksi->jumlah,
                    'metode' => $transaksi->metode,
                    'keterangan' => $transaksi->keterangan,
                    'status' => $transaksi->status_verifikasi,
                    'status_approval' => $transaksi->status_approval,
                    'tanggal_transaksi' => $transaksi->created_at,
                    'bukti_transfer' => $transaksi->bukti_transfer ? url($transaksi->bukti_transfer) : null,
                    'verified_by' => $transaksi->verifier ? $transaksi->verifier->name : null,
                    'verified_at' => $transaksi->verified_at ? $transaksi->verified_at : null,
                    'catatan_verifikasi' => $transaksi->catatan_verifikasi,
                    'token' => $transaksi->token,
                    'token_expired_at' => $transaksi->token_expired_at ? $transaksi->token_expired_at : null,
                ],
                'audit_trail' => $logs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'aksi' => $log->aksi,
                        'dilakukan_oleh' => $log->pelaku ? $log->pelaku->name : 'N/A',
                        'keterangan' => $log->keterangan,
                        'data_lama' => $log->data_lama ? json_decode($log->data_lama) : null,
                        'data_baru' => $log->data_baru ? json_decode($log->data_baru) : null,
                        'dilakukan_pada' => $log->created_at,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Riwayat tabungan tidak ditemukan'
            ], 404);
        }
    }

    /**
     * API v1 - Riwayat Tagihan (dengan filter dan pagination)
     * GET /api/v1/riwayat/tagihan
     */
    public function riwayatTagihan(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);
            $siswaId = $request->get('siswa_id');
            $status = $request->get('status'); // pending, partial, paid, cancelled
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = Tagihansiswa::with(['siswa', 'tagihan', 'tagihanitem.kategori'])
                ->orderBy('created_at', 'desc');

            // Filter by siswa
            if ($siswaId) {
                $query->where('siswa_id', $siswaId);
            }

            // Filter by status
            if ($status) {
                $query->where('status', $status);
            }

            // Filter by date range
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            $tagihans = $query->paginate($perPage, ['*'], 'page', $page);

            // Helper function to map status to text
            $mapStatus = function($status) {
                $statusMap = [
                    '0' => 'Belum Lunas',
                    '1' => 'Lunas',
                    '2' => 'Cicilan'
                ];
                return $statusMap[$status] ?? 'Unknown';
            };

            // Helper function to get month name in Indonesian
            $getBulanText = function($bulanKe, $tahun) {
                if (!$bulanKe || !$tahun) return 'N/A';

                $bulanArray = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                    4 => 'April', 5 => 'Mei', 6 => 'Juni',
                    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                $bulan = isset($bulanArray[$bulanKe]) ? $bulanArray[$bulanKe] : $bulanKe;
                return "$bulan $tahun";
            };

            $data = $tagihans->map(function ($tgh) use ($mapStatus, $getBulanText) {
                // Get nominal tagihan
                $nominalTagihan = (float)($tgh->tagihanitem ? $tgh->tagihanitem->nominal : 0);

                // Get kategori nama
                $namaKategori = $tgh->tagihanitem && $tgh->tagihanitem->kategori ? $tgh->tagihanitem->kategori->nama_kategori : 'N/A';

                // Get pembayaran details
                $pembayaran = Pembayarantagihan::where('tagihan_siswa_id', $tgh->id)
                    ->first();

                // Get tahun dari tagihan
                $tahunTagihan = $tgh->tagihan ? $tgh->tagihan->tahun_mulai : null;

                // Build bulan text
                $bulanText = $getBulanText($tgh->bulan_ke, $tahunTagihan);

                return [
                    'id' => $tgh->id,
                    'tagihan_id' => $tgh->tagihan_id,
                    'siswa_id' => $tgh->siswa_id,
                    'siswa_nama' => $tgh->siswa ? $tgh->siswa->user->name : 'N/A',
                    'nisn' => $tgh->siswa ? $tgh->siswa->nisn : 'N/A',
                    'nama_tagihan' => $tgh->tagihan ? $tgh->tagihan->nama_tagihan : 'N/A',
                    'nama_kategori' => $namaKategori,
                    'bulan_ke' => $tgh->bulan_ke,
                    'bulan_text' => $bulanText,
                    'nominal_tagihan' => $nominalTagihan,
                    'sisa_nominal' => (float)$tgh->sisa_nominal,
                    'status_code' => $tgh->status,
                    'status_text' => $mapStatus($tgh->status),
                    'tanggal_tagihan' => $tgh->created_at,
                    'tanggal_bayar' => $tgh->tanggal_bayar ,
                    'payment_details' => $pembayaran ? [
                        'id' => $pembayaran->id,
                        'code_pembayaran' => $pembayaran->code_pembayaran,
                        'jumlah_bayar' => (float)$pembayaran->jumlah_bayar,
                        'metode_bayar' => $pembayaran->metode_bayar,
                        'file_bukti' => $pembayaran->file_bukti ? Storage::disk('public')->url($pembayaran->file_bukti) : null,
                        'tanggal_bayar' => $pembayaran->tanggal_bayar,
                        'status_approval' => $pembayaran->status_approval,
                        'keterangan' => $pembayaran->keterangan,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Riwayat tagihan berhasil diambil',
                'data' => $data,
                'meta' => [
                    'total' => $tagihans->total(),
                    'per_page' => $tagihans->perPage(),
                    'current_page' => $tagihans->currentPage(),
                    'last_page' => $tagihans->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat tagihan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API v1 - Riwayat Tagihan per Siswa
     * GET /api/v1/riwayat/tagihan/siswa/{siswaId}
     */
    public function riwayatTagihanSiswa(Request $request, $siswaId)
    {
        $request->merge(['siswa_id' => $siswaId]);
        return $this->riwayatTagihan($request);
    }

    /**
     * API v1 - Detail Riwayat Tagihan dengan Mutasi dan Pembayaran
     * GET /api/v1/riwayat/tagihan/{id}
     */
    public function detailTagihan($id)
    {
        try {
            $tagihan = Tagihansiswa::with([
                'siswa',
                'tagihan.rekening',
                'tagihanitem',
                'pembayarantagihan'
            ])->findOrFail($id);

            // Get mutations
            $mutasi = Tagihansiswa_mutasi::where('tagihan_siswa_id', $id)
                ->with(['createdBy', 'approvedBy'])
                ->orderBy('created_at', 'asc')
                ->get();

            // Get payments
            $pembayaran = Pembayarantagihan::where('tagihan_siswa_id', $id)
                ->with(['approvedBy'])
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Detail riwayat tagihan berhasil diambil',
                'data' => [
                    'id' => $tagihan->id,
                    'tagihan_id' => $tagihan->tagihan_id,
                    'siswa_id' => $tagihan->siswa_id,
                    'siswa_nama' => $tagihan->siswa ? $tagihan->siswa->nama_lengkap : 'N/A',
                    'bulan_ke' => $tagihan->bulan_ke,
                    'nominal_tagihan' => (float)($tagihan->tagihanitem ? $tagihan->tagihanitem->nominal : 0),
                    'sisa_nominal' => (float)$tagihan->sisa_nominal,
                    'status' => $tagihan->status,
                    'tanggal_bayar' => $tagihan->tanggal_bayar  ,
                    'tanggal_tagihan' => $tagihan->created_at,
                ],
                'mutations' => $mutasi->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'code_mutasi' => $m->code_mutasi,
                        'jenis_mutasi' => $m->jenis_mutasi,
                        'nominal_sebelum' => (float)$m->nominal_sebelum,
                        'nominal_perubahan' => (float)$m->nominal_perubahan,
                        'nominal_sesudah' => (float)$m->nominal_sesudah,
                        'keterangan' => $m->keterangan,
                        'alasan' => $m->alasan,
                        'status_mutasi' => $m->status_mutasi,
                        'created_by' => $m->createdBy ? $m->createdBy->name : null,
                        'approved_by' => $m->approvedBy ? $m->approvedBy->name : null,
                        'approved_at' => $m->approved_at ? $m->approved_at : null,
                        'created_at' => $m->created_at,
                    ];
                }),
                'payments' => $pembayaran->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'jumlah_bayar' => (float)$p->jumlah_bayar,
                        'tanggal_bayar' => $p->tanggal_bayar->format('Y-m-d'),
                        'metode_bayar' => $p->metode_bayar,
                        'keterangan' => $p->keterangan,
                        'status_approval' => $p->status_approval,
                        'file_bukti' => $p->file_bukti ? Storage::disk('public')->url($p->file_bukti) : null,
                        'keterangan_siswa' => $p->keterangan_siswa,
                        'catatan_approval' => $p->catatan_approval,
                        'approved_by' => $p->approvedBy ? $p->approvedBy->name : null,
                        'approved_at' => $p->approved_at ? $p->approved_at : null,
                        'created_at' => $p->created_at,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Riwayat tagihan tidak ditemukan'
            ], 404);
        }
    }

    /**
     * API v1 - Riwayat Pembayaran Tagihan
     * GET /api/v1/riwayat/tagihan-pembayaran
     */
    public function riwayatPembayaranTagihan(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);
            $siswaId = $request->get('siswa_id');
            $status = $request->get('status'); // pending, approved, rejected
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = Pembayarantagihan::with([
                'tagihanSiswa.siswa',
                'tagihanSiswa.tagihanItem.kategori',
                'tagihanSiswa.potonganSiswa.potongan',
                'tagihanSiswa.tagihan.rekening',
                'keuanganTransaksi',
                'user',
                'approvedBy'
            ])->orderBy('created_at', 'desc');

            // Filter by siswa
            if ($siswaId) {
                $query->whereHas('tagihanSiswa', function ($q) use ($siswaId) {
                    $q->where('siswa_id', $siswaId);
                });
            }

            // Filter by status
            if ($status) {
                $query->where('status_approval', $status);
            }

            // Filter by date range
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            $pembayaran = $query->paginate($perPage, ['*'], 'page', $page);

            $data = $pembayaran->map(function ($p) {
                // Get kategori info from tagihanItem
                $kategoriCode = null;
                $namaKategori = 'N/A';
                if ($p->tagihanSiswa && $p->tagihanSiswa->tagihanItem && $p->tagihanSiswa->tagihanItem->kategori) {
                    $kategoriCode = $p->tagihanSiswa->tagihanItem->kategori->kode_kategori ?? null;
                    $namaKategori = $p->tagihanSiswa->tagihanItem->kategori->nama_kategori;
                }

                // Get status_verifikasi and catatan_verifikasi from keuangan_transaksi
                $statusVerifikasi = $p->keuanganTransaksi ? $p->keuanganTransaksi->status_verifikasi : null;
                $catatanVerifikasi = $p->keuanganTransaksi ? $p->keuanganTransaksi->catatan_verifikasi : null;

                // Get potongan from tagihanSiswa
                $potonganList = [];
                if ($p->tagihanSiswa && $p->tagihanSiswa->potonganSiswa) {
                    $potonganList = $p->tagihanSiswa->potonganSiswa->map(function ($potongan) {
                        return [
                            'id' => $potongan->id,
                            'nominal' => (float)$potongan->nominal,
                            'keterangan' => $potongan->keterangan,
                            'potongan_data' => $potongan->potongan ? [
                                'id' => $potongan->potongan->id,
                                'nama' => $potongan->potongan->nama,
                                'nominal' => (float)$potongan->potongan->nominal,
                            ] : null,
                        ];
                    })->toArray();
                }

                // Calculate bulan and tahun info
                $bulanArray = [
                    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];
                $bulanKeOriginal = $p->tagihanSiswa ? $p->tagihanSiswa->bulan_ke : null;
                $tahunMulai = $p->tagihanSiswa && $p->tagihanSiswa->tagihan ? $p->tagihanSiswa->tagihan->tahun_mulai : null;

                // Calculate actual month and year based on bulan_ke
                if ($bulanKeOriginal && $tahunMulai) {
                    $yearOffset = floor(($bulanKeOriginal - 1) / 12);
                    $monthIndex = ($bulanKeOriginal - 1) % 12;
                    $tahunTagihan = $tahunMulai + $yearOffset;
                    $bulanText = ($bulanArray[$monthIndex] ?? 'N/A') . ' ' . $tahunTagihan;
                } else {
                    $tahunTagihan = null;
                    $bulanText = 'N/A';
                }

                // Calculate potongan total
                $totalPotongan = 0;
                foreach ($potonganList as $potongan) {
                    $totalPotongan += $potongan['nominal'];
                }

                // Get kelas info from siswa
                $kelasNama = 'N/A';
                if ($p->tagihanSiswa && $p->tagihanSiswa->siswa && $p->tagihanSiswa->siswa->kelas) {
                    $kelasNama = $p->tagihanSiswa->siswa->kelas->nama_kelas;
                }

                // Get nominal tagihan from tagihanItem
                $nominalTagihan = $p->tagihanSiswa && $p->tagihanSiswa->tagihanItem ? (float)$p->tagihanSiswa->tagihanItem->nominal : 0;

                // Calculate tunggakan
                $sisaNominal = $p->tagihanSiswa ? (float)$p->tagihanSiswa->sisa_nominal : 0;

                // Get data rekening dari tagihan
                $dataRekenings = null;
                if ($p->tagihanSiswa && $p->tagihanSiswa->tagihan && $p->tagihanSiswa->tagihan->rekening) {
                    $rekening = $p->tagihanSiswa->tagihan->rekening;
                    $dataRekenings = [
                        'id' => $rekening->id,
                        'nama_rekening' => $rekening->account_name,
                        'nomor_rekening' => $rekening->account_number,
                        'nama_pemilik' => $rekening->owner_name,
                        'kcp_name' => $rekening->kcp_name,
                        'allotment' => $rekening->allotment,
                    ];
                }

                // Check if this is a multi-payment (has head_tagihan)
                $listTagihan = null;
                if ($p->head_tagihan) {
                    // Fetch all related payment details
                    $detailPembayaran = PembayaranTagihanDetail::where('head_tagihan', $p->head_tagihan)
                        ->with([
                            'tagihanSiswa.siswa',
                            'tagihanSiswa.tagihanItem.kategori',
                            'tagihanSiswa.tagihan',
                            'tagihanSiswa.potonganSiswa.potongan'
                        ])
                        ->get();

                    $listTagihan = $detailPembayaran->map(function ($detail) use ($bulanArray) {
                        $tagihanSiswa = $detail->tagihanSiswa;

                        // Get kategori info
                        $kategoriNama = 'N/A';
                        if ($tagihanSiswa && $tagihanSiswa->tagihanItem && $tagihanSiswa->tagihanItem->kategori) {
                            $kategoriNama = $tagihanSiswa->tagihanItem->kategori->nama_kategori;
                        }

                        // Calculate bulan text
                        $bulanKeOriginal = $tagihanSiswa ? $tagihanSiswa->bulan_ke : null;
                        $tahunMulai = $tagihanSiswa && $tagihanSiswa->tagihan ? $tagihanSiswa->tagihan->tahun_mulai : null;

                        // Calculate actual month and year based on bulan_ke
                        if ($bulanKeOriginal && $tahunMulai) {
                            $yearOffset = floor(($bulanKeOriginal - 1) / 12);
                            $monthIndex = ($bulanKeOriginal - 1) % 12;
                            $tahun = $tahunMulai + $yearOffset;
                            $bulanText = ($bulanArray[$monthIndex] ?? 'N/A') . ' ' . $tahun;
                        } else {
                            $tahun = null;
                            $bulanText = 'N/A';
                        }

                        // Get nominal tagihan
                        $nominalTagihan = $tagihanSiswa && $tagihanSiswa->tagihanItem ? (float)$tagihanSiswa->tagihanItem->nominal : 0;

                        // Get potongan
                        $potonganTotal = 0;
                        if ($tagihanSiswa && $tagihanSiswa->potonganSiswa) {
                            foreach ($tagihanSiswa->potonganSiswa as $potongan) {
                                $potonganTotal += (float)$potongan->nominal;
                            }
                        }

                        return [
                            'id' => $detail->id,
                            'tagihan_siswa_id' => $detail->tagihan_siswa_id,
                            'nama_tagihan' => $tagihanSiswa && $tagihanSiswa->tagihan ? $tagihanSiswa->tagihan->nama_tagihan : 'N/A',
                            'kategori' => $kategoriNama,
                            'periode' => $bulanText,
                            'tahun' => $tahun,
                            'nominal_tagihan' => $nominalTagihan,
                            'jumlah_potongan' => $potonganTotal,
                            'jumlah_tagihan' => $nominalTagihan - $potonganTotal,
                            'jumlah_bayar_detail' => (float)$detail->jumlah_bayar_detail,
                            'created_at' => $detail->created_at,
                        ];
                    })->toArray();
                }

                return [
                    'id' => $p->id,
                    'code_pembayaran' => $p->code_pembayaran,
                    'head_tagihan' => $p->head_tagihan,
                    'is_multi_payment' => $p->head_tagihan ? true : false,
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
                    'status_verifikasi' => $statusVerifikasi,
                    'file_bukti' => $p->file_bukti ? Storage::disk('public')->url($p->file_bukti) : null,
                    'keterangan' => $p->keterangan,
                    'catatan_verifikasi' => $catatanVerifikasi,
                    'potongan' => $potonganList,
                    'data_rekenings' => $dataRekenings,
                    'list_tagihan' => $listTagihan,
                    'created_by' => $p->user ? $p->user->name : null,
                    'approved_by' => $p->approvedBy ? $p->approvedBy->name : null,
                    'approved_at' => $p->approved_at ? $p->approved_at : null,
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Riwayat pembayaran tagihan berhasil diambil',
                'data' => $data,
                'meta' => [
                    'total' => $pembayaran->total(),
                    'per_page' => $pembayaran->perPage(),
                    'current_page' => $pembayaran->currentPage(),
                    'last_page' => $pembayaran->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API v1 - Riwayat Mutasi Tagihan
     * GET /api/v1/riwayat/tagihan-mutasi
     */
    public function riwayatMutasiTagihan(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);
            $siswaId = $request->get('siswa_id');
            $jenisMutasi = $request->get('jenis_mutasi'); // koreksi, diskon, denda, pembatalan
            $status = $request->get('status'); // pending, approved, rejected
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = Tagihansiswa_mutasi::with([
                'tagihanSiswa.siswa',
                'createdBy',
                'approvedBy'
            ])->orderBy('created_at', 'desc');

            // Filter by siswa
            if ($siswaId) {
                $query->whereHas('tagihanSiswa', function ($q) use ($siswaId) {
                    $q->where('siswa_id', $siswaId);
                });
            }

            // Filter by jenis mutasi
            if ($jenisMutasi) {
                $query->where('jenis_mutasi', $jenisMutasi);
            }

            // Filter by status
            if ($status) {
                $query->where('status_mutasi', $status);
            }

            // Filter by date range
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            $mutasi = $query->paginate($perPage, ['*'], 'page', $page);

            $data = $mutasi->map(function ($m) {
                return [
                    'id' => $m->id,
                    'code_mutasi' => $m->code_mutasi,
                    'tagihan_siswa_id' => $m->tagihan_siswa_id,
                    'siswa_id' => $m->tagihanSiswa && $m->tagihanSiswa->siswa ? $m->tagihanSiswa->siswa->id : null,
                    'siswa_nama' => $m->tagihanSiswa && $m->tagihanSiswa->siswa ? $m->tagihanSiswa->siswa->nama_lengkap : 'N/A',
                    'jenis_mutasi' => $m->jenis_mutasi,
                    'nominal_sebelum' => (float)$m->nominal_sebelum,
                    'nominal_perubahan' => (float)$m->nominal_perubahan,
                    'nominal_sesudah' => (float)$m->nominal_sesudah,
                    'keterangan' => $m->keterangan,
                    'alasan' => $m->alasan,
                    'status_mutasi' => $m->status_mutasi,
                    'created_by' => $m->createdBy ? $m->createdBy->name : null,
                    'approved_by' => $m->approvedBy ? $m->approvedBy->name : null,
                    'approved_at' => $m->approved_at ? $m->approved_at : null,
                    'created_at' => $m->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Riwayat mutasi tagihan berhasil diambil',
                'data' => $data,
                'meta' => [
                    'total' => $mutasi->total(),
                    'per_page' => $mutasi->perPage(),
                    'current_page' => $mutasi->currentPage(),
                    'last_page' => $mutasi->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat mutasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API v1 - Riwayat Mutasi Tagihan per Siswa
     * GET /api/v1/riwayat/tagihan-mutasi/{siswaId}
     */
    public function riwayatMutasiTagihanSiswa(Request $request, $siswaId)
    {
        $request->merge(['siswa_id' => $siswaId]);
        return $this->riwayatMutasiTagihan($request);
    }

    /**
     * API v1 - Audit Trail Lengkap untuk Transaksi
     * GET /api/v1/riwayat/audit-trail/{transactionId}
     */
    public function auditTrail($transactionId)
    {
        try {
            $transaksi = Keuangan_transaksi::findOrFail($transactionId);

            $logs = Keuangan_transaksi_logs::where('transaksi_id', $transactionId)
                ->with('pelaku')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Audit trail berhasil diambil',
                'transaction' => [
                    'id' => $transaksi->id,
                    'code' => $transaksi->code_pembayaran,
                    'jenis_transaksi' => $transaksi->jenis_transaksi,
                    'jumlah' => (float)$transaksi->jumlah,
                    'created_at' => $transaksi->created_at,
                ],
                'audit_logs' => $logs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'aksi' => $log->aksi,
                        'dilakukan_oleh' => $log->pelaku ? [
                            'id' => $log->pelaku->id,
                            'name' => $log->pelaku->name,
                            'email' => $log->pelaku->email,
                        ] : null,
                        'keterangan' => $log->keterangan,
                        'data_lama' => $log->data_lama ? json_decode($log->data_lama, true) : null,
                        'data_baru' => $log->data_baru ? json_decode($log->data_baru, true) : null,
                        'dilakukan_pada' => $log->created_at,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }
    }

    /**
     * API v1 - Dashboard Riwayat (Summary Statistics)
     * GET /api/v1/riwayat/dashboard
     */
    public function dashboard(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth());
            $endDate = $request->get('end_date', now()->endOfMonth());
            $siswaId = $request->get('siswa_id');

            // ===== MONTHLY STATISTICS (within date range) =====
            // Tabungan Statistics for the period
            $tabunganQuery = Keuangan_transaksi::whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan'])
                ->where('status_verifikasi','approved')
                ->where('status_approval','approved')
                ->whereBetween('created_at', [$startDate, $endDate]);

            if ($siswaId) {
                $tabunganQuery->where('penerima_id', $siswaId);
            }

            // Monthly deposits and withdrawals
            $totalSetorPerbulan = Keuangan_transaksi::whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan'])
                ->where('status_verifikasi','approved')
                ->where('status_approval','approved')
                ->where('jenis_transaksi', 'setoran_tabungan')
                ->whereBetween('created_at', [$startDate, $endDate]);

            if ($siswaId) {
                $totalSetorPerbulan->where('penerima_id', $siswaId);
            }
            $totalSetorPerbulan = $totalSetorPerbulan->sum('jumlah');

            $totalTarikPerbulan = Keuangan_transaksi::whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan'])
                ->where('status_verifikasi','approved')
                ->where('status_approval','approved')
                ->where('jenis_transaksi', 'penarikan_tabungan')
                ->whereBetween('created_at', [$startDate, $endDate]);

            if ($siswaId) {
                $totalTarikPerbulan->where('penerima_id', $siswaId);
            }
            $totalTarikPerbulan = $totalTarikPerbulan->sum('jumlah');

            $countSetorPerbulan = Keuangan_transaksi::whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan'])
                ->where('status_verifikasi','approved')
                ->where('status_approval','approved')
                ->where('jenis_transaksi', 'setoran_tabungan')
                ->whereBetween('created_at', [$startDate, $endDate]);

            if ($siswaId) {
                $countSetorPerbulan->where('penerima_id', $siswaId);
            }
            $countSetorPerbulan = $countSetorPerbulan->count();

            $countTarikPerbulan = Keuangan_transaksi::whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan'])
                ->where('status_verifikasi','approved')
                ->where('status_approval','approved')
                ->where('jenis_transaksi', 'penarikan_tabungan')
                ->whereBetween('created_at', [$startDate, $endDate]);

            if ($siswaId) {
                $countTarikPerbulan->where('penerima_id', $siswaId);
            }
            $countTarikPerbulan = $countTarikPerbulan->count();

            // ===== OVERALL STATISTICS (all time) =====
            // Total deposits all time
            $totalSetorKeseluruhan = Keuangan_transaksi::where('jenis_transaksi', 'setoran_tabungan')
                ->where('status_verifikasi','approved')
                ->where('status_approval','approved');

            if ($siswaId) {
                $totalSetorKeseluruhan->where('penerima_id', $siswaId);
            }
            $totalSetorKeseluruhan = $totalSetorKeseluruhan->sum('jumlah');

            // Total withdrawals all time
            $totalTarikKeseluruhan = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
                ->where('status_verifikasi','approved')
                ->where('status_approval','approved');

            if ($siswaId) {
                $totalTarikKeseluruhan->where('penerima_id', $siswaId);
            }
            $totalTarikKeseluruhan = $totalTarikKeseluruhan->sum('jumlah');

            // Count of deposits all time
            $jumlahSetorKeseluruhan = Keuangan_transaksi::where('jenis_transaksi', 'setoran_tabungan')->where('status_verifikasi','approved')
                ->where('status_approval','approved');

            if ($siswaId) {
                $jumlahSetorKeseluruhan->where('penerima_id', $siswaId);
            }
            $jumlahSetorKeseluruhan = $jumlahSetorKeseluruhan->count();

            // Count of withdrawals all time
            $jumlahTarikKeseluruhan = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
                ->where('status_verifikasi','approved')
                ->where('status_approval','approved');

            if ($siswaId) {
                $jumlahTarikKeseluruhan->where('penerima_id', $siswaId);
            }
            $jumlahTarikKeseluruhan = $jumlahTarikKeseluruhan->count();

            // Total count of both (deposits + withdrawals) all time
            $jumlahKeduanyaKeseluruhan = $jumlahSetorKeseluruhan + $jumlahTarikKeseluruhan;

            // Get current balance (saldo_saat_ini)
            $saldoSaatIni = 0;
            if ($siswaId) {
                $siswa = Siswa::where('id', $siswaId)->first();
                if ($siswa) {
                    $saldo = Saldo_keuangan::where('user_id', $siswa->user_id)->first();
                    if ($saldo) {
                        $saldoSaatIni = $saldo->saldo_akhir ?? 0;
                    }
                }
            }

            // Tagihan Statistics
            $tagihanQuery = Tagihansiswa::whereBetween('created_at', [$startDate, $endDate]);

            if ($siswaId) {
                $tagihanQuery->where('siswa_id', $siswaId);
            }

            $totalTagihan = $tagihanQuery->sum('sisa_nominal');

            // Status: 0 = Belum Bayar, 1 = Lunas, 2 = Cicilan
            $totalLunas = Tagihansiswa::where('status', '1')
                ->whereBetween('created_at', [$startDate, $endDate]);

            if ($siswaId) {
                $totalLunas->where('siswa_id', $siswaId);
            }
            $totalLunas = $totalLunas->count();

            $totalBelumLunas = Tagihansiswa::where('status', '!=', '1')
                ->whereBetween('created_at', [$startDate, $endDate]);

            if ($siswaId) {
                $totalBelumLunas->where('siswa_id', $siswaId);
            }
            $totalBelumLunas = $totalBelumLunas->count();

            // Pembayaran Statistics
            $pembayaranQuery = Pembayarantagihan::whereBetween('created_at', [$startDate, $endDate])
                ->where('status_approval','approved');

            if ($siswaId) {
                $pembayaranQuery->whereHas('tagihanSiswa', function ($q) use ($siswaId) {
                    $q->where('siswa_id', $siswaId);
                });
            }

            $totalPembayaran = $pembayaranQuery->sum('jumlah_bayar');
            $pembayaranApproved = $pembayaranQuery->where('status_approval', 'approved')->count();
            $pembayaranPending = $pembayaranQuery->where('status_approval', 'pending')->count();

            return response()->json([
                'success' => true,
                'message' => 'Dashboard riwayat berhasil diambil',
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
                'saldo_saat_ini' => $saldoSaatIni,
                'tabungan' => [
                    // Monthly statistics (within the period)
                    'perbulan' => [
                        'total_setoran' => (float)$totalSetorPerbulan,
                        'total_penarikan' => (float)$totalTarikPerbulan,
                        'jumlah_setoran' => $countSetorPerbulan,
                        'jumlah_penarikan' => $countTarikPerbulan,
                        'net_balance' => (float)($totalSetorPerbulan - $totalTarikPerbulan),
                    ],
                    // Overall statistics (all time)
                    'keseluruhan' => [
                        'total_setoran' => (float)$totalSetorKeseluruhan,
                        'total_penarikan' => (float)$totalTarikKeseluruhan,
                        'jumlah_setoran' => $jumlahSetorKeseluruhan,
                        'jumlah_penarikan' => $jumlahTarikKeseluruhan,
                        'jumlah_keduanya' => $jumlahKeduanyaKeseluruhan,
                        'net_balance' => (float)($totalSetorKeseluruhan - $totalTarikKeseluruhan),
                    ],
                ],
                'tagihan' => [
                    'total_tagihan' => (float)$totalTagihan,
                    'jumlah_lunas' => $totalLunas,
                    'jumlah_belum_lunas' => $totalBelumLunas,
                ],
                'pembayaran' => [
                    'total_pembayaran' => (float)$totalPembayaran,
                    'count_approved' => $pembayaranApproved,
                    'count_pending' => $pembayaranPending,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to format transaction
     */
    private function formatTransaction($trx)
    {
        $type = 'unknown';
        if (strpos($trx->jenis_transaksi, 'tabungan') !== false) {
            $type = 'Tabungan';
        } elseif ($trx->jenis_transaksi === 'pembayaran') {
            $type = 'Pembayaran';
        }

        return [
            'id' => $trx->id,
            'code' => $trx->code_pembayaran,
            'type' => $type,
            'jenis_transaksi' => $trx->jenis_transaksi,
            'jumlah' => (float)$trx->jumlah,
            'metode' => $trx->metode,
            'status' => $trx->status_verifikasi,
            'created_at' => $trx->created_at,
        ];
    }
}
