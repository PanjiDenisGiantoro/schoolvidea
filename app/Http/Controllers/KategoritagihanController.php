<?php

namespace App\Http\Controllers;

use App\Models\Kategoritagihan;
use App\Models\Unit;
use App\Models\Tahun_ajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KategoritagihanController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::all();

        // Build query
        $query = Kategoritagihan::with('unit');

        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan kategori dari semua unit di yayasan tersebut
            $query->whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            });
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan kategori dari unit tersebut saja
            $query->whereHas('unit', function ($q) {
                $q->where('id', Auth::user()->unit_id);
            });
        } elseif ($request->filled('unit_id')) {
            // Admin user filtering by unit
            $query->where('unit_id', $request->unit_id);
        }

        // Search functionality across all columns
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_kategori', 'like', "%{$search}%")
                  ->orWhere('kode_kategori', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhere('biaya_tagihan', 'like', "%{$search}%")
                  ->orWhereHas('unit', function($q) use ($search) {
                      $q->where('nama_unit', 'like', "%{$search}%");
                  });
            });
        }

        // Paginate results
        $kategoritagihans = $query->paginate(15)->appends($request->except('page'));

        $headers = [
            'No',
            'Nama Unit',
            'Nama Kategori',
            'Kode Kategori',
            'Biaya Tagihan',
            'Status',
            'Action'
        ];

        return view('pages.data_master.kategori.kategori', compact('kategoritagihans', 'headers', 'units'));
    }

    public function create()
    {
        // Filter units berdasarkan user access
        if (Auth::user()->yayasan_id) {
            $units = Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->get();
        } elseif (Auth::user()->unit_id) {
            $units = Unit::when(Auth::user()->unit_id,function ($query,$unit_id){
                $query->where('id',$unit_id);
            })->where('status','1')->get();
        } else {
            $units = Unit::where('status', '1')->get();
        }

        $tahun_ajaran = Tahun_ajaran::orderBy('id', 'desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();

        return view('pages.data_master.kategori.kategori_create', compact('units', 'tahun_ajaran', 'tahun_ajaran_selected'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori'    => 'required|string|max:255',
            'kode_kategori'    => 'required|string|max:100',
            'keterangan'       => 'nullable|string',
            'unit_id'          => 'required|exists:units,id',
            'biaya_tagihan'    => 'required|integer|min:0',
            'tahun_ajaran_id'  => 'required|exists:tahun_ajarans,id',
            'status'           => 'required|in:0,1',
        ]);

        Kategoritagihan::create([
            'nama_kategori'    => $request->nama_kategori,
            'kode_kategori'    => $request->kode_kategori,
            'keterangan'       => $request->keterangan,
            'unit_id'          => $request->unit_id,
            'biaya_tagihan'    => $request->biaya_tagihan,
            'tahun_ajaran_id'  => $request->tahun_ajaran_id,
            'status'           => $request->status,
        ]);

        return redirect()->route('kategoritagihan.index')
            ->with('success', 'Kategori tagihan berhasil ditambahkan');
    }

    public function edit($id)
    {
        $kategoritagihan = Kategoritagihan::findOrFail($id);
        $units = Unit::when(Auth::user()->unit_id,function ($query,$unit_id){
            $query->where('id',$unit_id);
        })
            ->where('status','1')
            ->get();
        $tahun_ajaran = Tahun_ajaran::orderBy('id', 'desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();

        return view('pages.data_master.kategori.kategori_create', compact('kategoritagihan', 'units', 'tahun_ajaran', 'tahun_ajaran_selected'));
    }

    public function update(Request $request, $id)
    {
        $kategoritagihan = Kategoritagihan::findOrFail($id);

        $request->validate([
            'nama_kategori'    => 'required|string|max:255',
            'kode_kategori'    => 'required|string|max:100',
            'keterangan'       => 'nullable|string',
            'unit_id'          => 'required|exists:units,id',
            'biaya_tagihan'    => 'required|integer|min:0',
            'tahun_ajaran_id'  => 'required|exists:tahun_ajarans,id',
            'status'           => 'required|in:0,1',
        ]);

        $kategoritagihan->update($request->only([
            'nama_kategori',
            'kode_kategori',
            'keterangan',
            'unit_id',
            'biaya_tagihan',
            'tahun_ajaran_id',
            'status'
        ]));

        return redirect()->route('kategoritagihan.index')
            ->with('success', 'Kategori tagihan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $kategoritagihan = Kategoritagihan::findOrFail($id);
        $kategoritagihan->delete();

        return redirect()->route('kategoritagihan.index')
            ->with('success', 'Kategori tagihan berhasil dihapus');
    }

    public function show($id)
    {
        $kategoritagihan = Kategoritagihan::findOrFail($id);
        $units = Unit::isactive()->get();
        $tahun_ajaran = Tahun_ajaran::orderBy('id', 'desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();
        $show = true;

        return view('pages.data_master.kategori.kategori_create', compact(
            'kategoritagihan', 'show', 'units', 'tahun_ajaran', 'tahun_ajaran_selected'
        ));
    }

    /**
     * Get kategori tagihan by unit and kelas (AJAX endpoint)
     * Used for dependent dropdown in tagihan creation form
     */
    public function getByUnitAndKelas($unitId, $kelasId = null)
    {
        try {
            // Build query with unit filter
            $query = Kategoritagihan::where('unit_id', $unitId)
                ->where('status', '1')
                ->select('id', 'nama_kategori', 'biaya_tagihan', 'kode_kategori');

            // If kelas is provided, you can add additional filtering logic here
            // For now, we return all kategori for the unit
            if ($kelasId) {
                // Optional: Could filter by kelas-specific pricing if you have that relationship
                // For example: ->where('kelas_id', $kelasId)
            }

            $kategori = $query->get();

            return response()->json([
                'success' => true,
                'data' => $kategori,
                'message' => 'Data kategori berhasil diambil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kategori: ' . $e->getMessage()
            ], 500);
        }
    }
}
