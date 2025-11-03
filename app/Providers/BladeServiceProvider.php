<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Directive untuk check permission
        Blade::if('hasPermission', function ($permission) {
            return hasPermission($permission);
        });

        // Directive untuk check role
        Blade::if('hasRole', function ($role) {
            return hasRole($role);
        });

        // Directive untuk check any permission
        Blade::if('hasAnyPermission', function (...$permissions) {
            return hasAnyPermission($permissions);
        });

        // Directive untuk check all permissions
        Blade::if('hasAllPermissions', function (...$permissions) {
            return hasAllPermissions($permissions);
        });

        // Directive untuk check superadmin
        Blade::if('isSuperAdmin', function () {
            return isSuperAdmin();
        });

        // Directive untuk check if can view menu
        Blade::if('canViewMenu', function ($permissions) {
            return canViewMenu($permissions);
        });
    }
}