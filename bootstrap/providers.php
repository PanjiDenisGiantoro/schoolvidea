<?php

$providers = [
    App\Providers\AppServiceProvider::class,
    App\Providers\BladeServiceProvider::class,
    App\Providers\QrCodeServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
];

// Only load Telescope in local environment or if package exists
if (class_exists(\Laravel\Telescope\TelescopeApplicationServiceProvider::class)) {
    $providers[] = App\Providers\TelescopeServiceProvider::class;
}

return $providers;
