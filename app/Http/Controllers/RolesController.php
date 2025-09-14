<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roles_petugas;

class RolesController extends Controller
{
    public function index(){
        $roles = Roles_petugas::get();
        $headers = [
            'No',
            'Nama Role',
            'Action'
        ];

        return view('pages.data_master.roles.roles', compact('roles','headers'));
    }
    public function create()
    {
        return view('pages.data_master.roles.roles_create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        Roles_petugas::create([
            'name' => strtolower($request->name),
            'guard_name' =>'web',
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Data berhasil ditambahkan dengan Tahun Ajaran' . $request->tahun_ajaran);
    }
    public function edit($id)
    {
        $roles = Roles_petugas::findOrFail($id);
        return view('pages.data_master.roles.roles_create', compact('roles'));
    }
    public function update(Request $request, $id)
    {
        $roles = Roles_petugas::findOrFail($id);
        $data = $request->all();
        $data['name'] = strtolower($request->name);
        $roles->update($data);
        return redirect()->route('roles.index')
            ->with('success', 'Data berhasil diupdate');
    }
    public function destroy($id)
    {
        $roles = Roles_petugas::findOrFail($id);
        $roles->delete();
        return redirect()->route('roles.index')
            ->with('success', 'Data berhasil dihapus');
    }
    public function show($id)
    {
        $roles = Roles_petugas::findOrFail($id);
        $show = true;
        return view('pages.data_master.roles.roles_create', compact('roles','show'));
    }

}
