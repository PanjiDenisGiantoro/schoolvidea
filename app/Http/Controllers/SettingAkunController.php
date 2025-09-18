<?php

namespace App\Http\Controllers;

use App\Models\setting_akun;
use App\Models\Unit;
use App\Models\Akun;
use Illuminate\Http\Request;

class SettingAkunController extends Controller
{
    public function index()
    {
        $settings = setting_akun::with(['unit', 'akun'])->get();
        $headers = [
            'No',
            'Nama Setting',
            'Akun',
            'Debit',
            'Kredit',
            'Keterangan',
            'Unit',
            'Kategori',
            'Status',
            'Action'
        ];
        return view('pages.data_master.setting_akun.setting_akun', compact('settings', 'headers'));
    }

    public function create()
    {
        $units = Unit::isactive()->get();
        $akuns = Akun::all();

        return view('pages.data_master.setting_akun.setting_akun_create', compact('units','akuns'));
    }


    public function edit($id)
    {
        $setting = setting_akun::findOrFail($id);
        $units = Unit::isactive()->get();
        $akuns = Akun::all();

        return view('pages.data_master.setting_akun.setting_akun_create', compact('setting','units','akuns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_setting' => 'required|string|max:255',
            'akun_id'      => 'nullable|exists:akuns,id',
            'debit'        => 'nullable|integer|in:0,1',
            'keterangan'   => 'nullable|string',
            'unit_id'      => 'nullable|exists:units,id',
            'status'       => 'required|in:0,1',
            'kategori'     => 'required|string|max:255',
        ]);

        // Hitung otomatis kredit
        $kredit = null;
        if ($request->filled('debit')) {
            $kredit = $request->debit == 1 ? 0 : 1;
        }

        setting_akun::create([
            'nama_setting' => $request->nama_setting,
            'akun_id'      => $request->akun_id,
            'debit'        => $request->debit,
            'kredit'       => $kredit,
            'keterangan'   => $request->keterangan,
            'unit_id'      => $request->unit_id,
            'status'       => $request->status,
            'kategori'     => $request->kategori,
        ]);

        return redirect()->route('setting_akun.index')
            ->with('success', 'Setting Akun berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $setting = setting_akun::findOrFail($id);

        $request->validate([
            'nama_setting' => 'required|string|max:255',
            'akun_id'      => 'nullable|exists:akuns,id',
            'debit'        => 'nullable|integer|in:0,1',
            'keterangan'   => 'nullable|string',
            'unit_id'      => 'nullable|exists:units,id',
            'status'       => 'required|in:0,1',
            'kategori'     => 'required|string|max:255',
        ]);

        // Hitung otomatis kredit
        $kredit = null;
        if ($request->filled('debit')) {
            $kredit = $request->debit == 1 ? 0 : 1;
        }

        $setting->update([
            'nama_setting' => $request->nama_setting,
            'akun_id'      => $request->akun_id,
            'debit'        => $request->debit,
            'kredit'       => $kredit,
            'keterangan'   => $request->keterangan,
            'unit_id'      => $request->unit_id,
            'status'       => $request->status,
            'kategori'     => $request->kategori,
        ]);

        return redirect()->route('setting_akun.index')
            ->with('success', 'Setting Akun berhasil diperbarui');
    }


    public function destroy($id)
    {
        $setting = setting_akun::findOrFail($id);
        $setting->delete();

        return redirect()->route('setting-akun.index')
            ->with('success', 'Setting Akun berhasil dihapus');
    }

    public function show($id)
    {
        $setting = setting_akun::with(['unit', 'akun'])->findOrFail($id);
        $units = Unit::isactive()->get();
        $akuns = Akun::all();
        $show = true;

        return view('pages.data_master.setting_akun.setting_akun_create', compact(
            'setting','show','units','akuns'
        ));
    }
}
