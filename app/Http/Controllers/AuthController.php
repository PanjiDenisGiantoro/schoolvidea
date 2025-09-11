<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function portalCode()
    {
        return view('pages.portalcode');
    }

    // Proses cek kode sekolah
    public function checkPortalCode(Request $request)
    {
        $lembaga = Unit::where('code', $request->kode_sekolah)->first();

        if (!$lembaga) {
            return back()->with('error', 'Kode sekolah tidak ditemukan');
        }

        // simpan di session
        session(['lembaga_id' => $lembaga->id, 'kode_sekolah' => $lembaga->kode_sekolah]);

        return redirect()->route('login.form')->with('success', 'Kode sekolah valid, silakan login.');
    }

    // Halaman login
    public function loginForm()
    {
        if (!session()->has('lembaga_id')) {
            return redirect()->route('portal.form')->with('error' , 'Masukkan kode sekolah terlebih dahulu');
        }

        return view('pages.login');
    }

    // Proses login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah',
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.form')->with('success', 'Anda telah logout');
    }
}
