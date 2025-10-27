<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

// We'll use the framework Authenticate middleware to set a safe redirect callback
use Illuminate\Auth\Middleware\Authenticate as FrameworkAuthenticate;

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
        // Fix for MySQL index length issue
        Schema::defaultStringLength(191);

        // Ensure authentication redirect callback is safe: avoid calling route('login') when
        // that named route isn't registered yet. We prefer admin/customer named routes
        // and fall back to a sensible URL.
        FrameworkAuthenticate::redirectUsing(function ($request) {
            // Prefer explicit admin/customer login named routes when available
            if ($request->is('admin/*') && Route::has('admin.login')) {
                return route('admin.login');
            }

            if ($request->is('customer/*') && Route::has('customer.login')) {
                return route('customer.login');
            }

            // If a generic 'login' named route exists, use it.
            if (Route::has('login')) {
                return route('login');
            }

            // Final fallback: direct URL to admin login
            return url('/admin/login');
        });
    }
}
