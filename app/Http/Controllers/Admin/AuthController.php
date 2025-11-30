<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // Check if already logged in
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        // Debug: See what's being submitted
        Log::info('Login attempt:', $request->all());
        
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt authentication
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if the authenticated user is an admin
            if ($user->role === 'admin') {
                $request->session()->regenerate();
                Log::info('Authentication successful for admin: ' . $user->email);
                return redirect()->route('admin.dashboard');
            } else {
                // If not admin, logout and show error
                Auth::logout();
                Log::info('Authentication failed - not an admin: ' . $credentials['email']);
                return back()->withErrors([
                    'email' => 'Access denied. Admin privileges required.',
                ])->withInput();
            }
        }

        Log::info('Authentication failed for: ' . $credentials['email']);
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}