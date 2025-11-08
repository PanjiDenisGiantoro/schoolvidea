<?php

namespace App\Http\Controllers;

use App\Models\setting_akun;
use App\Models\Unit;
use App\Models\Akun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingAkunController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::all();

        // Build query - include akunDebit and akunKredit
        $query = setting_akun::with(['unit', 'akun', 'akunDebit', 'akunKredit']);

        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan setting dari semua unit di yayasan tersebut
            $query->whereHas('unit', function($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            });
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan setting dari unit tersebut saja
            $query->where('unit_id', Auth::user()->unit_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_setting', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%")
                  ->orWhereHas('akun', function($q) use ($search) {
                      $q->where('nama_akun', 'like', "%{$search}%")
                        ->orWhere('kode_akun', 'like', "%{$search}%");
                  })
                  ->orWhereHas('unit', function($q) use ($search) {
                      $q->where('nama_unit', 'like', "%{$search}%");
                  });
            });
        }

        // Paginate results
        $settings = $query->paginate(15)->appends($request->except('page'));

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
        return view('pages.data_master.setting_akun.setting_akun', compact('settings', 'headers', 'units'));
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
        // Filter units berdasarkan user access
        if (Auth::user()->yayasan_id) {
            $units = Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->get();
            $akuns = Akun::whereHas('unit', function($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->where('status', '1')->get();
        } elseif (Auth::user()->unit_id) {
            $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('id', $unitId);
            })->where('status','1')->get();
            $akuns = Akun::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id', $unitId);
            })->where('status','1')->get();
        } else {
            $units = Unit::where('status', '1')->get();
            $akuns = Akun::where('status', '1')
                ->get();
        }


        $akunOptions = $this->buildAkunOptions($akuns, null, 0);

        return view('pages.data_master.setting_akun.setting_akun_create', compact('units','akuns','akunOptions'));
    }


    public function edit($id)
    {
        $setting = setting_akun::findOrFail($id);

        if (Auth::user()->yayasan_id) {
            $units = Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->get();
            $akuns = Akun::whereHas('unit', function($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->where('status', '1')->get();
        } elseif (Auth::user()->unit_id) {
            $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('id', $unitId);
            })->where('status','1')->get();
            $akuns = Akun::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id', $unitId);
            })->where('status','1')->get();
        } else {
            $units = Unit::where('status', '1')->get();
            $akuns = Akun::where('status', '1')
                ->get();
        }
        $akunOptions = $this->buildAkunOptions($akuns, null, 0);

        return view('pages.data_master.setting_akun.setting_akun_create', compact('setting','units','akuns','akunOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_setting'     => 'required|string|max:255',
            'akun_id'          => 'nullable',
//            'akun_debit_id'    => 'required|exists:akuns,id',
//            'akun_kredit_id'   => 'required|exists:akuns,id',
            'keterangan'       => 'nullable|string',
            'unit_id'          => 'nullable',
            'status'           => 'required|in:0,1',
            'kategori'         => 'required|string|max:255',
        ]);

        // Validasi akun debit dan kredit tidak boleh sama
//        if ($request->akun_debit_id == $request->akun_kredit_id) {
//            return back()->withErrors(['akun_kredit_id' => 'Akun Debit dan Kredit tidak boleh sama'])->withInput();
//        }

        setting_akun::create([
            'nama_setting'   => $request->nama_setting,
            'akun_id'        => $request->akun_id,
            'keterangan'     => $request->keterangan,
            'unit_id'        => $request->unit_id,
            'status'         => $request->status,
            'kategori'       => $request->kategori,
        ]);

        return redirect()->route('setting_akun.index')
            ->with('success', 'Setting Akun berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $setting = setting_akun::findOrFail($id);

        $request->validate([
            'nama_setting'     => 'required|string|max:255',
            'akun_id'          => 'nullable|exists:akuns,id',
            'keterangan'       => 'nullable|string',
            'unit_id'          => 'nullable|exists:units,id',
            'status'           => 'required|in:0,1',
            'kategori'         => 'required|string|max:255',
        ]);

        // Validasi akun debit dan kredit tidak boleh sama
//        if ($request->akun_debit_id == $request->akun_kredit_id) {
//            return back()->withErrors(['akun_kredit_id' => 'Akun Debit dan Kredit tidak boleh sama'])->withInput();
//        }

        $setting->update([
            'nama_setting'   => $request->nama_setting,
            'akun_id'        => $request->akun_id,
            'keterangan'     => $request->keterangan,
            'unit_id'        => $request->unit_id,
            'status'         => $request->status,
            'kategori'       => $request->kategori,
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
