<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use App\Models\Keuangan_transaksi;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Telescope auth (only if available)
        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::auth(function ($request) {
                return auth()->check() && auth()->user()->is_admin;
            });
        }

        Carbon::setLocale('id');

    View::composer('*', function ($view) {

        // Pending Tabungan
        $pendingTabungan = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
            ->where('status_approval', 'pending')
            ->latest('tanggal_transaksi')
            ->take(5)
            ->get();

        // Pending Tagihan
        $pendingTagihan = Keuangan_transaksi::whereIn('jenis_transaksi', ['tagihan', 'pembayaran'])
            ->where('status_approval', 'pending')
            ->latest('tanggal_transaksi')
            ->take(5)
            ->get();

        // Total pending untuk badge
        $totalPending = $pendingTabungan->count() + $pendingTagihan->count();

        $view->with(compact('pendingTabungan', 'pendingTagihan', 'totalPending'));
    });
    }
}
