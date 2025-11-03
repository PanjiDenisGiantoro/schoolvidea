<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Jurnals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index()
    {
        $accounts = Akun::where('status', '1')->orderBy('kode_akun')->get();

        // Get transactions with related data
        $transactions = Jurnals::with([
            'akun',
            'siswa.kelas.unit',
            'tagihan' // If there's a relationship with tagihan
        ])
        ->latest('tanggal')
        ->paginate(20);

        // Get list of payment methods
        $paymentMethods = [
            'Tunai' => 'Tunai',
            'Transfer Bank' => 'Transfer Bank',
            'Kartu Kredit' => 'Kartu Kredit',
            'E-Wallet' => 'E-Wallet',
        ];

        // Get list of transaction types
        $transactionTypes = [
            'Pembayaran SPP' => 'Pembayaran SPP',
            'Pembayaran Uang Pangkal' => 'Pembayaran Uang Pangkal',
            'Pembayaran Uang Gedung' => 'Pembayaran Uang Gedung',
            'Pembayaran Lainnya' => 'Pembayaran Lainnya',
        ];

        return view('pages.transaksi.transaksi', compact(
            'accounts',
            'transactions',
            'paymentMethods',
            'transactionTypes'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'akun_id' => 'required|exists:akun,id',
            'keterangan' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Create journal entry
            $jurnal = new Jurnals();
            $jurnal->tanggal = $request->tanggal;
            $jurnal->akun_id = $request->akun_id;
            $jurnal->keterangan = $request->keterangan;

            // Determine if this is a debit or credit transaction
            $akun = Akun::find($request->akun_id);
            if (in_array($akun->kategori_akun, ['Aktiva', 'Beban'])) {
                $jurnal->debit = $request->jumlah;
                $jurnal->kredit = 0;
            } else {
                $jurnal->debit = 0;
                $jurnal->kredit = $request->jumlah;
            }

            $jurnal->save();

            // Update account balance
            $this->updateAccountBalance($akun);

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $jurnal = Jurnals::findOrFail($id);
            $akun = $jurnal->akun;

            $jurnal->delete();

            // Update account balance
            $this->updateAccountBalance($akun);

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function updateAccountBalance($akun)
    {
        // Calculate new balance based on all journal entries for this account
        $totalDebit = Jurnals::where('akun_id', $akun->id)->sum('debit');
        $totalKredit = Jurnals::where('akun_id', $akun->id)->sum('kredit');

        // Update account balance based on account type
        if (in_array($akun->kategori_akun, ['Aktiva', 'Beban'])) {
            $balance = $totalDebit - $totalKredit;
        } else {
            $balance = $totalKredit - $totalDebit;
        }

        $akun->saldo = $balance;
        $akun->save();
    }
}
