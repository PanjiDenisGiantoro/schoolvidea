<?php

namespace App\Http\Controllers;

use App\Models\MerchantProduct;
use App\Models\Merchants;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardMerchantController extends Controller
{
    public function dashboard()
    {
        $merchantId = auth('merchant')->id();
        $id = Merchants::where('no_hp', $merchantId)->value('id');

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $productTotal = MerchantProduct::where('merchant_id', $id)
            ->count();

        $pendapatan = DB::table('merchant_transactions')
            ->select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(amount) as total')
            )
            ->where('merchant_id', $merchantId)
            ->where('type', 'credit')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'), 'asc')
            ->get();

        return view('pages.ekantin.dashboard_merchant.dashboard', compact('pendapatan', 'productTotal'));
    }
}
