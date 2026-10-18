<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function showLoginForm(Request $request): View
    {
        $lastEmail = $request->cookie('del_last_user_email', '');

        return view('auth.login', compact('lastEmail'));
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // WAJIB: Regenerasi ID sesi untuk mencegah Session Fixation
            $request->session()->regenerate();

            // Simpan timestamp sesi aktif
            session(['login_timestamp' => now()->format('d M Y, H:i:s') . ' WIB']);

            // Simpan email terakhir di HTTP Cookie (durasi 7 hari = 10080 menit)
            Cookie::queue('del_last_user_email', $request->email, 10080);

            return redirect()
                ->intended(route('bukus.index'))
                ->with('success', 'Sesi Anda telah aktif. Selamat datang, ' . Auth::user()->name . '!');
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->with('error', 'Kredensial login tidak valid.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Hapus data sesi dan invalidasi ID sesi
        $request->session()->invalidate();

        // Regenerasi token CSRF
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Sesi server telah dihapus dengan aman.');
    }
}
