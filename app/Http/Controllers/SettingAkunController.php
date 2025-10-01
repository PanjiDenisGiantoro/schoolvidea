<?php

namespace App\Http\Controllers;

use App\Models\setting_akun;
use App\Models\Unit;
use App\Models\Akun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingAkunController extends Controller
{
    public function index()
    {
        $settings = setting_akun::with(['unit', 'akun'])
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id', $unitId);
            })
            ->get();
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
        $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('id', $unitId);
        })
            ->where('status','1')
            ->get();
        $akuns = Akun::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('id', $unitId);
        })
            ->where('status','1')
            ->get();


        $akunOptions = $this->buildAkunOptions($akuns, null, 0);

        return view('pages.data_master.setting_akun.setting_akun_create', compact('units','akuns','akunOptions'));
    }


    public function edit($id)
    {
        $setting = setting_akun::findOrFail($id);
        $units = Unit::isactive()->get();
        $akuns = Akun::all();
        $akunOptions = $this->buildAkunOptions($akuns, null, 0);

        return view('pages.data_master.setting_akun.setting_akun_create', compact('setting','units','akuns','akunOptions'));
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
