<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\DataRekening;
use App\Models\Yayasan;
use App\Models\Akun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DataRekeningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $units = Unit::all();
        $query = DataRekening::with(['unit' => function ($q) {
            $q->isactive();
        }, 'akun']);

        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan akun dari semua unit di yayasan tersebut
            $query->whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            });
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan akun dari unit tersebut saja
            $query->where('unit_id', Auth::user()->unit_id);
        } elseif ($request->filled('unit_id')) {
            // Admin user filtering by unit
            $query->where('unit_id', $request->unit_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_name', 'like', "%{$search}%")
                  ->orWhere('account_code', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%")
                  ->orWhereHas('unit', function ($q) use ($search) {
                      $q->where('nama_unit', 'like', "%{$search}%");
                  });
            });
        }
        $datarekening = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->except('page'));
        $headers = [
            'No', 'Unit', 'Kode Rekening', 'Nama Bank', 'Nama Pemilik', 'Akun', 'Status', 'Peruntukan Rekening', 'Aksi'
        ];
        return view('pages.data_master.data_rekening.index', compact('datarekening', 'headers', 'units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Filter yayasan dan units berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan yayasan tersebut dan semua unit di yayasan tersebut
            $yayasan = Yayasan::where('status', '1')->where('id', Auth::user()->yayasan_id)->get();
            $units = Unit::where('status', '1')->where('yayasan_id', Auth::user()->yayasan_id)->get();
            $akuns = Akun::where('unit_id',Auth::user()->unit_id)->get();

        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan yayasan yang terkait dan unit tersebut saja
            $yayasan = Yayasan::with('units')
                ->when(Auth::user()->unit_id, function ($query, $unitId) {
                    $query->whereHas('units', function ($q) use ($unitId) {
                        $q->where('id', $unitId);
                    });
                })->where('status', '1')->get();
            $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('id', $unitId);
            })->where('status', '1')->get();
            $akuns = Akun::where('unit_id',Auth::user()->unit_id)->get();

        } else {
            // Admin bisa melihat semua
            $yayasan = Yayasan::where('status', '1')->get();
            $units = Unit::where('status', '1')->get();
            $akuns = Akun::all();

        }

        // Load semua akun untuk dropdown

        return view('pages.data_master.data_rekening.create', compact('yayasan', 'units', 'akuns'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_name'  => 'required|string|max:50',
            'account_code'  => 'required|string|max:50',
            'account_number' => 'required|string|max:50',
            'owner_name'    => 'required|string|max:50',
            'allotment'     => 'required|string|max:50',
            'kcp_name'      => 'string|max:50',
            'unit_id'       => 'required|exists:units,id',
            'akun_id'       => 'required',
            'status'        => 'required|in:1,0',
            'image'         => 'nullable|string',
        ]);

        DataRekening::create([
            'account_name'   => $request->account_name,
            'account_code'   => $request->account_code,
            'account_number' => $request->account_number,
            'owner_name'     => $request->owner_name,
            'allotment'      => $request->allotment,
            'kcp_name'       => $request->kcp_name,
            'unit_id'        => $request->unit_id,
            'akun_id'        => $request->akun_id,
            'status'         => $request->status,
            'account_pict'   => $request->image ?? null,
        ]);

        return redirect()->route('data-rekening.index')->with('success', 'Data rekening berhasil ditambah');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $datarekening = DataRekening::findOrFail($id);

        // Filter units berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan semua unit di yayasan tersebut
            $units = Unit::where('status', '1')->where('yayasan_id', Auth::user()->yayasan_id)->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan unit tersebut saja
            $units = Unit::where('status', '1')->where('id', Auth::user()->unit_id)->get();
        } else {
            // Admin bisa melihat semua
            $units = Unit::isactive()->get();
        }

        // Load semua akun untuk dropdown
        $akuns = Akun::all();
        $show = true;

        return view('pages.data_master.data_rekening.create', compact('datarekening', 'units', 'akuns', 'show'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $datarekening = DataRekening::findOrFail($id);

        // Filter units berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan semua unit di yayasan tersebut
            $units = Unit::where('status', '1')->where('yayasan_id', Auth::user()->yayasan_id)->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan unit tersebut saja
            $units = Unit::when(Auth::user()->unit_id, function ($query, $unit_id) {
                $query->where('id', $unit_id);
            })->where('status', '1')->get();
        } else {
            // Admin bisa melihat semua
            $units = Unit::where('status', '1')->get();
        }

        // Load semua akun untuk dropdown
        $akuns = Akun::all();

        return view('pages.data_master.data_rekening.create', compact(
            'datarekening',
            'units',
            'akuns',
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $datarekening = DataRekening::findOrFail($id);

        $request->validate([
            'account_name'   => 'required|string|max:50',
            'account_code'   => 'required|string|max:50',
            'account_number' => 'required|string|max:50',
            'owner_name'     => 'required|string|max:50',
            'allotment'      => 'required|string|max:50',
            'kcp_name'       => 'string|max:50',
            'unit_id'        => 'required|exists:units,id',
            'akun_id'        => 'required',
            'status'         => 'required|in:1,0',
            'image'          => 'nullable|string'
        ]);

        // Simpan nilai gambar lama
        $oldImage = $datarekening->account_pict;

        $datarekening->update([
            'account_name'   => $request->account_name,
            'account_code'   => $request->account_code,
            'account_number' => $request->account_number,
            'owner_name'     => $request->owner_name,
            'allotment'      => $request->allotment,
            'kcp_name'       => $request->kcp_name,
            'unit_id'        => $request->unit_id,
            'akun_id'        => $request->akun_id,
            'status'         => $request->status,
            'account_pict'   => $request->image ?? $oldImage,
        ]);

        // Jika ada image baru → hapus yang lama
        if ($request->image && $request->image !== $oldImage) {
            if ($oldImage && file_exists(public_path($oldImage))) {
                unlink(public_path($oldImage));
            }
        }
        return redirect()->route('data-rekening.index')->with('success', 'Data rekening berhasil diubah');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $datarekening = DataRekening::findOrFail($id);
        $datarekening->delete();
        return redirect()->route('data-rekening.index')->with('success', 'Data rekening berhasil dihapus');

    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $file = $request->file('file');
        $filename = Str::random(15) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/rekening', $filename, 'public');

        return response()->json([
            'success' => true,
            'filepath' => 'storage/' . $path
        ]);
    }
}
