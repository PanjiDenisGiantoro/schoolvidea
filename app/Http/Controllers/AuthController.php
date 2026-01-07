<?php

namespace App\Http\Controllers;

use App\Models\Tipeunit;
use App\Models\Unit;
use App\Models\Yayasan;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class AuthController extends Controller
{
    public function portalCode()
    {
        return view("pages.portalcode");
    }

    // Proses cek kode sekolah
    public function checkPortalCode(Request $request)
    {
        $lembaga = Unit::where("code", $request->kode_sekolah)->first();

        if (!$lembaga) {
            activity()
                ->withProperties(["kode_sekolah" => $request->kode_sekolah])
                ->log("User memasukkan kode sekolah yang salah");
            return back()->with("error", "Kode sekolah tidak ditemukan");
        }

        if ($lembaga->status == "0") {
            activity()
                ->withProperties(["kode_sekolah" => $request->kode_sekolah])
                ->log("User memasukkan kode sekolah yang tidak aktif");
            return back()->with("error", "Lembaga tidak aktif");
        }
        // simpan di session
        session([
            "lembaga_id" => $lembaga->id,
            "kode_sekolah" => $lembaga->kode_sekolah,
        ]);

        return redirect()
            ->route("login.form")
            ->with("success", "Kode sekolah valid, silakan login.");
    }

    // Halaman login
    public function loginForm()
    {
        if (!session()->has("lembaga_id")) {
            return redirect()
                ->route("login.form")
                ->with("error", "Masukkan kode sekolah terlebih dahulu");
        }


        // Ambil data unit berdasarkan lembaga_id di session
        $unit = \App\Models\Unit::where('code',session('lembaga_id'))->first();
        $logoUrl = $unit && $unit->image
            ? asset($unit->image)
            : asset('assets/images/videa.png');

        return view("pages.login", compact('logoUrl'));
    }
    // Halaman login
    public function portalcentral()
    {

        return view("pages.login");
    }
    public function loginunit()
    {
        return view("pages.login");
    }


    // Proses login
    public function portal(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',

        ]);
        $loginType = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $loginType => $request->email,
            'password' => $request->password,
        ];


        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    $loginType => $request->email
                ])
                ->log('User berhasil login');

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email/Username atau password salah',
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        activity()->causedBy(Auth::user())->log("User logout");

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/login")->with("success", "Anda telah logout");
    }
    public function registerpublic()
    {
        $tipeunit = Tipeunit::all();
        return view("registerpublic", compact("tipeunit"));
    }
    public function store(Request $request)
    {
        // Validasi input
        $data = $request->validate([
            "school_name" => "required|string|max:150",
            "npsn" => "required|string|max:30",
            "address" => "required|string|max:255",
            "full_name" => "required|string|max:150",
            "email" => "required|email|max:150",
            "no_hp" => "required|string|max:30",
            "agree" => "accepted",
            "tipe_unit_id" => "required|integer",
            "yayasan_id" => "nullable|integer|exists:yayasans,id", // Validasi jika yayasan_id ada
        ]);

        // Jika tidak ada yayasan_id yang dipilih, maka atur menjadi null
        $yayasan_id = $data["yayasan_id"] ?? null;

        if (!empty($data["yayasan_id"])) {
            $yayasan = Yayasan::create([
                "nama_yayasan" => $data["yayasan_id"],
                "nama_pimpinan" => $data["full_name"],
                "central_code" => $data["npsn"],
                "email" => $data["email"],
                "no_hp" => $data["no_hp"],
                "alamat" => $data["address"],
                "website" => $data["website"],
                "status" => "0",
            ]);
        }


        // Simpan data unit baru
        $unit = Unit::create([
            "nama_unit" => $data["school_name"],
            "code" => $data["npsn"],
            "alamat" => $data["address"],
            "email" => $data["email"],
            "no_hp" => $data["no_hp"],
            "tipe_unit_id" => $data["tipe_unit_id"],
            "yayasan_id" => $yayasan_id,
            "status" => "active", // Status default
        ]);
        activity()
            ->withProperties([
                "email" => $data["email"],
                "school" => $data["school_name"],
            ])
            ->log("User melakukan pendaftaran publik");
        return redirect()
            ->route("landing") // Ganti dengan route yang sesuai
            ->with(
                "success",
                "Terima kasih! Data Anda sudah kami terima. Tim kami akan segera menghubungi Anda.",
            );
    }
    public function showupdate()
    {
        return view("pages.profile.update");
    }

    // Function untuk handle update password
    public function updatePassword(Request $request)
    {
        // Validasi input
        $request->validate([
            "current_password" => "required|string",
            "new_password" => "required|string|min:6|confirmed",
        ]);


        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        activity()
            ->causedBy($user)
            ->withProperties(["user_id" => $user->id])
            ->log("User mengubah password");

        // Redirect dengan sukses
        return redirect()
            ->route("profile.show")
            ->with("success", "Password berhasil diupdate.");
    }
    public function activity()
    {
        $activity = Activity::latest()->get();
        return view('pages.activity_log.activity_log', compact('activity'));
    }
}
