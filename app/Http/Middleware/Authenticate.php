<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            // Check which guard is being used and redirect accordingly
            if ($request->is('admin/*')) {
                // prefer named route if present, otherwise fallback to /admin/login
                return Route::has('admin.login') ? route('admin.login') : url('/admin/login');
            }
            if ($request->is('customer/*')) {
                return Route::has('customer.login') ? route('customer.login') : url('/customer/login');
            }
            return Route::has('admin.login') ? route('admin.login') : url('/admin/login');
        }
        
        return null;
    }
}