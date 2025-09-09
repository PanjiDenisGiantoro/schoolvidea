<?php

namespace App\Http\Controllers;

use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LembagaunitController extends Controller
{
    public function index(){
        $lembagaunit = Yayasan::get();

        $headers = [
            'No',
            'Nama Yayasan',
            'Central Code Yayasan',
            'image',
            'No Telp',
            'Email',
            'Alamat',
            'Website',
            'Status',
            'Action'
        ];

            return view('pages.data_master.kode_lembaga.kode_lembaga', compact('lembagaunit','headers'));
    }
    public function create()
    {
        return view('pages.data_master.kode_lembaga.kode_lembaga_create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'nama_yayasan' => 'required|string|max:255',
            'no_hp'        => 'nullable|string|max:20',
            'email'        => 'nullable|email',
            'alamat'       => 'nullable|string',
            'website'      => 'nullable|string',
            'image'        => 'nullable|string',
        ]);

        // generate central_code 7 huruf acak
        $centralCode = strtoupper(Str::random(7));

        Yayasan::create([
            'nama_yayasan' => $request->nama_yayasan,
            'central_code' => 'U'.$centralCode,
            'no_hp'        => $request->no_hp,
            'email'        => $request->email,
            'alamat'       => $request->alamat,
            'website'      => $request->website,
            'image'        => $request->image,
            'status'       => $request->status,
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
}
