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

        // hitung total saldo dari saldo_keuangan
        $totalSaldo = Saldo_keuangan::where('status', 1) // kalau hanya saldo aktif
             ->when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->whereHas('user', function ($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            });
        })->sum('saldo_akhir');

        $jumlahTransaksi = Keuangan_transaksi::where('jenis_transaksi', 'setoran_tabungan')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

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
        $pembayaranTagihans = \App\Models\Pembayarantagihan::with([
            'tagihanSiswa.siswa.user',
            'tagihanSiswa.tagihan.unit',
            'tagihanSiswa.tagihan.kelas',
            'tagihanSiswa.tagihan.items.kategori'
        ])
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->whereHas('tagihanSiswa.tagihan', function ($q) use ($unitId) {
                    $q->where('unit_id', $unitId);
                });
            })
            ->latest() // ambil yang terbaru
            ->limit(15)
            ->get();

        $data = $pembayaranTagihans->map(function ($pembayaran) {
            $ts = $pembayaran->tagihanSiswa;
            $tagihan = $ts->tagihan;
            $itemTagihan = $tagihan->items->pluck('kategori.nama_kategori')->implode(', ');

            return [
                'nomor_induk'   => $ts->siswa->nisn ?? '-',
                'nama_lengkap'  => $ts->siswa->user->name ?? '-',
                'tagihan_unit'  => $tagihan->unit->nama_unit ?? '-',
                'tagihan_kelas' => $tagihan->kelas->nama_kelas ?? '-',
                'item_tagihan'  => $itemTagihan,
                'jml_dibayar'   => $pembayaran->jumlah_bayar,
                'status_approval' => $pembayaran->status_approval ?? 'pending',
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
        $allTagihans = Tagihansiswa::with([
            'siswa.pembayaranTagihan',
            'tagihan.items'
        ])
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->whereHas('tagihan', function ($q) use ($unitId) {
                    $q->where('unit_id', $unitId);
                });
            })
            ->get();

        $tagihanData = [
            'jumlah_data' => $allTagihans->count(),
            'nominal_tagihan' => $allTagihans->sum(function ($ts) {
                return $ts->tagihan->items->sum('nominal');
            }),
            'sudah_dibayar' => $allTagihans->sum(function ($ts) {
                return $ts->siswa->pembayaranTagihan
                    ->where('status_approval', 'approved')
                    ->sum('jumlah_bayar');
            }),
            'belum_dibayar' => $allTagihans->sum(function ($ts) {
                $nominal_tagihan = $ts->tagihan->items->sum('nominal');
                $sudah_bayar = $ts->siswa->pembayaranTagihan
                    ->where('status_approval', 'approved')
                    ->sum('jumlah_bayar');
                return max($nominal_tagihan - $sudah_bayar, 0);
            }),
        ];

        return view('dashboard', compact('totalPetugas', 'totalUnit', 'totalKelas', 'totalSiswa','totalSaldo','jumlahTransaksi','data','datas','tagihanData'));
    }
}
