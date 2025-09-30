<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\Unit;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function tagihan()
    {
        $units = Unit::all();
        $kelas = Kelas::all();

        $tagihans = Tagihan::with([
            'unit',
            'kelas',
            'items.kategori',
            'tagihanSiswa.siswa.user',
            'tagihanSiswa.siswa.pembayaranTagihan'
        ])
        ->when(Auth::user()->unit_id, function($query, $unit_id) {
            $query->where('unit_id', $unit_id);
        })
        ->when(request('unit_id'), function($query) {
            $query->where('unit_id', request('unit_id'));
        })
        ->when(request('kelas_id'), function($query) {
            $query->where('kelas_id', request('kelas_id'));
        })
        ->when(request('status') === 'lunas', function($query) {
            $query->whereHas('tagihanSiswa.siswa.pembayaranTagihan', function($q) {
                $q->selectRaw('sum(jumlah_bayar) as total_bayar')
                  ->havingRaw('total_bayar >= tagihan_siswas.jumlah_tagihan');
            });
        })
        ->when(request('status') === 'belum_lunas', function($query) {
            $query->whereDoesntHave('tagihanSiswa.siswa.pembayaranTagihan', function($q) {
                $q->selectRaw('sum(jumlah_bayar) as total_bayar')
                  ->havingRaw('total_bayar >= tagihan_siswas.jumlah_tagihan');
            });
        })
        ->get();

        $summary = [
            'jumlah_data' => $tagihans->count(),
            'nominal_tagihan' => $tagihans->sum(function($t) {
                $total = $t->items->sum('nominal') * ($t->periode ?? 1);
                return $total * $t->tagihanSiswa->count();
            }),
            'sudah_dibayar' => $tagihans->sum(function($t) {
                return $t->tagihanSiswa->sum(function($ts) {
                    return $ts->siswa->pembayaranTagihan->sum('jumlah_bayar');
                });
            }),
            'belum_dibayar' => $tagihans->sum(function($t) {
                $total_tagihan = $t->items->sum('nominal') * ($t->periode ?? 1);
                return $t->tagihanSiswa->sum(function($ts) use ($total_tagihan) {
                    $sudah_bayar = $ts->siswa->pembayaranTagihan->sum('jumlah_bayar');
                    return max($total_tagihan - $sudah_bayar, 0);
                });
            }),
        ];

        return view('pages.report.tagihan.tagihan', compact('tagihans', 'summary', 'units', 'kelas'));
    }
}
