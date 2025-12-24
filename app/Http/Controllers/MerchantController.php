<?php

namespace App\Http\Controllers;

use App\Models\Merchants;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MerchantController extends Controller
{
    public function index()
    {
        $units = Unit::all();
        $query = Merchants::query();

        if (auth()->user()->unit_id) {
            $query->where('unit_id', auth()->user()->unit_id);
        }

        $merchants = $query;
        $merCount = $merchants->count();
        $merActive = $merchants->with('status', '1')->count();

        return view('pages.ekantin.merchant.index', compact('units', 'merchants', 'merCount', 'merActive'));
    }

    public function create()
    {
        $units = Unit::all();
        $kodeMerchant = 'MRC-' . Carbon::now()->format('YmdHis');

        return view('pages.ekantin.merchant.create', compact('units', 'kodeMerchant'));
    }

    public function edit(Merchants $merchant)
    {
        $units = Unit::all();

        return view('pages.ekantin.merchant.create', compact('merchant', 'units'));
    }

    public function show(Merchants $merchant)
    {
        $units = Unit::all();
        $show = true;

        return view('pages.ekantin.merchant.create', compact('merchant', 'units', 'show'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'kode_merchant' => 'required|string|max:255|unique:merchants,kode_merchant',
            'nama_merchant' => 'required|string|max:255',
            'jenis' => 'required|string|max:255',
            'pemilik' => 'required|string|max:255',
            'no_hp' => 'required|string|max:14',
            'status' => 'required|in:0,1',
            'password' => 'required|min:6',
        ]);

        $validated['password'] = bcrypt($request->password);
        $validated['created_by'] = auth()->id();

        Merchants::create($validated);

        return redirect()
            ->route('merchant.index')
            ->with('success', 'Merchant berhasil ditambahkan');
    }

    public function update(Request $request, Merchants $merchant)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'kode_merchant' => [
                'required',
                'string',
                'max:255',
                Rule::unique('merchants', 'kode_merchant')->ignore($merchant->id),
            ],
            'nama_merchant' => 'required|string|max:255',
            'jenis' => 'required|string|max:255',
            'pemilik' => 'required|string|max:255',
            'no_hp' => 'required|string|max:14',
            'status' => 'required|in:0,1',
            'password' => 'nullable|min:6',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        } else {
            unset($validated['password']);
        }

        $merchant->update($validated);

        return redirect()
            ->route('merchant.index')
            ->with('success', 'Merchant berhasil diperbarui');
    }

    public function destroy($id)
    {
        $merchant = Merchants::findOrFail($id);

        if (auth()->user()->unit_id && $merchant->unit_id != auth()->user()->unit_id) {
            abort(403, 'Tidak punya akses menghapus merchant ini');
        }

        $merchant->delete();

        return redirect()->route('merchant.index')->with('success', 'Merchant berhasil dihapus');
    }

    // =======================
    // DATATABLE
    // =======================
    public function datatable(Request $request)
    {
        $query = Merchants::query();

        if ($request->unit_id) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->status_merchant !== null && $request->status_merchant !== '') {
            $query->where('status', $request->status_merchant);
        }

        // Search DataTables
        if (! empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('kode_merchant', 'LIKE', "%{$search}%")
                    ->orWhere('nama_merchant', 'LIKE', "%{$search}%")
                    ->orWhere('pemilik', 'LIKE', "%{$search}%")
                    ->orWhere('jenis', 'LIKE', "%{$search}%");
            });
        }

        if ($request->search_custom) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_merchant', 'like', "%{$request->search_custom}%")
                    ->orWhere('nama_merchant', 'like', "%{$request->search_custom}%")
                    ->orWhere('pemilik', 'like', "%{$request->search_custom}%");
            });
        }

        $recordsFiltered = $query->count();
        $recordsTotal = Merchants::count();

        $merchants = $query
            ->orderBy('id', 'desc')
            ->offset($request->start)
            ->limit($request->length)
            ->get();

        $data = [];
        $no = $request->start + 1;

        foreach ($merchants as $merchant) {
            $data[] = [
                'no' => $no++,
                'kode_merchant' => $merchant->kode_merchant,
                'nama_merchant' => $merchant->nama_merchant,
                'pemilik' => $merchant->pemilik,
                'jenis' => $merchant->jenis,
                'no_hp' => $merchant->no_hp,
                'saldo_aktif' => 'Rp ' . number_format($merchant->saldo_aktif ?? 0, 0, ',', '.'),
                'waktu_registrasi' => Carbon::parse($merchant->created_at)->format('d-m-Y H:i'),
                'status' => $merchant->status
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Non Aktif</span>',
                'action' => '
            <div class="d-flex align-items-center gap-2">
                <a href="' . route('merchant.show', $merchant->id) . '"
                   class="btn btn-sm btn-success rounded-pill">
                    <i class="ri-eye-line"></i>
                </a>

                <a href="' . route('merchant.edit', $merchant->id) . '"
                   class="btn btn-sm btn-warning rounded-pill">
                    <i class="ri-pencil-line"></i>
                </a>

                <button
                    type="button"
                    class="btn btn-sm btn-danger rounded-pill btn-delete"
                    data-id="' . $merchant->id . '"
                    data-url="' . route('merchant.delete', $merchant->id) . '"
                >
                    <i class="ri-delete-bin-7-line"></i>
                </button>
            </div>
        ',
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    // LOGIN
    public function showLoginForm()
    {
        return view('pages.ekantin.dashboard_merchant.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'no_hp' => 'required',
            'password' => 'required',
        ]);

        if (
            Auth::guard('merchant')->attempt([
                'no_hp' => $request->no_hp,
                'password' => $request->password,
            ])
        ) {
            $request->session()->regenerate();

            $merchant = Auth::guard('merchant')->user();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil masuk',
                'redirect' => route('merchant.dashboard'),
                'merchant' => [
                    'id' => $merchant->id,
                    'nama_merchant' => $merchant->nama_merchant,
                    'no_hp' => $merchant->no_hp,
                    'saldo_aktif' => $merchant->saldo_aktif,
                ],
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Nomor HP atau Password salah',
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::guard('merchant')->logout();

        // $request->session()->invalidate();
        $request->session()->regenerateToken();

        return view('pages.ekantin.dashboard_merchant.login');
    }
}
