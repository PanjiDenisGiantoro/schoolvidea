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
     * List semua transaksi keuangan
     */
    public function index(Request $request)
    {
        $transaksis = Keuangan_transaksi::with([
                'penerima',
                'creator',
                'pembayaranTagihan.tagihanSiswa.tagihan.items.kategori'
            ])
            // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
            ->when($request->filled('unit_id') && $request->unit_id != '' && $request->unit_id != 'all', function ($query) use ($request) {
                // Jika ada unit_id yang dipilih (bukan "semua unit")
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) use ($request) {
                    $q->where('unit_id', $request->unit_id);
                });
            })
            ->when((!$request->filled('unit_id') || $request->unit_id == '' || $request->unit_id == 'all') && Auth::user()->unit_id, function ($query) {
                // Jika tidak memilih unit spesifik DAN user punya unit_id, filter by unit_id user
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) {
                    $q->where('unit_id', Auth::user()->unit_id);
                });
            })
            ->when((!$request->filled('unit_id') || $request->unit_id == '' || $request->unit_id == 'all') && Auth::user()->yayasan_id && !Auth::user()->unit_id, function ($query) {
                // Jika tidak memilih unit spesifik DAN user punya yayasan_id (tapi tidak punya unit_id), filter by yayasan_id
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) {
                    $q->whereHas('unit', function($q2) {
                        $q2->where('yayasan_id', Auth::user()->yayasan_id);
                    });
                });
            })
            ->when($request->jenis_transaksi, function ($query, $jenis) {
                $query->where('jenis_transaksi', $jenis);
            })

            ->when($request->kode_pembayaran, function ($query, $kode) {
                $query->where('code_pembayaran', 'like', '%' . $kode . '%');
            })
            ->when($request->dari_tanggal, function ($query, $dari) {
                $query->whereDate('tanggal_transaksi', '>=', $dari);
            })
            ->when($request->sampai_tanggal, function ($query, $sampai) {
                $query->whereDate('tanggal_transaksi', '<=', $sampai);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $total_pemasukan = Keuangan_transaksi::whereIn('jenis_transaksi', ['setoran_tabungan', 'pembayaran', 'tagihan'])
            ->when($request->filled('unit_id') && $request->unit_id != '' && $request->unit_id != 'all', function ($query) use ($request) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) use ($request) {
                    $q->where('unit_id', $request->unit_id);
                });
            })
            ->when((!$request->filled('unit_id') || $request->unit_id == '' || $request->unit_id == 'all') && Auth::user()->unit_id, function ($query) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) {
                    $q->where('unit_id', Auth::user()->unit_id);
                });
            })
            ->when((!$request->filled('unit_id') || $request->unit_id == '' || $request->unit_id == 'all') && Auth::user()->yayasan_id && !Auth::user()->unit_id, function ($query) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) {
                    $q->whereHas('unit', function($q2) {
                        $q2->where('yayasan_id', Auth::user()->yayasan_id);
                    });
                });
            })
            ->when($request->jenis_transaksi, function ($query, $jenis) {
                $query->where('jenis_transaksi', $jenis);
            })
            ->when($request->kode_pembayaran, function ($query, $kode) {
                $query->where('code_pembayaran', 'like', '%' . $kode . '%');
            })
            ->when($request->dari_tanggal, function ($query, $dari) {
                $query->whereDate('tanggal_transaksi', '>=', $dari);
            })
            ->when($request->sampai_tanggal, function ($query, $sampai) {
                $query->whereDate('tanggal_transaksi', '<=', $sampai);
            })
            ->sum('jumlah');

        $total_pengeluaran = Keuangan_transaksi::whereIn('jenis_transaksi', ['penarikan_tabungan'])
            ->when($request->filled('unit_id') && $request->unit_id != '' && $request->unit_id != 'all', function ($query) use ($request) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) use ($request) {
                    $q->where('unit_id', $request->unit_id);
                });
            })
            ->when((!$request->filled('unit_id') || $request->unit_id == '' || $request->unit_id == 'all') && Auth::user()->unit_id, function ($query) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) {
                    $q->where('unit_id', Auth::user()->unit_id);
                });
            })
            ->when((!$request->filled('unit_id') || $request->unit_id == '' || $request->unit_id == 'all') && Auth::user()->yayasan_id && !Auth::user()->unit_id, function ($query) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) {
                    $q->whereHas('unit', function($q2) {
                        $q2->where('yayasan_id', Auth::user()->yayasan_id);
                    });
                });
            })
            ->when($request->jenis_transaksi, function ($query, $jenis) {
                $query->where('jenis_transaksi', $jenis);
            })
            ->when($request->kode_pembayaran, function ($query, $kode) {
                $query->where('code_pembayaran', 'like', '%' . $kode . '%');
            })
            ->when($request->dari_tanggal, function ($query, $dari) {
                $query->whereDate('tanggal_transaksi', '>=', $dari);
            })
            ->when($request->sampai_tanggal, function ($query, $sampai) {
                $query->whereDate('tanggal_transaksi', '<=', $sampai);
            })
            ->sum('jumlah');

        $total_transaksi = Keuangan_transaksi::when($request->filled('unit_id') && $request->unit_id != '' && $request->unit_id != 'all', function ($query) use ($request) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) use ($request) {
                    $q->where('unit_id', $request->unit_id);
                });
            })
            ->when((!$request->filled('unit_id') || $request->unit_id == '' || $request->unit_id == 'all') && Auth::user()->unit_id, function ($query) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) {
                    $q->where('unit_id', Auth::user()->unit_id);
                });
            })
            ->when((!$request->filled('unit_id') || $request->unit_id == '' || $request->unit_id == 'all') && Auth::user()->yayasan_id && !Auth::user()->unit_id, function ($query) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) {
                    $q->whereHas('unit', function($q2) {
                        $q2->where('yayasan_id', Auth::user()->yayasan_id);
                    });
                });
            })
            ->when($request->jenis_transaksi, function ($query, $jenis) {
                $query->where('jenis_transaksi', $jenis);
            })
            ->when($request->kode_pembayaran, function ($query, $kode) {
                $query->where('code_pembayaran', 'like', '%' . $kode . '%');
            })
            ->when($request->dari_tanggal, function ($query, $dari) {
                $query->whereDate('tanggal_transaksi', '>=', $dari);
            })
            ->when($request->sampai_tanggal, function ($query, $sampai) {
                $query->whereDate('tanggal_transaksi', '<=', $sampai);
            })
            ->count();

        // Get units for filter options
        if (Auth::user()->yayasan_id) {
            $units = \App\Models\Unit::where('yayasan_id', Auth::user()->yayasan_id)->orderBy('nama_unit')->get();
        } elseif (Auth::user()->unit_id) {
            $units = \App\Models\Unit::where('id', Auth::user()->unit_id)->orderBy('nama_unit')->get();
        } else {
            $units = \App\Models\Unit::orderBy('nama_unit')->get();
        }

        return view('pages.keuangan.transaksi.index', compact(
            'transaksis',
            'total_pemasukan',
            'total_pengeluaran',
            'total_transaksi',
            'units'
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
}
