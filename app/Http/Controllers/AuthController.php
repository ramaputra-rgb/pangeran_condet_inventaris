<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Menampilkan form login
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Memproses autentikasi pengguna dengan proteksi rate limiting & session regeneration
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $this->ensureIsNotRateLimited($request);

        $loginInput = trim($request->input('username'));
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Dukungan login menggunakan username ataupun email
        $primaryField = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$primaryField => $loginInput, 'password' => $password];

        $authenticated = Auth::attempt($credentials, $remember);

        // Jika gagal dengan field utama, coba cek alternate field (username / email)
        if (!$authenticated) {
            $altField = ($primaryField === 'email') ? 'username' : 'email';
            $authenticated = Auth::attempt([$altField => $loginInput, 'password' => $password], $remember);
        }

        if (!$authenticated) {
            RateLimiter::hit($this->throttleKey($request), 60);

            throw ValidationException::withMessages([
                'username' => 'Username/email atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        // Bersihkan hit rate limiter setelah berhasil
        RateLimiter::clear($this->throttleKey($request));

        // Regenerasi session ID untuk mencegah serangan Session Fixation
        $request->session()->regenerate();

        $userName = Auth::user()->name ?? Auth::user()->username ?? 'Pengguna';
        return redirect()->intended(route('dashboard'))
            ->with('success', 'Selamat datang kembali, ' . $userName . '!');
    }

    /**
     * Logout pengguna dan hancurkan sesi secara aman
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidate dan regenerasi token CSRF untuk keamanan sesi
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Memastikan percobaan login tidak melebihi batas (Mencegah Brute Force Attack)
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        $key = $this->throttleKey($request);

        if (!RateLimiter::tooManyAttempts($key, 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'username' => "Terlalu banyak percobaan masuk yang salah. Silakan coba lagi dalam {$seconds} detik.",
        ]);
    }

    /**
     * Menghasilkan key unik rate limit berdasarkan input login dan alamat IP
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('username', '')) . '|' . $request->ip());
    }
}
