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

        return view("pages.login");
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
        try {
            $request->validate([
                "email" => "required|email",
                "password" => "required",
            ]);

            $credentials = $request->only("email", "password");

            // Cek apakah user dengan email tersebut ada
            $user = User::where("email", $request->email)->first();

            if (!$user) {
                Log::warning("Login gagal - Email tidak ditemukan", [
                    "email" => $request->email,
                    "ip" => $request->ip(),
                ]);

                activity()
                    ->withProperties([
                        "email" => $request->email,
                        "reason" => "Email tidak ditemukan",
                    ])
                    ->log("Percobaan login gagal - Email tidak terdaftar");

                return back()
                    ->withErrors([
                        "email" => "Email tidak terdaftar",
                    ])
                    ->withInput($request->only("email"));
            }

            // Cek apakah user aktif (jika ada kolom status)
            if (isset($user->status) && $user->status == "0") {
                Log::warning("Login gagal - User tidak aktif", [
                    "email" => $request->email,
                    "user_id" => $user->id,
                ]);

                activity()
                    ->withProperties([
                        "email" => $request->email,
                        "user_id" => $user->id,
                        "reason" => "User tidak aktif",
                    ])
                    ->log("Percobaan login gagal - User tidak aktif");

                return back()
                    ->withErrors([
                        "email" =>
                            "Akun Anda tidak aktif. Silakan hubungi administrator.",
                    ])
                    ->withInput($request->only("email"));
            }

            // Coba login dengan credentials
            if (Auth::attempt($credentials, $request->filled("remember"))) {
                $request->session()->regenerate();

                Log::info("Login berhasil", [
                    "user_id" => Auth::id(),
                    "email" => $request->email,
                    "ip" => $request->ip(),
                ]);

                activity()
                    ->causedBy(Auth::user())
                    ->withProperties([
                        "email" => $request->email,
                        "ip" => $request->ip(),
                    ])
                    ->log("User berhasil login");

                return redirect()->intended(route("dashboard"));
            }

            // Password salah
            Log::warning("Login gagal - Password salah", [
                "email" => $request->email,
                "user_id" => $user->id,
                "ip" => $request->ip(),
            ]);

            activity()
                ->withProperties([
                    "email" => $request->email,
                    "user_id" => $user->id,
                    "reason" => "Password salah",
                ])
                ->log("Percobaan login gagal - Password salah");

            return back()
                ->withErrors([
                    "password" => "Password yang Anda masukkan salah",
                ])
                ->withInput($request->only("email"));
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangani validation error
            Log::error("Login validation error", [
                "errors" => $e->errors(),
                "email" => $request->email ?? null,
            ]);

            return back()
                ->withErrors($e->errors())
                ->withInput($request->only("email"));
        } catch (\Exception $e) {
            // Tangani error lainnya
            Log::error("Login error exception", [
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "email" => $request->email ?? null,
                "ip" => $request->ip(),
            ]);

            activity()
                ->withProperties([
                    "email" => $request->email ?? null,
                    "error" => $e->getMessage(),
                ])
                ->log("Error saat proses login");

            return back()
                ->withErrors([
                    "email" =>
                        "Terjadi kesalahan saat login. Silakan coba lagi.",
                ])
                ->withInput($request->only("email"));
        }
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
        return view("pages.activity_log.activity_log", compact("activity"));
    }
}
