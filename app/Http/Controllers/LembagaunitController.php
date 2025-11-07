<?php

namespace App\Http\Controllers;

use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LembagaunitController extends Controller
{
    public function index(Request $request){
        $units = \App\Models\Unit::all();

        // Build query
        $query = Yayasan::query();

        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan yayasan tersebut saja
            $query->where('yayasans.id', Auth::user()->yayasan_id);
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, join dengan units dan tampilkan yayasan yang terkait
            $query->join('units', 'units.yayasan_id', '=', 'yayasans.id')
                  ->where('units.id', Auth::user()->unit_id)
                  ->select('yayasans.*'); // Select semua kolom dari yayasans
        } elseif ($request->filled('unit_id')) {
            // Jika admin memilih unit tertentu, filter berdasarkan unit tersebut
            $query->join('units', 'units.yayasan_id', '=', 'yayasans.id')
                  ->where('units.id', $request->unit_id)
                  ->select('yayasans.*');
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_yayasan', 'like', "%{$search}%")
                  ->orWhere('central_code', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('website', 'like', "%{$search}%")
                  ->orWhere('nama_pimpinan', 'like', "%{$search}%");
            });
        }

        // Paginate results
        $lembagaunit = $query->paginate(15)->appends($request->except('page'));

        $headers = [
            'No',
            'Nama Yayasan',
            'Central Code Yayasan',
            'No Telp',
            'Email',
            'Alamat',
            'Website',
            'Nama Pimpinan',
            'Status',
            'Action'
        ];

        return view('pages.data_master.kode_lembaga.kode_lembaga', compact('lembagaunit','headers','units'));
    }
    public function create()
    {
        // Jika user punya yayasan_id, tidak boleh buat yayasan baru
        if(Auth::user()->yayasan_id){
            return redirect()->route('lembagaunit.index')
                ->with('danger', 'Anda sudah terdaftar di yayasan, tidak dapat membuat yayasan baru');
        }

        if(Auth::user()->unit_id){
            $ceknotdoubleunit = Yayasan::where('unit_id', Auth::user()->unit_id)->first();
            if($ceknotdoubleunit){
                return redirect()->route('lembagaunit.index')
                    ->with('danger', 'Unit ini sudah memiliki yayasan, silahkan hubungi admin');
            }
        }

        return view('pages.data_master.kode_lembaga.kode_lembaga_create');
    }
    public function store(Request $request)
    {


            $request->validate([
            'nama_yayasan' => 'required|string|max:255',
            'central_code' => 'nullable|string|max:50|unique:yayasans,central_code', // optional
            'no_hp'        => 'nullable|string|max:20',
            'email'        => 'nullable|email',
            'alamat'       => 'nullable|string',
            'website'      => 'nullable|string',
            'image'        => 'nullable|string',
            'status'       => 'required|in:0,1',
            'nama_pimpinan' => 'nullable|string|max:255',
        ]);

        $centralCode = $request->central_code;
        if (empty($centralCode)) {
            $centralCode = 'U' . strtoupper(Str::random(7));
        }

        Yayasan::create([
            'nama_yayasan' => $request->nama_yayasan,
            'central_code' => $centralCode,
            'no_hp'        => $request->no_hp,
            'email'        => $request->email,
            'alamat'       => $request->alamat,
            'website'      => $request->website,
            'image'        => $request->image,
            'status'       => $request->status,
            'nama_pimpinan' => $request->nama_pimpinan,
            'unit_id' => Auth::user()->unit_id ? Auth::user()->unit_id : null,
        ]);

        return redirect()->route('lembagaunit.index')
            ->with('success', 'Data berhasil ditambahkan dengan Central Code: ' . $centralCode);
    }

    public function edit($id)
    {
        $lembagaunit = Yayasan::findOrFail($id);
        return view('pages.data_master.kode_lembaga.kode_lembaga_create', compact('lembagaunit'));
    }
    public function update(Request $request, $id)
    {
        $lembagaunit = Yayasan::findOrFail($id);
        $request->merge([
            'unit_id' => Auth::user()->unit_id ? Auth::user()->unit_id : null,
        ]);
        $lembagaunit->update($request->all());
        return redirect()->route('lembagaunit.index')
            ->with('success', 'Data berhasil diupdate');
    }
    public function destroy($id)
    {
        $lembagaunit = Yayasan::findOrFail($id);
        $lembagaunit->delete();
        return redirect()->route('lembagaunit.index')
            ->with('success', 'Data berhasil dihapus');
    }
    public function show($id)
    {
            $lembagaunit = Yayasan::findOrFail($id);
            $show = true;
        return view('pages.data_master.kode_lembaga.kode_lembaga_create', compact('lembagaunit','show'));
    }
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,gif|max:1024',
        ]);

        $file = $request->file('file');
        $randomName = substr(str_shuffle('0123456789'), 0, 15);
        $extension = $file->getClientOriginalExtension();
        $filename = $randomName . '.' . $extension;
        $path = $file->storeAs('uploads/lembagaunit', $filename, 'public');

        return response()->json([
            'success' => true,
            'filepath' => 'storage/' . $path,
            'filename' => $filename
        ]);
    }
}
