<?php

namespace App\Http\Controllers;

use App\Models\Kategoritagihan;
use App\Models\Unit;
use App\Models\Tahun_ajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KategoritagihanController extends Controller
{
    public function index()
    {
        $kategoritagihans = Kategoritagihan::with('unit')
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                // Filter kategori berdasarkan unit_id dari relasi 'unit'
                $query->whereHas('unit', function ($q) use ($unitId) {
                    $q->where('id', $unitId);
                });
            })
            ->get();

        $headers = [
            'No',
            'Nama Unit',
            'Nama Kategori',
            'Kode Kategori',
            'Biaya Tagihan',
            'Status',
            'Action'
        ];

        return view('pages.data_master.kategori.kategori', compact('kategoritagihans', 'headers'));
    }

    public function create()
    {
        $units = Unit::when(Auth::user()->unit_id,function ($query,$unit_id){
            $query->where('id',$unit_id);
        })
            ->where('status','1')
            ->get();
        $tahun_ajaran = Tahun_ajaran::orderBy('id', 'desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();

        return view('pages.data_master.kategori.kategori_create', compact('units', 'tahun_ajaran', 'tahun_ajaran_selected'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori'    => 'required|string|max:255',
            'kode_kategori'    => 'required|string|max:100|unique:kategoritagihans,kode_kategori',
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
            'kode_kategori'    => 'required|string|max:100|unique:kategoritagihans,kode_kategori,' . $kategoritagihan->id,
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
}
