<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Keuangan_transaksi;
use App\Models\Saldo_keuangan;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // hitung total saldo dari saldo_keuangan
        $totalSaldo = Saldo_keuangan::where('status', 1) // kalau hanya saldo aktif
            ->sum('saldo_akhir');

        $jumlahTransaksi = Keuangan_transaksi::where('jenis_transaksi', 'setoran_tabungan')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalPetugas = User::with('roles')->whereHas('roles', function ($query) {
            $query->where('name', '!=', 'siswa');
        })->count();
        $totalUnit = Unit::count();
        $totalKelas = Kelas::count();
        $totalSiswa = Siswa::count();
        $tagihans = Tagihan::with([
            'unit',
            'kelas',
            'items.kategori',
            'tagihanSiswa.siswa.user',
            'tagihanSiswa.pembayaranTagihan'
        ])
            ->latest() // ambil yang terbaru
            ->limit(10)
            ->get();

        $data = $tagihans->map(function ($tagihan) {
            $itemTagihan   = $tagihan->items->pluck('kategori.nama_kategori')->implode(', ');
            $nominalTagihan = $tagihan->items->sum('nominal');

            return $tagihan->tagihanSiswa->map(function ($ts) use ($tagihan, $itemTagihan, $nominalTagihan) {
                $jumlahDibayar = $ts->pembayaranTagihan->sum('jumlah_bayar');
                $tunggakan     = $nominalTagihan - $jumlahDibayar;

                return [
                    'nomor_induk'   => $ts->siswa->nisn ?? '-',
                    'nama_lengkap'  => $ts->siswa->user->name ?? '-',
                    'tagihan_unit'  => $tagihan->unit->nama_unit ?? '-',
                    'tagihan_kelas' => $tagihan->kelas->nama_kelas ?? '-',
                    'item_tagihan'  => $itemTagihan,
                    'type_tagihan'  => $tagihan->jenis_tagihan ?? '-',
                    'jml_tagihan'   => $nominalTagihan,
                    'jml_dibayar'   => $jumlahDibayar,
                    'jml_tunggakan' => $tunggakan,
                    'status'        => $tunggakan <= 0 ? 'Lunas' : 'Belum Lunas',
                ];
            });
        })->flatten(1)
            ->take(10); // ambil hanya 10 data pertama


        return view('dashboard', compact('totalPetugas', 'totalUnit', 'totalKelas', 'totalSiswa','totalSaldo','jumlahTransaksi','data'));
    }
}
