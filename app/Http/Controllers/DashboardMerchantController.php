<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardMerchantController extends Controller
{
    public function dashboard()
    {
        $merchantId = auth('merchant')->id();

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $pendapatan = DB::table('merchant_transactions')
            ->select(
                // Perhatikan letak tutup kurung DATE(created_at)
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(amount) as total')
            )
            ->where('merchant_id', $merchantId)
            ->where('type', 'credit')
            ->whereBetween('created_at', [$start, $end])
            // Di PostgreSQL, lebih aman menggunakan alias atau kolom asli di groupBy
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'), 'asc')
            ->get();

        return view('pages.ekantin.dashboard_merchant.dashboard', compact('pendapatan'));
    }

    public function product()
    {
        return view('pages.ekantin.dashboard_merchant.product');
    }
}
