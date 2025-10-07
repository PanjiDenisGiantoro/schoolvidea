<?php

namespace App\Http\Controllers;

use App\Models\Tipeunit;
use App\Models\Unit;
use App\Models\Yayasan;
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

        if ($lembaga->status == '0'){
            return back()->with('error', 'Lembaga tidak aktif');
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
    // Halaman login
    public function logincentral()
    {
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
        return redirect('/login')->with('success', 'Anda telah logout');
    }
    public function registerpublic()
    {
        $tipeunit  = Tipeunit::all();
        return view('registerpublic',compact('tipeunit'));
    }
    public function store(Request $request)
    {
        // Validasi input
        $data = $request->validate([
            'school_name'    => 'required|string|max:150',
            'npsn'           => 'required|string|max:30',
            'address'        => 'required|string|max:255',
            'full_name'      => 'required|string|max:150',
            'email'          => 'required|email|max:150',
            'no_hp'          => 'required|string|max:30',
            'agree'          => 'accepted',
            'tipe_unit_id'   => 'required|integer',
            'yayasan_id'     => 'nullable|integer|exists:yayasans,id',  // Validasi jika yayasan_id ada
        ]);

        // Jika tidak ada yayasan_id yang dipilih, maka atur menjadi null
        $yayasan_id = $data['yayasan_id'] ?? null;

        if(!empty($data['yayasan_id'])){

//            yayasans
//id
//nama_yayasan
//nama_pimpinan
//central_code
//image
//no_hp
//email
//alamat
//website
//status
//created_at
//updated_at

            $yayasan = Yayasan::create([
                'nama_yayasan' => $data['yayasan_id'],
                'nama_pimpinan' => $data['full_name'],
                'central_code' => $data['npsn'],
                'email' => $data['email'],
                'no_hp' => $data['no_hp'],
                'alamat' => $data['address'],
                'website' => $data['website'],
                'status' => '0',
            ]);
        }


        // Simpan data unit baru
        $unit = Unit::create([
            'nama_unit'      => $data['school_name'],
            'code'           => $data['npsn'],
            'alamat'         => $data['address'],
            'email'          => $data['email'],
            'no_hp'          => $data['no_hp'],
            'tipe_unit_id'   => $data['tipe_unit_id'],
            'yayasan_id'     => $yayasan_id,
            'status'         => 'active',  // Status default
        ]);

        // Redirect kembali dengan sukses
        return redirect()
            ->route('landing') // Ganti dengan route yang sesuai
            ->with('success', 'Terima kasih! Data Anda sudah kami terima. Tim kami akan segera menghubungi Anda.');
    }
}
