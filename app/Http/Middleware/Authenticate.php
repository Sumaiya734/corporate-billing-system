<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                return route('admin.login');
            }
            if ($request->is('customer/*')) {
                return route('customer.login');
            }
            // For root paths, check if user is logged in with specific guard
            if (Auth::guard('customer')->check()) {
                return route('customer.dashboard');
            }
            if (Auth::guard('admin')->check()) {
                return route('admin.dashboard');
            }
            // Default to customer login for general paths
            return route('customer.login');
        }
        
        return null;
    }
}