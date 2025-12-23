<?php

namespace App\Http\Controllers;

use App\Models\MerchantWithdrawal;
use App\Models\Unit;
use Illuminate\Http\Request;

class MerchantWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::all();

        $baseQuery = MerchantWithdrawal::query();

        if (auth()->user()->unit_id) {
            $baseQuery->whereHas('merchant', function ($q) {
                $q->where('unit_id', auth()->user()->unit_id);
            });
        }

        $summary = [
            'total_nominal' => (clone $baseQuery)->sum('amount'),
            'total_approved' => (clone $baseQuery)->where('status', 'approved')->sum('amount'),
            'total_pending' => (clone $baseQuery)->where('status', 'pending')->sum('amount'),
            'total_rejected' => (clone $baseQuery)->where('status', 'rejected')->sum('amount'),
        ];

        return view(
            'pages.ekantin.withdrawal.index',
            compact('units', 'summary')
        );
    }

    public function datatable(Request $request)
    {
        $query = MerchantWithdrawal::with('merchant');

        if ($request->unit_id) {
            $query->whereHas(
                'merchant',
                fn ($q) => $q->where('unit_id', $request->unit_id)
            );
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->tanggal) {
            $query->whereDate('requested_at', $request->tanggal);
        }

        if (! empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->whereHas('merchant', function ($q) use ($search) {
                $q->where('kode_merchant', 'like', "%{$search}%")
                    ->orWhere('nama_merchant', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();
        $recordsTotal = MerchantWithdrawal::count();

        $rows = $query
            ->orderBy('requested_at', 'desc')
            ->offset($request->start)
            ->limit($request->length)
            ->get();

        $data = [];
        $no = $request->start + 1;

        foreach ($rows as $wd) {
            $data[] = [
                'no' => $no++,
                'kode_merchant' => $wd->merchant->kode_merchant,
                'nama_merchant' => $wd->merchant->nama_merchant,
                'no_telp' => $wd->merchant->no_hp,
                'jml' => 'Rp ' . number_format($wd->amount, 0, ',', '.'),
                'metode' => 'Debit',
                'status' => ucfirst($wd->status),
                'waktu_penarikan' => $wd->requested_at->format('d-m-Y H:i'),
                'action' => view('pages.ekantin.withdrawal.action', compact('wd'))->render(),
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
