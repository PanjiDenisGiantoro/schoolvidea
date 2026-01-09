<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Keuangan_transaksi;
use App\Models\Saldo_keuangan;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Tagihansiswa;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {

        // hitung total saldo dari saldo_keuangan - filter berdasarkan unit_id atau yayasan_id
        $saldoQuery = Saldo_keuangan::where('status', 1); // kalau hanya saldo aktif

        if (Auth::user()->unit_id) {
            $saldoQuery->whereHas('user', function ($q) {
                $q->where('unit_id', Auth::user()->unit_id);
            });
        } elseif (Auth::user()->yayasan_id) {
            $saldoQuery->whereHas('user.unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            });
        }

        $totalSaldo = $saldoQuery->sum('saldo_akhir');

        // Hitung jumlah transaksi - hanya yang sudah approved/verified
        $transaksiQuery = Keuangan_transaksi::where('jenis_transaksi', 'setoran_tabungan')
            ->where('status_verifikasi', 'approved')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);

        if (Auth::user()->unit_id) {
            $transaksiQuery->whereHasMorph('penerima', [Siswa::class], function ($q) {
                $q->where('unit_id', Auth::user()->unit_id);
            });
        } elseif (Auth::user()->yayasan_id) {
            $transaksiQuery->whereHasMorph('penerima', [Siswa::class], function ($q) {
                $q->whereHas('unit', function ($q2) {
                    $q2->where('yayasan_id', Auth::user()->yayasan_id);
                });
            });
        }

        $jumlahTransaksi = $transaksiQuery->count();

        $totalPetugas = User::with('roles')
            ->whereHas('roles', function ($query) {
            $query->where('name', '!=', 'siswa')
            ->where('name','!=','user');
        })
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id', $unitId);
            })
            ->count();
        if(Auth::user()->unit_id != null){
            $totalUnit = 1;
        }else{
            $totalUnit = Unit::count();
        }
        $totalKelas = Kelas::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('unit_id', $unitId);
        })->count();


        $totalSiswa = Siswa::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('unit_id', $unitId);
        })->count();

        // Ambil data pembayaran tagihan terbaru dengan status approval
        $pembayaranTagihansQuery = \App\Models\Pembayarantagihan::with([
            'tagihanSiswa.siswa.user',
            'tagihanSiswa.tagihan.unit',
            'tagihanSiswa.tagihan.kelas',
            'tagihanSiswa.tagihan.items.kategori'
        ]);

        if (Auth::user()->unit_id) {
            $pembayaranTagihansQuery->whereHas('tagihanSiswa.tagihan', function ($q) {
                $q->where('unit_id', Auth::user()->unit_id);
            });
        } elseif (Auth::user()->yayasan_id) {
            $pembayaranTagihansQuery->whereHas('tagihanSiswa.tagihan.unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            });
        }

        $pembayaranTagihans = \App\Models\Pembayarantagihan::where('status_approval', '!=', 'pending')->latest()->take(5)->get();


        $data = $pembayaranTagihans->map(function ($pembayaran) {
            $ts = $pembayaran->tagihanSiswa;
            $tagihan = $ts->tagihan;
            $itemTagihan = $tagihan->items
            ->where('status_approval', '!=', 'pending')
            ->pluck('kategori.nama_kategori')
            ->implode(', ');

            return [
                'nomor_induk'   => $ts->siswa->nisn ?? '-',
                'nama_lengkap'  => $ts->siswa->user->name ?? '-',
                'tagihan_unit'  => $tagihan->unit->nama_unit ?? '-',
                'tagihan_kelas' => $tagihan->kelas->nama_kelas ?? '-',
                'item_tagihan'  => $itemTagihan,
                'jml_dibayar'   => $pembayaran->jumlah_bayar,
                'status_approval' => $pembayaran->status_approval,
            ];
        });


        $tahun = now()->year;

        $datas = Tagihansiswa::with('tagihan.items')
            ->whereYear('created_at', $tahun)
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->whereHas('tagihan', function ($q) use ($unitId) {
                    $q->where('unit_id', $unitId);
                });
            })
            ->get()
            ->groupBy(function ($row) {
                return $row->created_at->format('n'); // bulan angka
            })
            ->map(function ($rows, $bulan) {
                $jmlTagihan = $rows->sum(function ($ts) {
                    return $ts->tagihan ? $ts->tagihan->items->sum('nominal') : 0;
                });

                $jmlDibayar = $rows->sum('sisa_nominal');
                $jmlTunggakan = $jmlTagihan - $jmlDibayar;

                return [
                    'bulan' => $bulan,
                    'jml_tagihan' => $jmlTagihan,
                    'jml_dibayar' => $jmlDibayar,
                    'jml_tunggakan' => $jmlTunggakan,
                ];
            })
            ->values();

        // Hitung ringkasan tagihan untuk dashboard card
        // Filter berdasarkan yayasan_id atau unit_id
        $allTagihansQuery = Tagihansiswa::with([
            'siswa.pembayaranTagihan',
            'tagihan.items'
        ]);

        if (Auth::user()->unit_id) {
            $allTagihansQuery->whereHas('tagihan', function ($q) {
                $q->where('unit_id', Auth::user()->unit_id);
            });
        } elseif (Auth::user()->yayasan_id) {
            $allTagihansQuery->whereHas('tagihan.unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            });
        }

        $allTagihans = $allTagihansQuery->get();

        $tagihanData = [
            'jumlah_data' => $allTagihans->count(),
            'nominal_tagihan' => $allTagihans->sum(function ($ts) {
                return $ts->tagihan->items->sum('nominal');
            }),
            'sudah_dibayar' => $allTagihans->sum(function ($ts) {
                return $ts->siswa->pembayaranTagihan
                    ->where('status_approval', 'approved')
                    ->where('status_verifikasi', 'approved')
                    ->sum('jumlah_bayar');
            }),
            'belum_dibayar' => $allTagihans->sum(function ($ts) {
                $nominal_tagihan = $ts->tagihan->items->sum('nominal');
                $sudah_bayar = $ts->siswa->pembayaranTagihan
                    ->where('status_approval', 'approved')
                    ->where('status_verifikasi', 'approved')
                    ->sum('jumlah_bayar');
                return max($nominal_tagihan - $sudah_bayar, 0);
            }),
        ];

        return view('dashboard', compact('totalPetugas', 'totalUnit', 'totalKelas', 'totalSiswa','totalSaldo','jumlahTransaksi','data','datas','tagihanData'));
    }
}