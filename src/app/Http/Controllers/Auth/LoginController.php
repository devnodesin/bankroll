<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginField = $credentials['username'];
        $password = $credentials['password'];
        $remember = $request->filled('remember');

        // Try to login with username (stored in name field)
        if (Auth::attempt(['name' => $loginField, 'password' => $password], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        // Try to login with email if first attempt failed
        if (Auth::attempt(['email' => $loginField, 'password' => $password], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}
