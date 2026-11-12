<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->flash('request_push_permission', true);

            // Pakai full page load (Inertia::location -> 409 + X-Inertia-Location),
            // bukan redirect XHR biasa. Alasannya:
            // - Shell Blade (CSRF meta, status login, tombol & init notifikasi,
            //   flag request_push_permission) dirender ulang sesuai sesi baru.
            // - Tidak bergantung pada state klien Inertia halaman login yang
            //   bisa saja basi (versi aset lama / dibuka dari cache).
            return Inertia::location($this->intendedUrl($request));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location(url('/login'));
    }

    /**
     * URL tujuan setelah login. url.intended hanya dipakai jika masih di host
     * yang sama dan bukan halaman login/aset; selain itu ke dashboard.
     * (URL intended bisa basi, mis. tersimpan dari host/tunnel ngrok lama.)
     */
    private function intendedUrl(Request $request): string
    {
        $default = url('/dashboard');
        $intended = $request->session()->pull('url.intended');

        if (! is_string($intended) || $intended === '') {
            return $default;
        }

        $host = parse_url($intended, PHP_URL_HOST);
        $path = parse_url($intended, PHP_URL_PATH) ?: '/';

        if ($host && $host !== $request->getHost()) {
            return $default;
        }

        if ($path === '/' || str_starts_with($path, '/login') || preg_match('/\.(js|css|json|webmanifest|png|ico|svg|map)$/i', $path)) {
            return $default;
        }

        return $intended;
    }
}
