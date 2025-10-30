<?php

namespace App\Http\Controllers;

use App\Models\Keuangan_transaksi;
use App\Models\Keuangan_transaksi_logs;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeuanganTransaksiController extends Controller
{
    /**
     * List semua transaksi keuangan
     */
    public function index()
    {
        $transaksis = Keuangan_transaksi::with(['penerima', 'creator'])
            ->when(Auth::user()->unit_id, function ($query, $unit_id) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) use ($unit_id) {
                    $q->where('unit_id', $unit_id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
        $total_pemasukan = Keuangan_transaksi::whereIn('jenis_transaksi', ['setoran_tabungan', 'pembayaran'])
            ->when(Auth::user()->unit_id, function ($query, $unit_id) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) use ($unit_id) {
                    $q->where('unit_id', $unit_id);
                });
            })
            ->sum('jumlah');

        $total_pengeluaran = Keuangan_transaksi::whereIn('jenis_transaksi', ['penarikan_tabungan'])
            ->when(Auth::user()->unit_id, function ($query, $unit_id) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) use ($unit_id) {
                    $q->where('unit_id', $unit_id);
                });
            })
            ->sum('jumlah');

        $total_transaksi = Keuangan_transaksi::when(Auth::user()->unit_id, function ($query, $unit_id) {
                $query->whereHasMorph('penerima', [Siswa::class], function ($q) use ($unit_id) {
                    $q->where('unit_id', $unit_id);
                });
            })
            ->count();

        return view('pages.keuangan.transaksi.index', compact(
            'transaksis',
            'total_pemasukan',
            'total_pengeluaran',
            'total_transaksi'
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
            'jurnals.akun'
        ])->findOrFail($id);

        // Ambil logs aktivitas
        $logs = Keuangan_transaksi_logs::with('pelaku')
            ->where('transaksi_id', $id)
            ->orderBy('dilakukan_pada', 'desc')
            ->get();

        return view('pages.keuangan.transaksi.show', compact('transaksi', 'logs'));
    }
}
