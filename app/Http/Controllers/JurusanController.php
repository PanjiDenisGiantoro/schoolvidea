<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Tahun_ajaran;
use App\Models\Unit;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JurusanController extends Controller
{
    public function index(){
        $jurusan = Jurusan::with(['unit' => function ($q) {
            $q->isactive();
        }])
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id', $unitId);
            })
            ->get();
        $headers = [
            'No',
            'Nama Jurusan',
            'Kode Jurusan',
            'Keterangan',
            'Unit',
            'Status',
            'Action'
        ];

        return view('pages.data_master.jurusan.jurusan', compact('jurusan','headers'));
    }
    public function create()
    {
        $yayasan = Yayasan::with('units')
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->whereHas('units', function ($q) use ($unitId) {
                    $q->where('id', $unitId);
                });
            })->active()->get();
        $units = Unit::when(Auth::user()->unit_id,function ($query, $unitId) {
          $query->where('id', $unitId);
        })
            ->where('status','1')->get();

        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();

        return view('pages.data_master.jurusan.jurusan_create', compact('yayasan','units','tahun_ajaran','tahun_ajaran_selected'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'nama_jurusan'    => 'required|string|max:255',
            'kode_jurusan'    => 'required|string|max:100|unique:jurusans,kode_jurusan',
            'keterangan'      => 'nullable|string',
            'unit_id'         => 'required|exists:units,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'status'          => 'required|in:0,1',
        ]);

        Jurusan::create([
            'nama_jurusan'    => $request->nama_jurusan,
            'kode_jurusan'    => $request->kode_jurusan,
            'keterangan'      => $request->keterangan,
            'unit_id'         => $request->unit_id,
            'tahun_ajaran_id' => $request->tahun_ajaran_id,
            'status'          => $request->status,
        ]);

        return redirect()->route('jurusan.index')
            ->with('success', 'Jurusan berhasil ditambahkan');
    }

    public function edit($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        $units = Unit::when(Auth::user()->unit_id,function ($query, $unit_id){
            $query->where('unit_id', $unit_id);
        })
        ->isactive()->get();
        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();

        return view('pages.data_master.jurusan.jurusan_create', compact(
            'jurusan','units','tahun_ajaran','tahun_ajaran_selected'
        ));
    }

    public function update(Request $request, $id)
    {
        $jurusan = Jurusan::findOrFail($id);

        $request->validate([
            'nama_jurusan'    => 'required|string|max:255',
            'kode_jurusan'    => 'required|string|max:100|unique:jurusans,kode_jurusan,' . $jurusan->id,
            'keterangan'      => 'nullable|string',
            'unit_id'         => 'required|exists:units,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'status'          => 'required|in:0,1',
        ]);
        $jurusan->update($request->only([
            'nama_jurusan',
            'kode_jurusan',
            'keterangan',
            'unit_id',
            'tahun_ajaran_id',
            'status'
        ]));

        return redirect()->route('jurusan.index')
            ->with('success', 'Jurusan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        $jurusan->delete();

        return redirect()->route('jurusan.index')
            ->with('success', 'Jurusan berhasil dihapus');
    }

    public function show($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        $units = Unit::isactive()->get();
        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();
        $show = true;

        return view('pages.data_master.jurusan.jurusan_create', compact(
            'jurusan','show','units','tahun_ajaran','tahun_ajaran_selected'
        ));
    }

}
