<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Tipeunit;
use App\Models\Unit;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    public function index(Request $request){
        $units = Unit::all();

        // Build query
        $query = Unit::with('tipe_unit');

        // Filter berdasarkan prioritas: yayasan_id > unit_id
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan semua unit yang terkait dengan yayasan tersebut
            $query->where('yayasan_id', Auth::user()->yayasan_id);
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan unit tersebut saja
            $query->where('id', Auth::user()->unit_id);
        }
        // Search functionality across all columns
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_unit', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%")
                    ->orWhere('nama_pimpinan_unit', 'like', "%{$search}%")
                    ->orWhereHas('tipe_unit', function($q) use ($search) {
                        $q->where('nama_tipe_unit', 'like', "%{$search}%");
                    });
            });
        }

        // Paginate results
        $unit = $query->paginate(15)->appends($request->except('page'));

        $headers = [
            'No',
            'Nama Yayasan',
            'Tipe Unit',
            'Nama Unit',
            'Code Unit',
            'No Telp',
            'Email',
            'Alamat',
            'Website',
            'Status',
            'Action'
        ];

        return view('pages.data_master.unit.unit', compact('unit','headers','units'));
    }
    public function create()
    {
        $unit = Unit::where('id', Auth::user()->unit_id)->first();

        // Filter yayasan berdasarkan user access
        if(Auth::user()->yayasan_id){
            // Jika user punya yayasan_id, hanya tampilkan yayasan tersebut
            $yayasan = Yayasan::where('status', '1')->where('id', Auth::user()->yayasan_id)->get();
        } elseif(Auth::user()->unit_id){
            // Jika user punya unit_id, tampilkan yayasan yang terkait dengan unit tersebut
            $yayasan = Yayasan::where('status', '1')->when($unit->yayasan_id, function ($query, $yayasanId) {
                $query->where('id', $yayasanId);
            })->get();
        } else {
            // Admin bisa melihat semua yayasan
            $yayasan = Yayasan::where('status', '1')->get();
        }

        $tipeunit = Tipeunit::where('status','1')->get();
        return view('pages.data_master.unit.unit_create', compact('yayasan','tipeunit'));
    }
    public function store(Request $request)
    {

        $request->validate([
            'nama_unit' => 'required|string|max:255|unique:units,nama_unit',
            'image' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'alamat' => 'nullable|string',
            'website' => 'nullable|string',
            'status' => 'required|in:0,1',
            'tipe_unit_id' => 'nullable',
            'nama_pimpinan_unit' => 'nullable|string',
        ]);



        $centralCode = $request->code;
        if (empty($centralCode)) {
            $centralCode = 'U' . strtoupper(Str::random(7));
        }
        Unit::create([
            'nama_unit' => $request->nama_unit,
            'code' => 'U'.$centralCode,
            'image' => $request->image,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'website' => $request->website,
            'yayasan_id' => $request->yayasan_id,
            'status' => $request->status,
            'tipe_unit_id' => $request->tipe_unit_id,
            'nama_pimpinan_unit' => $request->nama_pimpinan_unit,
        ]);

        return redirect()->route('unit.index')
            ->with('success', 'Data berhasil ditambahkan dengan Central Code: ' . $centralCode);
    }
    public function edit($id)
    {
        $unit = Unit::findOrFail($id);

        // Filter yayasan berdasarkan user access
        if(Auth::user()->yayasan_id){
            // Jika user punya yayasan_id, hanya tampilkan yayasan tersebut
            $yayasan = Yayasan::where('status', '1')->where('id', Auth::user()->yayasan_id)->get();
        } elseif(Auth::user()->unit_id){
            // Jika user punya unit_id, tampilkan yayasan yang terkait dengan unit tersebut
            $yayasan = Yayasan::where('status', '1')->when($unit->yayasan_id, function ($query, $yayasanId) {
                $query->where('id', $yayasanId);
            })->get();
        } else {
            // Admin bisa melihat semua yayasan
            $yayasan = Yayasan::where('status', '1')->get();
        }

        $tipeunit = Tipeunit::where('status','1')->get();

        return view('pages.data_master.unit.unit_create', compact('unit','yayasan','tipeunit'));
    }
    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->update($request->all());
        return redirect()->route('unit.index')
            ->with('success', 'Data berhasil diupdate');
    }
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();
        return redirect()->route('unit.index')
            ->with('success', 'Data berhasil dihapus');
    }
    public function show($id)
    {
        $unit = Unit::findOrFail($id);
        $show = true;

        // Filter yayasan berdasarkan user access
        if(Auth::user()->yayasan_id){
            // Jika user punya yayasan_id, hanya tampilkan yayasan tersebut
            $yayasan = Yayasan::where('status', '1')->where('id', Auth::user()->yayasan_id)->get();
        } elseif(Auth::user()->unit_id){
            // Jika user punya unit_id, tampilkan yayasan yang terkait dengan unit tersebut
            $yayasan = Yayasan::with('units')
                ->when(Auth::user()->unit_id, function ($query, $unitId) {
                    $query->whereHas('units', function ($q) use ($unitId) {
                        $q->where('id', $unitId);
                    });
                })->where('status', '1')->get();
        } else {
            // Admin bisa melihat semua yayasan
            $yayasan = Yayasan::where('status', '1')->get();
        }

        $tipeunit = Tipeunit::where('status','1')->get();

        return view('pages.data_master.unit.unit_create', compact('unit','show','yayasan','tipeunit'));
    }
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $file = $request->file('file');
        $filename = Str::random(15) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/unit', $filename, 'public');

        return response()->json([
            'success' => true,
            'filepath' => 'storage/' . $path
        ]);
    }

    public function listkelas($id)
    {
        $kelas = Kelas::where('unit_id', $id)->where('status', '1')->get();
        return response()->json($kelas);
    }
    public function getKelasByUnit($unitId)
    {
        $kelas = \App\Models\Kelas::where('unit_id', $unitId)
            ->where('status', '1')
            ->select('id', 'nama_kelas')
            ->get();

        return response()->json($kelas);
    }
}
