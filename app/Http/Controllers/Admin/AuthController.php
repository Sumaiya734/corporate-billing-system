<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // Add this line
use App\Models\User; // Add this line

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // Check if already logged in
        if (Auth::check()) {
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

        // Debug: Check if user exists and password matches
        $user = User::where('email', $credentials['email'])->first();
        
        if ($user) {
            Log::info('User found:', ['email' => $user->email]);
            
            // Check password manually
            if (Hash::check($credentials['password'], $user->password)) {
                Log::info('Password matches!');
            } else {
                Log::info('Password does NOT match!');
                Log::info('Input password: ' . $credentials['password']);
                Log::info('Stored hash: ' . $user->password);
            }
        } else {
            Log::info('User not found with email: ' . $credentials['email']);
        }

        // Attempt authentication
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            Log::info('Authentication successful for: ' . Auth::user()->email);
            return redirect()->route('admin.dashboard');
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