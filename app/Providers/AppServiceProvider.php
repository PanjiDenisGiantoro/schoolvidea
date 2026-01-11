<?php

namespace App\Providers;

use App\Models\Keuangan_transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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

            if (! auth()->check()) {
                return;
            }

            $user = auth()->user();

            // =========================
            // Pending Tabungan
            // =========================
            $pendingTabunganQuery = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
                ->where('status_approval', 'pending');

            if ($user->unit_id) {
                $pendingTabunganQuery->whereHasMorph(
                    'penerima',
                    [\App\Models\Siswa::class],
                    fn ($q) => $q->where('unit_id', $user->unit_id)
                );
            } elseif ($user->yayasan_id) {
                $pendingTabunganQuery->whereHasMorph(
                    'penerima',
                    [\App\Models\Siswa::class],
                    fn ($q) => $q->whereHas(
                        'unit',
                        fn ($u) => $u->where('yayasan_id', $user->yayasan_id)
                    )
                );
            }

            $pendingTabungan = $pendingTabunganQuery
                ->latest('tanggal_transaksi')
                ->take(5)
                ->get();

            // =========================
            // Pending Tagihan
            // =========================
            $pendingTagihanQuery = Keuangan_transaksi::whereIn('jenis_transaksi', ['tagihan', 'pembayaran'])
                ->where('status_approval', 'pending');

            if ($user->unit_id) {
                $pendingTagihanQuery->whereHasMorph(
                    'penerima',
                    [\App\Models\Siswa::class],
                    fn ($q) => $q->where('unit_id', $user->unit_id)
                );
            } elseif ($user->yayasan_id) {
                $pendingTagihanQuery->whereHasMorph(
                    'penerima',
                    [\App\Models\Siswa::class],
                    fn ($q) => $q->whereHas(
                        'unit',
                        fn ($u) => $u->where('yayasan_id', $user->yayasan_id)
                    )
                );
            }

            $pendingTagihan = $pendingTagihanQuery
                ->latest('tanggal_transaksi')
                ->take(5)
                ->get();

            // =========================
            // Total Badge
            // =========================
            $totalPending = $pendingTabungan->count() + $pendingTagihan->count();

            $view->with(compact('pendingTabungan', 'pendingTagihan', 'totalPending'));
        });
    }
}
