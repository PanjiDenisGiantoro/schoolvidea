<?php

namespace App\Http\Controllers;

use App\Models\MerchantTransaction;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MerchantTransactionController extends Controller
{
    public function index()
    {
        $units = Unit::all();
        $query = MerchantTransaction::with('merchant.unit');
        if (auth()->user()->unit_id) {
            $query->whereHas('merchant', function ($q) {
                $q->where('unit_id', auth()->user()->unit_id);
            });
        }

        if (request('tanggal')) {
            $query->whereDate('created_at', request('tanggal'));
        }

        $transaction = $query->orderBy('created_at', 'desc')->get();
        $trxCount = $transaction->count();
        $trxCredit = $transaction->where('type', 'credit')->count();
        $trxDebit = $transaction->where('type', 'debit')->count();
        $todayTotal = $transaction->sum('amount');

        return view('pages.ekantin.transaction.index', compact('units', 'transaction', 'trxCount', 'trxCredit', 'trxDebit', 'todayTotal'));
    }

    public function datatable(Request $request)
    {
        $query = MerchantTransaction::with('merchant');

        // filter unit
        if ($request->unit_id) {
            $query->whereHas('merchant', function ($q) use ($request) {
                $q->where('unit_id', $request->unit_id);
            });
        }

        // filter tipe
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // filter tanggal
        if ($request->tanggal) {
            $query->whereDate('created_at', $request->tanggal);
        }

        // search
        if (! empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('merchant', function ($m) use ($search) {
                    $m->where('kode_merchant', 'LIKE', "%{$search}%")
                        ->orWhere('nama_merchant', 'LIKE', "%{$search}%");
                })
                    ->orWhere('type', 'LIKE', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();
        $recordsTotal = MerchantTransaction::count();

        $transactions = $query
            ->orderBy('id', 'desc')
            ->offset($request->start)
            ->limit($request->length)
            ->get();

        $data = [];
        $no = $request->start + 1;

        foreach ($transactions as $trx) {
            $data[] = [
                'no' => $no++,
                'kode_merchant' => $trx->merchant->kode_merchant ?? '-',
                'nama_merchant' => $trx->merchant->nama_merchant ?? '-',
                'amount' => number_format($trx->amount, 0, ',', '.'),
                'type' => ucfirst($trx->type),
                'balance_after' => 'Rp ' . number_format($trx->balance_after ?? 0, 0, ',', '.'),
                'waktu_registrasi' => Carbon::parse($trx->created_at)->format('d-m-Y H:i'),
                'action' => '
                    <a href="#" class="btn btn-sm btn-info rounded-pill">
                        <i class="bx bx-show"></i>
                    </a>
                ',
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function dashboardTransaction()
    {
        $merchantId = auth('merchant')->id();

        $query = MerchantTransaction::where('merchant_id', $merchantId);

        if (request('tanggal')) {
            $query->whereDate('created_at', request('tanggal'));
        }

        $transaction = $query->orderBy('created_at', 'desc')->get();

        $trxCount = $transaction->count();
        $trxCredit = $transaction->where('type', 'credit')->count();
        $trxDebit = $transaction->where('type', 'debit')->count();
        $todayTotal = $transaction->sum('amount');

        return view('pages.ekantin.dashboard_merchant.transaction.index', compact('transaction', 'trxCount', 'trxCredit', 'trxDebit', 'todayTotal'));
    }

    public function datatableTransaction(Request $request)
    {
        $columns = [
            'id',               // kolom # (no)
            'amount',           // kolom JML Transaksi
            'type',             // kolom Jenis Transaksi
            'balance_after',    // kolom JML Saldo Akhir
            'created_at',       // kolom Waktu Registrasi
        ];
        $merchant = Auth::guard('merchant')->user();

        $query = MerchantTransaction::where('merchant_id', $merchant->id);

        if (! empty($request->tanggal)) {
            $query->where('created_at', $request->tanggal);
        }

        // 🔍 SEARCH
        if (! empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('reference_id', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        $recordsTotal = MerchantTransaction::where('merchant_id', $merchant->id)->count();

        $recordsFiltered = $query->count();

        // ↕ ORDER
        if ($request->order) {
            $columnIndex = $request->order[0]['column'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($columns[$columnIndex] ?? 'id', $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        // 📄 PAGINATION
        $transaction = $query
            ->offset($request->start)
            ->limit($request->length)
            ->get();

        $data = [];
        $no = $request->start + 1;

        foreach ($transaction as $trx) {
            $data[] = [
                'no' => $no++,
                'kode_merchant' => $trx->merchant->kode_merchant ?? '-',
                'nama_merchant' => $trx->merchant->nama_merchant ?? '-',
                'amount' => number_format($trx->amount, 0, ',', '.'),
                'type' => ucfirst($trx->type),
                'balance_after' => 'Rp ' . number_format($trx->balance_after ?? 0, 0, ',', '.'),
                'waktu_registrasi' => Carbon::parse($trx->created_at)->format('d-m-Y H:i'),
                'action' => '
                    <a href="#" class="btn btn-sm btn-info rounded-pill">
                        <i class="bx bx-show"></i>
                    </a>
                ',
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}
