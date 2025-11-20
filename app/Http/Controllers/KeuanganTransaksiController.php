<?php

namespace App\Http\Controllers;

use App\Models\Keuangan_transaksi;
use App\Models\Keuangan_transaksi_logs;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mpdf\Mpdf;

class KeuanganTransaksiController extends Controller
{
    /**
     * Helper function to apply base filters (unit_id, yayasan_id, siswa_id)
     */
    private function applyBaseFilters($query, $request)
    {
        // Priority: yayasan_id > unit_id > super admin (all)
        if ($request->filled('unit_id') && $request->unit_id != '' && $request->unit_id != 'all') {
            // User explicitly selected a unit
            $query->whereHasMorph('penerima', [Siswa::class], function ($q) use ($request) {
                $q->where('unit_id', $request->unit_id);
            });
        } elseif (Auth::user()->unit_id) {
            // User has unit_id, filter by their unit
            $query->whereHasMorph('penerima', [Siswa::class], function ($q) {
                $q->where('unit_id', Auth::user()->unit_id);
            });
        } elseif (Auth::user()->yayasan_id) {
            // User has yayasan_id, filter by yayasan
            $query->whereHasMorph('penerima', [Siswa::class], function ($q) {
                $q->whereHas('unit', function($q2) {
                    $q2->where('yayasan_id', Auth::user()->yayasan_id);
                });
            });
        }
        // Else: super admin - show all

        return $query;
    }

    /**
     * Helper function to parse date from DD/MM/YYYY format to YYYY-MM-DD
     */
    private function parseDateFormat($date)
    {
        if (!$date) {
            return null;
        }

        try {
            // Check if date contains slashes (DD/MM/YYYY format)
            if (strpos($date, '/') !== false) {
                // Manual parsing: DD/MM/YYYY to YYYY-MM-DD
                $parts = explode('/', $date);
                if (count($parts) === 3 && is_numeric($parts[0]) && is_numeric($parts[1]) && is_numeric($parts[2])) {
                    $day = (int)$parts[0];
                    $month = (int)$parts[1];
                    $year = (int)$parts[2];
                    // Validate date
                    if ($day > 0 && $day <= 31 && $month > 0 && $month <= 12 && $year > 1900) {
                        return sprintf('%04d-%02d-%02d', $year, $month, $day);
                    }
                }
            } else {
                // If already in YYYY-MM-DD format, return as-is
                return $date;
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper function to apply common filters
     */
    private function applyCommonFilters($query, $request)
    {
        if ($request->filled('siswa_id')) {
            $query->where('penerima_id', $request->siswa_id)->where('penerima_tipe', Siswa::class);
        }
        if ($request->jenis_transaksi) {
            $query->where('jenis_transaksi', $request->jenis_transaksi);
        }
        if ($request->kode_pembayaran) {
            $query->where('code_pembayaran', 'like', '%' . $request->kode_pembayaran . '%');
        }
        if ($request->nama_siswa) {
            $query->whereHasMorph('penerima', [Siswa::class], function ($q) use ($request) {
                $q->whereHas('user', function($q2) use ($request) {
                    $q2->where('name', 'like', '%' . $request->nama_siswa . '%');
                })->orWhere('nisn', 'like', '%' . $request->nama_siswa . '%');
            });
        }
        if ($request->dari_tanggal) {
            $parsedDate = $this->parseDateFormat($request->dari_tanggal);
            $query->whereDate('tanggal_transaksi', '>=', $parsedDate);
        }
        if ($request->sampai_tanggal) {
            $parsedDate = $this->parseDateFormat($request->sampai_tanggal);
            $query->whereDate('tanggal_transaksi', '<=', $parsedDate);
        }

        return $query;
    }

    /**
     * List semua transaksi keuangan
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        // List transaksi dengan filtering
        $transaksis = Keuangan_transaksi::with([
                'penerima',
                'creator',
                'pembayaranTagihan.tagihanSiswa.tagihan.items.kategori'
            ]);

        $transaksis = $this->applyBaseFilters($transaksis, $request);
        $transaksis = $this->applyCommonFilters($transaksis, $request);

        $transaksis = $transaksis
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->except('page'));

        // Total Pemasukan
        $totalPemasukanQuery = Keuangan_transaksi::whereIn('jenis_transaksi', ['setoran_tabungan', 'pembayaran', 'tagihan']);
        $totalPemasukanQuery = $this->applyBaseFilters($totalPemasukanQuery, $request);
        $totalPemasukanQuery = $this->applyCommonFilters($totalPemasukanQuery, $request);
        $total_pemasukan = $totalPemasukanQuery->sum('jumlah');

        // Total Pengeluaran
        $totalPengeluaranQuery = Keuangan_transaksi::whereIn('jenis_transaksi', ['penarikan_tabungan']);
        $totalPengeluaranQuery = $this->applyBaseFilters($totalPengeluaranQuery, $request);
        $totalPengeluaranQuery = $this->applyCommonFilters($totalPengeluaranQuery, $request);
        $total_pengeluaran = $totalPengeluaranQuery->sum('jumlah');

        // Total Transaksi
        $totalTransaksiQuery = Keuangan_transaksi::query();
        $totalTransaksiQuery = $this->applyBaseFilters($totalTransaksiQuery, $request);
        $totalTransaksiQuery = $this->applyCommonFilters($totalTransaksiQuery, $request);
        $total_transaksi = $totalTransaksiQuery->count();

        // Summary calculations with proper filtering
        $summaryQuery = Keuangan_transaksi::query();
        $summaryQuery = $this->applyBaseFilters($summaryQuery, $request);
        $summaryQuery = $this->applyCommonFilters($summaryQuery, $request);
        $summaryTransaksis = $summaryQuery->get();

        // Calculate various metrics for summary cards
        $total_tunai = $summaryTransaksis->where('metode', 'CASH')->sum('jumlah');
        $total_non_tunai = $summaryTransaksis->where('metode', '!=', 'CASH')->sum('jumlah');
        $today = \Carbon\Carbon::today()->toDateString();
        $total_harian = $summaryTransaksis->filter(function($item) use ($today) {
            if (!$item->tanggal_transaksi) {
                return false;
            }
            $itemDate = is_string($item->tanggal_transaksi)
                ? \Carbon\Carbon::parse($item->tanggal_transaksi)->toDateString()
                : $item->tanggal_transaksi->toDateString();
            return $itemDate === $today;
        })->sum('jumlah');

        // Get units for filter options
        if (Auth::user()->yayasan_id) {
            $units = \App\Models\Unit::where('yayasan_id', Auth::user()->yayasan_id)->orderBy('nama_unit')->get();
        } elseif (Auth::user()->unit_id) {
            $units = \App\Models\Unit::where('id', Auth::user()->unit_id)->orderBy('nama_unit')->get();
        } else {
            $units = \App\Models\Unit::orderBy('nama_unit')->get();
        }
        $siswaIds = $transaksis->pluck('penerima_id')->unique()->toArray();

        $baseQuery = function ($query) use ($siswaIds, $request) {
            return $query->where('penerima_tipe', Siswa::class)
                ->whereIn('penerima_id', $siswaIds)
                ->when(Auth::user()->unit_id, function ($q) {
                    // Join with siswa table to filter by unit_id
                    $q->join('siswas', 'keuangan_transaksis.penerima_id', '=', 'siswas.id')
                        ->where('siswas.unit_id', Auth::user()->unit_id)
                        ->select('keuangan_transaksis.*');
                })
                ->when($request->filled('dari_tanggal'), function ($q) use ($request) {
                    $q->whereDate('tanggal_transaksi', '>=', $request->dari_tanggal);
                })
                ->when($request->filled('sampai_tanggal'), function ($q) use ($request) {
                    $q->whereDate('tanggal_transaksi', '<=', $request->sampai_tanggal);
                });
        };
        $total_pending = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
            ->where('status_approval', 'pending')
            ->tap($baseQuery)
            ->count();

        $summary = [
            'total_pemasukan' => $total_pemasukan,
            'total_pengeluaran' => $total_pengeluaran,
            'total_transaksi' => $total_transaksi,
            'total_tunai' => $total_tunai,
            'total_non_tunai' => $total_non_tunai,
            'total_harian' => $total_harian,
        ];

        return view('pages.keuangan.transaksi.index', compact(
            'transaksis',
            'summary',
            'units',
            'total_pending'
        ));
    }
    /**
     * Detail transaksi dengan history jurnal dan logs
     */
    public function show($id)
    {
        $transaksi = Keuangan_transaksi::with([
            'penerima',
            'creator',
            'jurnals.akun',
            'pembayaranTagihan.tagihanSiswa.tagihan.items.kategori'
        ])->findOrFail($id);

        // Ambil logs aktivitas
        $logs = Keuangan_transaksi_logs::with('pelaku')
            ->where('transaksi_id', $id)
            ->orderBy('dilakukan_pada', 'desc')
            ->get();

        return view('pages.keuangan.transaksi.show', compact('transaksi', 'logs'));
    }

    /**
     * Print laporan transaksi menggunakan mPDF
     */
    public function printLaporan(Request $request)
    {
        $transaksis = Keuangan_transaksi::with([
                'penerima',
                'creator',
                'pembayaranTagihan.tagihanSiswa.tagihan.items.kategori'
            ])
            ->when(Auth::user()->yayasan_id, function ($query) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) {
                    $q->whereHas('unit', function($q2) {
                        $q2->where('yayasan_id', Auth::user()->yayasan_id);
                    });
                });
            })
            ->when(!Auth::user()->yayasan_id && Auth::user()->unit_id, function ($query, $unit_id) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) use ($unit_id) {
                    $q->where('unit_id', $unit_id);
                });
            })
            ->when($request->dari_tanggal, function ($query, $dari) {
                $query->whereDate('tanggal_transaksi', '>=', $dari);
            })
            ->when($request->sampai_tanggal, function ($query, $sampai) {
                $query->whereDate('tanggal_transaksi', '<=', $sampai);
            })
            ->when($request->jenis_transaksi, function ($query, $jenis) {
                $query->where('jenis_transaksi', $jenis);
            })
            ->orderBy('tanggal_transaksi', 'desc')
            ->get();

        // Statistik
        $total_pemasukan = $transaksis->whereIn('jenis_transaksi', ['setoran_tabungan', 'pembayaran', 'tagihan'])->sum('jumlah');
        $total_pengeluaran = $transaksis->whereIn('jenis_transaksi', ['penarikan_tabungan'])->sum('jumlah');
        $total_transaksi = $transaksis->count();

        $dari_tanggal = $request->dari_tanggal ?? date('Y-m-01');
        $sampai_tanggal = $request->sampai_tanggal ?? date('Y-m-t');

        // Generate HTML dari view
        $html = view('pages.keuangan.transaksi.pdf_laporan', compact(
            'transaksis',
            'total_pemasukan',
            'total_pengeluaran',
            'total_transaksi',
            'dari_tanggal',
            'sampai_tanggal'
        ))->render();

        // Konfigurasi mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'L', // Landscape untuk tabel lebar
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 5,
            'margin_footer' => 5,
        ]);

        $mpdf->SetTitle('Laporan Transaksi Keuangan');
        $mpdf->SetAuthor(Auth::user()->name);
        $mpdf->WriteHTML($html);

        // Output PDF ke browser
        return $mpdf->Output('Laporan-Transaksi-' . date('Ymd') . '.pdf', 'I');
    }

    /**
     * Print detail transaksi menggunakan mPDF
     */
    public function printDetail($id)
    {
        $transaksi = Keuangan_transaksi::with([
            'penerima',
            'creator',
            'jurnals.akun',
            'pembayaranTagihan.tagihanSiswa.tagihan.items.kategori'
        ])->findOrFail($id);

        $logs = Keuangan_transaksi_logs::with('pelaku')
            ->where('transaksi_id', $id)
            ->orderBy('dilakukan_pada', 'desc')
            ->get();

        // Generate HTML dari view
        $html = view('pages.keuangan.transaksi.pdf_detail', compact('transaksi', 'logs'))->render();

        // Konfigurasi mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P', // Portrait untuk detail
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 5,
            'margin_footer' => 5,
        ]);

        $mpdf->SetTitle('Bukti Transaksi - ' . $transaksi->code_pembayaran);
        $mpdf->SetAuthor(Auth::user()->name);
        $mpdf->WriteHTML($html);

        // Output PDF ke browser
        return $mpdf->Output('Bukti-Transaksi-' . $transaksi->code_pembayaran . '.pdf', 'I');
    }

    /**
     * Approve transaksi keuangan
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'catatan_verifikasi' => 'nullable|string'
        ]);

        \DB::beginTransaction();

        try {
            // Load dengan relasi yang sesuai berdasarkan jenis transaksi
            if (in_array($request->jenis_transaksi ?? '', ['setoran_tabungan', 'penarikan_tabungan'])) {
                $transaksi = Keuangan_transaksi::with(['penerima'])->findOrFail($id);
            } else {
                $transaksi = Keuangan_transaksi::with(['penerima', 'pembayaranTagihan.tagihanSiswa'])->findOrFail($id);
            }

            // Jika jenis_transaksi belum diketahui dari request, load dari database
            if (!isset($request->jenis_transaksi)) {
                $transaksi = Keuangan_transaksi::findOrFail($id);
                if (in_array($transaksi->jenis_transaksi, ['setoran_tabungan', 'penarikan_tabungan'])) {
                    $transaksi->load(['penerima']);
                } else {
                    $transaksi->load(['penerima', 'pembayaranTagihan.tagihanSiswa']);
                }
            }

            // Cek apakah transaksi sudah diverifikasi
            if ($transaksi->status_verifikasi === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi sudah diapprove sebelumnya'
                ], 400);
            }

            // Khusus penarikan tabungan: HARUS melewati token verification dulu
            if ($transaksi->jenis_transaksi === 'penarikan_tabungan') {
                if (!in_array($transaksi->status_approval, ['verified', 'approved'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Penarikan tabungan harus diverifikasi dengan token terlebih dahulu. Status saat ini: ' . $transaksi->status_approval,
                        'data' => [
                            'status_approval' => $transaksi->status_approval,
                            'required_status' => 'verified or approved',
                            'note' => 'Gunakan endpoint /api/v1/tabungan/verify untuk verifikasi token terlebih dahulu'
                        ]
                    ], 400);
                }
            }

            // Update status verifikasi transaksi di keuangan_transaksis ONLY
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
                $saldoSiswa = \App\Models\Saldo_keuangan::where('user_id', $siswa->user->id)->first();
                if ($saldoSiswa) {
                    $saldoSiswa->increment('saldo_akhir', $transaksi->jumlah);
                }
            }

            // === TABUNGAN TARIK: Kurangi saldo saat approve (setelah token diverifikasi) ===
            // Flow: pending -> verified (via API token verify) -> approved (via web, saldo dikurangi disini)
            if ($transaksi->jenis_transaksi === 'penarikan_tabungan' && $transaksi->penerima_tipe === Siswa::class) {
                $siswa = Siswa::findOrFail($transaksi->penerima_id);
                $saldoSiswa = \App\Models\Saldo_keuangan::where('user_id', $siswa->user->id)->first();

                if ($saldoSiswa) {
                    // Validasi saldo cukup
                    if ($saldoSiswa->saldo_akhir < $transaksi->jumlah) {
                        throw new \Exception('Saldo tidak mencukupi. Saldo tersedia: Rp ' . number_format($saldoSiswa->saldo_akhir, 0, ',', '.') . ', Penarikan: Rp ' . number_format($transaksi->jumlah, 0, ',', '.'));
                    }

                    $saldoSebelum = $saldoSiswa->saldo_akhir;

                    // Kurangi saldo
                    $saldoSiswa->update([
                        'saldo_akhir' => $saldoSebelum - $transaksi->jumlah,
                        'last_updated' => now()
                    ]);

                    // Create journal entries untuk penarikan tabungan
                    // Debit: Tabungan Keluar (mengurangi kas)
                    \App\Models\Jurnals::create([
                        'transaksi_id' => $transaksi->id,
                        'akun_id' => 2, // Tabungan Keluar
                        'debit' => $transaksi->jumlah,
                        'kredit' => 0,
                        'keterangan' => 'Penarikan tabungan - ' . ($siswa->user->name ?? 'Siswa'),
                        'tanggal' => now(),
                    ]);

                    // Kredit: Kas (mengurangi kas sekolah)
                    \App\Models\Jurnals::create([
                        'transaksi_id' => $transaksi->id,
                        'akun_id' => 1, // Kas
                        'debit' => 0,
                        'kredit' => $transaksi->jumlah,
                        'keterangan' => 'Penarikan tabungan - ' . ($siswa->user->name ?? 'Siswa'),
                        'tanggal' => now(),
                    ]);
                }
            }

            // === PEMBAYARAN TAGIHAN: Update status tagihan siswa ===
            if (in_array($transaksi->jenis_transaksi, ['pembayaran', 'tagihan']) && $transaksi->pembayaranTagihan) {
                $pembayaran = $transaksi->pembayaranTagihan;
                $tagihanSiswa = $pembayaran->tagihanSiswa;

                if ($tagihanSiswa) {
                    // Update sisa nominal dan jumlah_dibayar jika belum dilakukan
                    $jumlahBayar = (int) $pembayaran->jumlah_bayar;
                    $sisaNominalBaru = $tagihanSiswa->sisa_nominal - $jumlahBayar;
                    $jumlahDibayarBaru = ($tagihanSiswa->jumlah_dibayar ?? 0) + $jumlahBayar;

                    // Tentukan status baru berdasarkan sisa nominal
                    $statusBaru = '0'; // Default: Belum Bayar
                    if ($sisaNominalBaru <= 0) {
                        $statusBaru = '1'; // Lunas
                        $sisaNominalBaru = 0;
                    } elseif ($jumlahDibayarBaru > 0 && $sisaNominalBaru > 0) {
                        $statusBaru = '2'; // Cicilan
                    }

                    // Update tagihan siswa dengan nilai yang benar (HANYA field yang ada di tabel)
                    $tagihanSiswa->update([
                        'status' => $statusBaru,
                        'sisa_nominal' => $sisaNominalBaru,
                        'tanggal_bayar' => now(),
                    ]);
                }
            }

            // Update keuangan_transaksi status untuk pembayaran tagihan
            if (in_array($transaksi->jenis_transaksi, ['pembayaran', 'tagihan']) && $transaksi->pembayaranTagihan) {
                $transaksi->update([
                    'status_verifikasi' => 'approved',
                    'status_approval' => 'approved'
                ]);

                // Create journal entries untuk pembayaran tagihan
                $pembayaran = $transaksi->pembayaranTagihan;
                if ($pembayaran && $pembayaran->tagihanSiswa) {
                    $tagihanSiswa = $pembayaran->tagihanSiswa;
                    $tagihan = $tagihanSiswa->tagihan;
                    $siswa = $tagihanSiswa->siswa;
                    $jumlahBayar = (int) $pembayaran->jumlah_bayar;

                    $keterangan = "Pembayaran {$tagihan->nama_tagihan} sebesar Rp " . number_format($jumlahBayar, 0, ',', '.');

                    // Debit: Kas (uang masuk dari siswa)
                    \App\Models\Jurnals::create([
                        'transaksi_id' => $transaksi->id,
                        'akun_id' => 1, // Kas
                        'debit' => $jumlahBayar,
                        'kredit' => 0,
                        'keterangan' => $keterangan . ' - ' . ($siswa->user->name ?? 'Siswa'),
                        'tanggal' => now(),

                    ]);

                    // Kredit: Tagihan (mengurangi hutang siswa)
                    \App\Models\Jurnals::create([
                        'transaksi_id' => $transaksi->id,
                        'akun_id' => 3, // Tagihan Masuk (receivable)
                        'kredit' => $jumlahBayar,
                        'debit' => 0,
                        'keterangan' => $keterangan . ' - ' . ($siswa->user->name ?? 'Siswa'),
                        'tanggal' => now(),
                    ]);
                }
            }
            // Log activity
            Keuangan_transaksi_logs::create([
                'transaksi_id' => $transaksi->id,
                'aksi' => 'approve',
                'data_lama' => json_encode(['status_verifikasi' => 'pending']),
                'data_baru' => json_encode(['status_verifikasi' => 'approved']),
                'dilakukan_oleh' => Auth::id(),
                'dilakukan_pada' => now(),
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diapprove'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject transaksi keuangan
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan_verifikasi' => 'required|string'
        ]);

        \DB::beginTransaction();

        try {
            // Load dengan relasi yang sesuai berdasarkan jenis transaksi
            if (in_array($request->jenis_transaksi ?? '', ['setoran_tabungan', 'penarikan_tabungan'])) {
                $transaksi = Keuangan_transaksi::with(['penerima'])->findOrFail($id);
            } else {
                $transaksi = Keuangan_transaksi::with(['penerima', 'pembayaranTagihan.tagihanSiswa'])->findOrFail($id);
            }

            // Jika jenis_transaksi belum diketahui dari request, load dari database
            if (!isset($request->jenis_transaksi)) {
                $transaksi = Keuangan_transaksi::findOrFail($id);
                if (in_array($transaksi->jenis_transaksi, ['setoran_tabungan', 'penarikan_tabungan'])) {
                    $transaksi->load(['penerima']);
                } else {
                    $transaksi->load(['penerima', 'pembayaranTagihan.tagihanSiswa']);
                }
            }

            // Cek apakah transaksi sudah diverifikasi
            if ($transaksi->status_verifikasi === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi sudah direject sebelumnya'
                ], 400);
            }

            // Update status verifikasi transaksi di keuangan_transaksis ONLY
            $transaksi->update([
                'status_verifikasi' => 'rejected',
                'status_approval' => 'rejected',
                'catatan_verifikasi' => $request->catatan_verifikasi,
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);

            // === TABUNGAN SETOR: Tidak perlu rollback saldo (belum ditambah) ===
            // Saldo belum ditambah karena status masih pending
            // Reject hanya mengubah status, tidak perlu update saldo

            // === TABUNGAN TARIK: Tidak perlu kembalikan saldo ===
            // Flow baru: Saldo BELUM dikurangi saat pending/verified, hanya dikurangi saat approve
            // Jadi saat reject (dari pending atau verified), tidak perlu kembalikan saldo
            // Saldo tetap utuh karena belum pernah dikurangi

            // === PEMBAYARAN TAGIHAN: Rollback pembayaran ===
            if (in_array($transaksi->jenis_transaksi, ['pembayaran', 'tagihan']) && $transaksi->pembayaranTagihan) {
                $pembayaran = $transaksi->pembayaranTagihan;
                $tagihanSiswa = $pembayaran->tagihanSiswa;

                if ($tagihanSiswa) {
                    // Kembalikan nominal yang sudah dibayar saat reject
                    $jumlahBayar = (int) $pembayaran->jumlah_bayar;
                    $sisaNominalBaru = $tagihanSiswa->sisa_nominal + $jumlahBayar;
                    $jumlahDibayarBaru = ($tagihanSiswa->jumlah_dibayar ?? 0) - $jumlahBayar;

                    // Tentukan status baru setelah reject
                    $statusBaru = '0'; // Default: Belum Bayar
                    if ($jumlahDibayarBaru > 0 && $sisaNominalBaru > 0) {
                        $statusBaru = '2'; // Cicilan jika masih ada pembayaran sebelumnya
                    }

                    // Update tagihan siswa dengan rollback nilai (HANYA field yang ada di tabel)
                    $tagihanSiswa->update([
                        'status' => $statusBaru,
                        'sisa_nominal' => $sisaNominalBaru,
                        'jumlah_dibayar' => max(0, $jumlahDibayarBaru)
                    ]);
                }
            }

            // Update keuangan_transaksi status untuk pembayaran tagihan saat reject
            if (in_array($transaksi->jenis_transaksi, ['pembayaran', 'tagihan']) && $transaksi->pembayaranTagihan) {
                $transaksi->update([
                    'status_verifikasi' => 'rejected',
                    'status_approval' => 'rejected'
                ]);
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

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil direject'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal reject transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel transaksi keuangan (batalkan)
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'alasan_pembatalan' => 'required|string|min:10'
        ]);

        \DB::beginTransaction();

        try {
            $transaksi = Keuangan_transaksi::findOrFail($id);

            // Cek apakah transaksi sudah dibatalkan sebelumnya
            if ($transaksi->status_verifikasi === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi sudah dibatalkan sebelumnya'
                ], 400);
            }

            // Cek status - hanya transaksi pending/rejected yang bisa dibatalkan
            if (!in_array($transaksi->status_verifikasi, ['pending', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya transaksi dengan status Pending atau Rejected yang bisa dibatalkan'
                ], 400);
            }

            // Update status transaksi menjadi cancelled
            $transaksi->update([
                'status_verifikasi' => 'cancelled',
                'status_approval' => 'cancelled',
                'catatan_verifikasi' => $request->alasan_pembatalan,
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);

            // Log activity
            Keuangan_transaksi_logs::create([
                'transaksi_id' => $transaksi->id,
                'aksi' => 'cancel',
                'data_lama' => json_encode(['status_verifikasi' => 'pending']),
                'data_baru' => json_encode(['status_verifikasi' => 'cancelled']),
                'dilakukan_oleh' => Auth::id(),
                'dilakukan_pada' => now(),
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibatalkan'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}
