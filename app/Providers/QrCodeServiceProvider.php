<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Tambahkan alias secara manual
        $this->app->alias(QrCode::class, 'QrCode');
    }

    public function boot(): void
    {
        //
    }
}
