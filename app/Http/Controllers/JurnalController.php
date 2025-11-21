<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Jurnals;
use Illuminate\Http\Request;

class JurnalController extends Controller
{
    public function aruskas()
    {
        $headers = [
            "Unit",
            "Tanggal Transaksi",
            "Akun",
            "Debit",
            "Kredit",
            "Jumlah",
            "Created By"
        ];
        $akuns = \App\Models\Akun::with(['children.jurnals.transaksi', 'jurnals.transaksi'])
            ->whereNull('parent_id')
            ->get();

        return view('pages.report.arus-kas.arus-kas', compact('akuns', 'headers'));

    }

    public function jurnal()
    {
        $jurnals = Jurnals::with('akun')
            ->where('unit_id', auth()->user()->unit_id)
            ->orderBy('tanggal', 'asc')->get();
    $headers = [
        "Tanggal",
        "Akun",
        "Keterangan",
        "Debit",
        "Kredit"
    ];

        return view('pages.report.jurnal.jurnal', compact('jurnals', 'headers'));
    }
    public function buku_besar()
    {
        // bisa filter periode
        $from = $request->from ?? date('Y-m-01');
        $to   = $request->to ?? date('Y-m-t');

        // ambil akun
        $akuns = Akun::with(['jurnals' => function($q) use ($from, $to) {
            $q->whereBetween('tanggal', [$from, $to])->orderBy('tanggal', 'asc');
        }])->get();

        return view('pages.report.buku_besar.buku_besar', compact('akuns', 'from', 'to'));

    }
    public function neraca_saldo()
    {
        $from = $request->from ?? date('Y-m-01');
        $to   = $request->to ?? date('Y-m-t');

        // ambil saldo per akun
        $akuns = Akun::with(['jurnals' => function($q) use ($from, $to) {
            $q->whereBetween('tanggal', [$from, $to]);
        }])->get();

        $data = [];
        $totalDebit = 0;
        $totalKredit = 0;

        foreach ($akuns as $akun) {
            $debit = $akun->jurnals->sum('debit');
            $kredit = $akun->jurnals->sum('kredit');
            $saldo = $debit - $kredit;

            $row = [
                'kode'   => $akun->kode_akun,
                'nama'   => $akun->nama_akun,
                'debit'  => $saldo > 0 ? $saldo : 0,
                'kredit' => $saldo < 0 ? abs($saldo) : 0,
            ];

            $totalDebit  += $row['debit'];
            $totalKredit += $row['kredit'];

            $data[] = $row;
        }

        return view('pages.report.neraca_saldo.neraca_saldo', compact('data', 'from', 'to', 'totalDebit', 'totalKredit'));


    }
    public function neraca(Request $request)
    {
        $to = $request->to ?? date('Y-m-t');

        // Ambil akun + jurnals sesuai batas tanggal
        $akuns = Akun::with(['jurnals' => function($q) use ($to) {
            $q->where('tanggal', '<=', $to);
        }])->get();

        $data = [
            'aset' => [],
            'liabilitas' => [],
            'ekuitas' => [],
        ];

        $total_aset = $total_liabilitas = $total_ekuitas = 0;

        foreach ($akuns as $akun) {
            $debit  = $akun->jurnals->sum('debit');
            $kredit = $akun->jurnals->sum('kredit');

            // default saldo
            $saldo = 0;

            switch ($akun->tipe) {
                case 'ASET':
                    $saldo = $debit - $kredit; // normal saldo di debit
                    $data['aset'][] = ['nama' => $akun->nama_akun, 'saldo' => $saldo];
                    $total_aset += $saldo;
                    break;

                case 'LIABILITAS':
                    $saldo = $kredit - $debit; // normal saldo di kredit
                    $data['liabilitas'][] = ['nama' => $akun->nama_akun, 'saldo' => $saldo];
                    $total_liabilitas += $saldo;
                    break;

                case 'EKUITAS':
                    $saldo = $kredit - $debit; // normal saldo di kredit
                    $data['ekuitas'][] = ['nama' => $akun->nama_akun, 'saldo' => $saldo];
                    $total_ekuitas += $saldo;
                    break;

                // PENDAPATAN & BEBAN biasanya tidak masuk neraca, dipakai di Laba Rugi
            }
        }

        $total_passiva = $total_liabilitas + $total_ekuitas;

        return view('pages.report.neraca.neraca', compact(
            'data', 'to',
            'total_aset', 'total_liabilitas', 'total_ekuitas', 'total_passiva'
        ));
    }
    public function labarugi(Request $request)
    {
        $from = $request->from ?? date('Y-m-01');
        $to   = $request->to ?? date('Y-m-t');

        // ambil akun + jurnals dalam periode
        $akuns = Akun::with(['jurnals' => function($q) use ($from, $to) {
            $q->whereBetween('tanggal', [$from, $to]);
        }])->get();

        $pendapatan = [];
        $beban = [];
        $total_pendapatan = 0;
        $total_beban = 0;

        foreach ($akuns as $akun) {
            $debit  = $akun->jurnals->sum('debit');
            $kredit = $akun->jurnals->sum('kredit');
            $saldo = 0;

            switch ($akun->tipe) {
                case 'PENDAPATAN':
                    // pendapatan normal saldo di kredit
                    $saldo = $kredit - $debit;
                    $pendapatan[] = ['nama' => $akun->nama_akun, 'saldo' => $saldo];
                    $total_pendapatan += $saldo;
                    break;

                case 'BEBAN':
                    // beban normal saldo di debit
                    $saldo = $debit - $kredit;
                    $beban[] = ['nama' => $akun->nama_akun, 'saldo' => $saldo];
                    $total_beban += $saldo;
                    break;
            }
        }

        $laba_bersih = $total_pendapatan - $total_beban;

        return view('pages.report.labarugi.labarugi', compact(
            'from', 'to', 'pendapatan', 'beban',
            'total_pendapatan', 'total_beban', 'laba_bersih'
        ));
    }
}
