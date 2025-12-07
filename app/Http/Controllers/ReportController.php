<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\Unit;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function tagihan(Request $request)
    {
        $units = Unit::all();
        $kelas = Kelas::all();

        // Get filter parameters
        $unit_id = $request->unit_id;
        $kelas_id = $request->kelas_id;
        $status = $request->status;
        $search = $request->search;
        $tahun_ajaran_id = $request->tahun_ajaran_id;
        $from = $request->from;
        $to = $request->to;

        // Get tahun ajaran for filter
        $tahun_ajarans = \App\Models\Tahun_ajaran::orderBy('tahun_ajaran', 'desc')->get();

        $query = Tagihan::with([
            'unit',
            'kelas',
            'items.kategori',
            'tagihanSiswa.siswa.user',
            'tagihanSiswa.siswa.kelas',
            'tagihanSiswa.tagihanItem.kategori',
            'tagihanSiswa.pembayarantagihan',
            'tagihanSiswa.potonganSiswa'
        ]);

        // Filter by auth user unit
        if (Auth::user()->unit_id) {
            $query->where('unit_id', Auth::user()->unit_id);
        }

        // Filter by unit
        if ($unit_id) {
            $query->where('unit_id', $unit_id);
        }

        // Filter by kelas
        if ($kelas_id) {
            $query->where('kelas_id', $kelas_id);
        }

        // Filter by tahun ajaran
        if ($tahun_ajaran_id) {
            $query->where('tahun_ajaran_id', $tahun_ajaran_id);
        }

        // Filter by search (nama siswa or NISN)
        if ($search) {
            $query->whereHas('tagihanSiswa.siswa', function($q) use ($search) {
                $q->where('nisn', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by date range (created_at)
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        $tagihans = $query->get();

        // Build detail data for each tagihan siswa
        $dataDetails = [];
        $totalTagihan = 0;
        $totalDibayar = 0;
        $totalTunggakan = 0;

        foreach ($tagihans as $tagihan) {
            foreach ($tagihan->tagihanSiswa as $ts) {
                $siswa = $ts->siswa;
                if (!$siswa) continue;

                // Calculate nominal tagihan
                $nominalTagihan = $ts->tagihanItem ? $ts->tagihanItem->nominal : 0;

                // Calculate potongan
                $potongan = $ts->potonganSiswa->sum('nominal');

                // Calculate jumlah tagihan after potongan
                $jumlahTagihan = $nominalTagihan - $potongan;

                // Calculate sudah dibayar
                $sudahDibayar = $ts->pembayarantagihan()
                    ->where('status_approval', 'approved')
                    ->sum('jumlah_bayar');

                // Calculate tunggakan
                $tunggakan = max($ts->sisa_nominal, 0);

                // Determine status
                $statusLunas = $ts->status == '1' ? 'Lunas' : ($ts->status == '2' ? 'Cicilan' : 'Belum Lunas');

                // Apply status filter
                if ($status === 'lunas' && $ts->status != '1') continue;
                if ($status === 'belum_lunas' && $ts->status == '1') continue;

                $dataDetails[] = [
                    'tagihan' => $tagihan,
                    'tagihan_siswa' => $ts,
                    'siswa' => $siswa,
                    'nominal_tagihan' => $nominalTagihan,
                    'potongan' => $potongan,
                    'jumlah_tagihan' => $jumlahTagihan,
                    'sudah_dibayar' => $sudahDibayar,
                    'tunggakan' => $tunggakan,
                    'status' => $statusLunas,
                    'status_code' => $ts->status
                ];

                $totalTagihan += $jumlahTagihan;
                $totalDibayar += $sudahDibayar;
                $totalTunggakan += $tunggakan;
            }
        }

        $summary = [
            'jumlah_data' => count($dataDetails),
            'nominal_tagihan' => $totalTagihan,
            'sudah_dibayar' => $totalDibayar,
            'belum_dibayar' => $totalTunggakan,
        ];

        // Handle export
        if ($request->has('export')) {
            return $this->exportTagihan($request->export, $dataDetails, $summary, $from, $to);
        }

        return view('pages.report.tagihan.tagihan', compact(
            'dataDetails',
            'summary',
            'units',
            'kelas',
            'tahun_ajarans',
            'unit_id',
            'kelas_id',
            'status',
            'search',
            'tahun_ajaran_id',
            'from',
            'to'
        ));
    }

    private function exportTagihan($type, $dataDetails, $summary, $from, $to)
    {
        if ($type === 'excel') {
            return \Excel::download(
                new \App\Exports\TagihanReportExport($dataDetails, $summary, $from, $to),
                'laporan-tagihan-' . date('Y-m-d') . '.xlsx'
            );
        } elseif ($type === 'pdf') {
            $pdf = \PDF::loadView('pages.report.tagihan.tagihan-pdf', compact('dataDetails', 'summary', 'from', 'to'));
            return $pdf->download('laporan-tagihan-' . date('Y-m-d') . '.pdf');
        }
    }
}
