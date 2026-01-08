<?php

namespace App\Http\Controllers;

use App\Models\MerchantProduct;
use App\Models\Merchants;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardMerchantController extends Controller
{
    public function dashboard()
    {
        $merchantId = auth('merchant')->id();
        $id = Merchants::where('id', $merchantId)->value('id');

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $productTotal = MerchantProduct::where('merchant_id', $id)
            ->count();

        $pendapatan = DB::table('merchant_transactions')
            ->select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(amount) as total')
            )
            ->where('merchant_id', $merchantId)
            ->where('type', 'credit')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'), 'asc')
            ->get();

        return view('pages.ekantin.dashboard_merchant.dashboard', compact('pendapatan', 'productTotal'));
    }

    public function profile()
    {

        $merchant = auth('merchant')->user()->load('unit');

        return view('pages.ekantin.dashboard_merchant.profile', compact('merchant'));
    }

    public function updateProfile(Request $request)
    {
        // Ambil merchant yang sedang login lewat guard 'merchant'
        $merchant = auth('merchant')->user();

        $validated = $request->validate([
            'kode_merchant' => 'required|string|max:255|unique:merchants,kode_merchant,' . $merchant->id,
            'nama_merchant' => 'required|string|max:255',
            'jenis' => 'required|string|max:255',
            'pemilik' => 'required|string|max:255',
            'no_hp' => 'required|string|max:14',
            'status' => 'required|in:0,1',
            'password' => 'nullable|min:6',
            'bank_name' => 'required|string|max:50',
            'account_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:30',
        ]);

        // Jika password diisi, hash dulu
        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        } else {
            unset($validated['password']);
        }

        // Update merchant yang login
        $merchant->update($validated);

        return redirect()
            ->route('merchant.profile') // arahkan ke halaman profile merchant
            ->with('success', 'Profil Merchant berhasil diperbarui');
    }

    public function updatePhoto(Request $request)
    {
        // merchant login (guard merchant)
        $merchant = auth('merchant')->user();

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Hapus foto lama jika ada
        if ($merchant->image && Storage::disk('public')->exists($merchant->image)) {
            Storage::disk('public')->delete($merchant->image);
        }

        // Simpan foto baru
        $path = $request->file('image')
            ->store('merchant/profile', 'public');

        // Update ke database
        $merchant->update([
            'image' => $path,
        ]);

        // Jika request AJAX / JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Foto profile berhasil diperbarui',
                'image_url' => asset('storage/' . $path),
            ]);
        }

        // Jika form biasa
        return redirect()
            ->route('merchant.profile')
            ->with('success', 'Foto profile berhasil diperbarui');
    }
}
