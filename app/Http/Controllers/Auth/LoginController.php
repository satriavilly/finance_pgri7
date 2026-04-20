<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $auth = [$loginField => $credentials['login'], 'password' => $credentials['password']];

        if (!Auth::attempt($auth, $request->boolean('remember'))) {
            return back()->withErrors(['login' => 'Username/email atau password salah.'])->onlyInput('login');
        }

        if (!auth()->user()->is_active) {
            Auth::logout();
            return back()->withErrors(['login' => 'Akun Anda tidak aktif. Hubungi administrator.'])->onlyInput('login');
        }

        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
