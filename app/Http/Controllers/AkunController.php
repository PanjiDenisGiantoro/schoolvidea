<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Akun;
use Illuminate\Support\Facades\Auth;

class AkunController extends Controller
{
    public function index(Request $request)
    {
        $units = \App\Models\Unit::all();

        // Build query
        $query = Akun::with('parent', 'unit');

        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan akun dari semua unit di yayasan tersebut
            $query->whereHas('unit', function($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            });
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan akun dari unit tersebut saja
            $query->where('unit_id', Auth::user()->unit_id);
        } elseif ($request->filled('unit_id')) {
            // Admin user filtering by unit
            $query->where('unit_id', $request->unit_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_akun', 'like', "%{$search}%")
                  ->orWhere('nama_akun', 'like', "%{$search}%")
                  ->orWhere('kategori_akun', 'like', "%{$search}%")
                  ->orWhere('tipe', 'like', "%{$search}%")
                  ->orWhereHas('parent', function($q) use ($search) {
                      $q->where('nama_akun', 'like', "%{$search}%");
                  })
                  ->orWhereHas('unit', function($q) use ($search) {
                      $q->where('nama_unit', 'like', "%{$search}%");
                  });
            });
        }

        // Paginate results
        $akuns = $query->paginate(15)->appends($request->except('page'));

        $headers = [
            'No',
            'Kode Akun',
            'Nama Akun',
            'Kategori',
            'Tipe',
            'Parent',
            'Unit',
            'Status',
            'Action'
        ];

        return view('pages.data_master.akun.akun', compact('akuns', 'headers', 'units'));
    }
    private function buildAkunOptions(
        $akunList,
        $parentId = null,
        $level = 0,
        $excludeId = null
    ): array {
        $options = [];

        foreach ($akunList->where('parent_id', $parentId) as $akun) {
            if ($akun->id == $excludeId) continue;

            $options[] = [
                'id' => $akun->id,
                'nama' => str_repeat('--', $level) . ' ' . $akun->nama_akun
            ];

            // recursive untuk children
            $children = $this->buildAkunOptions($akunList, $akun->id, $level + 1, $excludeId);
            if ($children) {
                $options = array_merge($options, $children);
            }
        }

        return $options; // wajib mengembalikan array
    }

    public function create()
    {
        // Filter berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan data dari semua unit di yayasan tersebut
            $parents = Akun::whereHas('unit', function($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->where('status','1')->get();
            $units = \App\Models\Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status','1')->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan data dari unit tersebut saja
            $parents = Akun::where('unit_id', Auth::user()->unit_id)->where('status','1')->get();
            $units = \App\Models\Unit::where('id', Auth::user()->unit_id)->where('status','1')->get();
        } else {
            // Admin bisa melihat semua
            $parents = Akun::where('status','1')->get();
            $units = \App\Models\Unit::where('status','1')->get();
        }

        $akunOptions = $this->buildAkunOptions($parents, null, 0);

        return view('pages.data_master.akun.akun_create', compact('parents','units','akunOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_akun' => 'required|unique:akuns,kode_akun',
            'nama_akun' => 'required',
            'tipe' => 'required|in:ASET,LIABILITAS,EKUITAS,PENDAPATAN,BEBAN',
            'kategori_akun' => 'nullable'
        ]);

        Akun::create([
            'kode_akun' => $request->kode_akun,
            'nama_akun' => $request->nama_akun,
            'tipe' => $request->tipe,
            'parent_id' => $request->parent_id,
            'unit_id' => $request->unit_id,
            'status' => $request->status ?? '1',
            'kategori_akun' => $request->kategori_akun
        ]);

        return redirect()->route('akun.index')
            ->with('success', 'Data akun berhasil ditambahkan');
    }

    public function edit($id)
    {
        $akun = Akun::findOrFail($id);

        // Filter berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan data dari semua unit di yayasan tersebut
            $parents = Akun::where('id', '!=', $id)
                ->whereHas('unit', function($q) {
                    $q->where('yayasan_id', Auth::user()->yayasan_id);
                })
                ->where('status','1')->get();
            $units = \App\Models\Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status','1')->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan data dari unit tersebut saja
            $parents = Akun::where('id', '!=', $id)
                ->where('unit_id', Auth::user()->unit_id)
                ->where('status','1')->get();
            $units = \App\Models\Unit::where('id', Auth::user()->unit_id)->where('status','1')->get();
        } else {
            // Admin bisa melihat semua
            $parents = Akun::where('id', '!=', $id)->where('status','1')->get();
            $units = \App\Models\Unit::where('status','1')->get();
        }

        $akunOptions = $this->buildAkunOptions($parents, null, 0);

        return view('pages.data_master.akun.akun_create', compact('akun', 'parents', 'units','akunOptions'));
    }

    public function update(Request $request, $id)
    {
        $akun = Akun::findOrFail($id);
        $request->validate([
            'kode_akun' => 'required|unique:akuns,kode_akun,' . $akun->id,
            'nama_akun' => 'required',
            'tipe' => 'required|in:ASET,LIABILITAS,EKUITAS,PENDAPATAN,BEBAN',
            'kategori_akun' => 'nullable'
        ]);

        $akun->update([
            'kode_akun' => $request->kode_akun,
            'nama_akun' => $request->nama_akun,
            'tipe' => $request->tipe,
            'parent_id' => $request->parent_id,
            'unit_id' => $request->unit_id,
            'status' => $request->status ?? '1',
            'kategori_akun' => $request->kategori_akun
        ]);

        return redirect()->route('akun.index')
            ->with('success', 'Data akun berhasil diupdate');
    }

    public function destroy($id)
    {
        $akun = Akun::findOrFail($id);
        $akun->delete();

        return redirect()->route('akun.index')
            ->with('success', 'Data akun berhasil dihapus');
    }

    public function show($id)
    {
        $akun = Akun::findOrFail($id);

        // Filter berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan data dari semua unit di yayasan tersebut
            $parents = Akun::where('id', '!=', $id)
                ->whereHas('unit', function($q) {
                    $q->where('yayasan_id', Auth::user()->yayasan_id);
                })
                ->where('status','1')->get();
            $units = \App\Models\Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status','1')->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan data dari unit tersebut saja
            $parents = Akun::where('id', '!=', $id)
                ->where('unit_id', Auth::user()->unit_id)
                ->where('status','1')->get();
            $units = \App\Models\Unit::where('id', Auth::user()->unit_id)->where('status','1')->get();
        } else {
            // Admin bisa melihat semua
            $parents = Akun::where('id', '!=', $id)->where('status','1')->get();
            $units = \App\Models\Unit::where('status','1')->get();
        }

        $akunOptions = $this->buildAkunOptions($parents, null, 0);

        $show = true;
        return view('pages.data_master.akun.akun_create', compact('akun','show','parents','units','akunOptions'));
    }
}
