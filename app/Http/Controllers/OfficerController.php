<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Models\Positions;
use App\Models\Roles_petugas;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfficerController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::all();

        // Build query
        $query = User::with('officer.unit', 'roles')
            ->whereHas('roles', function ($query) {
                $query->whereNotIn('name', ['siswa', 'user']);
            });

        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan officer dari semua unit di yayasan tersebut
            $query->whereHas('officer', function($q) {
                $q->whereHas('unit', function($q2) {
                    $q2->where('yayasan_id', Auth::user()->yayasan_id);
                });
            });
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan officer dari unit tersebut saja
            $query->where('unit_id', Auth::user()->unit_id);
        } elseif ($request->filled('unit_id')) {
            // Admin user filtering by unit
            $query->where('unit_id', $request->unit_id);
        }

        // Search functionality across all columns
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhereHas('officer', function($q) use ($search) {
                      $q->where('nip', 'like', "%{$search}%")
                        ->orWhere('nuptk', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('va_guru', 'like', "%{$search}%")
                        ->orWhere('no_hp', 'like', "%{$search}%");
                  })
                  ->orWhereHas('officer.unit', function($q) use ($search) {
                      $q->where('nama_unit', 'like', "%{$search}%");
                  })
                  ->orWhereHas('roles', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Paginate results
        $officer = $query->paginate(15)->appends($request->except('page'));

        $headers = [
            'No',
            'Nama Unit',
            'Nama',
            'Role',
            'NIP',
            'Email',
            'VA Petugas',
            'Action',
        ];

        return view('pages.data_master.officer.officer', compact('officer', 'headers', 'units'));
    }

    public function create()
    {
        // Filter units berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan semua unit di yayasan tersebut
            $units = Unit::where('status', '1')->where('yayasan_id', Auth::user()->yayasan_id)->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan unit tersebut saja
            $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('id', $unitId);
            })->where('status', '1')->get();
        } else {
            // Admin bisa melihat semua
            $units = Unit::where('status', '1')->get();
        }

        $roles = Roles_petugas::all();
        $positions = Positions::all();

        // Ambil logo unit pertama (atau kosong)
        $logoUnit = $units->first()->image ?? null;

        return view('pages.data_master.officer.officer_create', compact(
            'units',
            'roles',
            'logoUnit',
            'positions'
        ));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6',
            'username' => 'nullable|string|',
            'role_id' => 'required|exists:roles,id',
            'image' => 'nullable|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20|unique:officers,no_hp',
            'unit_id' => 'required|exists:units,id',
            'rfid_no' => 'nullable|string|max:255',
            'nip'             => 'required|string|max:50|unique:officers,nip',
            'nuptk'           => 'nullable|string|max:50|unique:officers,nuptk',
            'nik'             => 'nullable|string|max:50|unique:officers,nik',
            'jenis_kelamin'   => 'nullable',
            'agama'           => 'nullable|string|max:50',
            'tanggal_lahir'   => 'nullable|date',
            'alamat'          => 'nullable|string',
            'bank'            => 'nullable|string|max:100',
            'no_rekening'     => 'nullable|string|max:50|unique:officers,no_rekening',
            'no_kartu_rfid'   => 'nullable|string|max:100|unique:officers,no_kartu_rfid',
            'qr_code'         => 'nullable|string|max:100',
            'va_guru'         => 'nullable|string|max:100|unique:officers,va_guru',
            'position_id'     => 'nullable|',
            'akses_yayasan'   => 'nullable|in:ya,tidak'
        ]);

        DB::beginTransaction();
        try {
            // Tentukan yayasan_id berdasarkan dropdown akses yayasan
            $yayasanId = null;
            if ($request->akses_yayasan === 'ya' && Auth::user()->yayasan_id) {
                $yayasanId = Auth::user()->yayasan_id;
            }

            // 1. Buat user baru
            $user = User::create([
                'name' => $request->name,
                'username' => $request->nip,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'rfid_no' => $request->no_kartu_rfid,
                'unit_id' => $request->unit_id,
                'yayasan_id' => $yayasanId,
            ]);

            $rolePetugas = Roles_petugas::findOrFail($request->role_id);
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );
            $user->assignRole($roleSpatie->name);


            $officer =   Officer::create([
                'nip'             => $request->nip,
                'image'           => $request->image,
                'tempat_lahir'    => $request->tempat_lahir,
                'no_hp'           => $request->no_hp,
                'unit_id'         => $request->unit_id,
                'user_id'         => $user->id,
                'role_id'         => $rolePetugas->id, // ✅ foreign key cocok dengan roles_petugas
                'nuptk'           => $request->nuptk,
                'nik'             => $request->nik,
                'jenis_kelamin'   => $request->jenis_kelamin,
                'agama'           => $request->agama,
                'tanggal_lahir'   => $request->tanggal_lahir,
                'alamat'          => $request->alamat,
                'bank'            => $request->bank,
                'no_rekening'     => $request->no_rekening,
                'no_kartu_rfid'   => $request->no_kartu_rfid,
                'qr_code'         => $request->qr_code,
                'va_guru'         => $request->va_guru,
                'position_id'     => $request->position_id,
                'name'            => $request->name,
            ]);
            DB::commit();

            return redirect()->route('officer.index')
                ->with('success', 'Officer berhasil ditambahkan dengan role ' . $roleSpatie->name);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
    public function edit($id)
    {
        $officer = Officer::with('user')->findOrFail($id);

        // Filter units berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan semua unit di yayasan tersebut
            $units = Unit::where('status', '1')->where('yayasan_id', Auth::user()->yayasan_id)->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan unit tersebut saja
            $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('id', $unitId);
            })->where('status', '1')->get();
        } else {
            // Admin bisa melihat semua
            $units = Unit::where('status', '1')->get();
        }

        $roles = Roles_petugas::all();
        $positions = Positions::all();

        // Ambil logo dari unit milik officer
        $logoUnit = $officer->unit->image ?? null;

        return view('pages.data_master.officer.officer_create', compact(
            'officer',
            'units',
            'roles',
            'logoUnit',
            'positions'
        ));
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|string|min:6',
            'username' => 'required|string|',
            'role_id' => 'required|exists:roles,id',
            'image' => 'nullable|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'unit_id' => 'required|exists:units,id',
            'rfid_no' => 'nullable|string|max:255',
            'nip'             => 'required|string|max:50',
            'nuptk'           => 'nullable|string|max:50',
            'nik'             => 'nullable|string|max:50',
            'jenis_kelamin'   => 'nullable',
            'agama'           => 'nullable|string|max:50',
            'tanggal_lahir'   => 'nullable|date',
            'alamat'          => 'nullable|string',
            'bank'            => 'nullable|string|max:100',
            'no_rekening'     => 'nullable|string|max:50',
            'no_kartu_rfid'   => 'nullable|string|max:100',
            'qr_code'         => 'nullable|string|max:100',
            'va_guru'         => 'nullable|string|max:100',
            'position_id'     => 'nullable|',
            'akses_yayasan'   => 'nullable|in:ya,tidak'
        ]);

        DB::beginTransaction();
        try {
            $officer = Officer::findOrFail($id);
            $user    = $officer->user;

            // Tentukan yayasan_id berdasarkan dropdown akses yayasan
            $yayasanId = null;
            if ($request->akses_yayasan === 'ya' && Auth::user()->yayasan_id) {
                $yayasanId = Auth::user()->yayasan_id;
            }

            // Update user
            $user->update([
                'name'  => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => $request->password ? bcrypt($request->password) : $user->password,
                'rfid_no' => $request->no_kartu_rfid,
                'yayasan_id' => $yayasanId,
            ]);

            // Update role user
            $rolePetugas = Roles_petugas::findOrFail($request->role_id);
            $roleSpatie  = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );

            $user->syncRoles([$roleSpatie->name]);

            // Update officer
            $officer->update([
                'nip'             => $request->nip,
                'image'           => $request->image,
                'tempat_lahir'    => $request->tempat_lahir,
                'no_hp'           => $request->no_hp,
                'unit_id'         => $request->unit_id,
                'nuptk'           => $request->nuptk,
                'nik'             => $request->nik,
                'jenis_kelamin'   => $request->jenis_kelamin,
                'agama'           => $request->agama,
                'tanggal_lahir'   => $request->tanggal_lahir,
                'alamat'          => $request->alamat,
                'bank'            => $request->bank,
                'no_rekening'     => $request->no_rekening,
                'no_kartu_rfid'   => $request->no_kartu_rfid,
                'qr_code'         => $request->qr_code,
                'va_guru'         => $request->va_guru,
                'position_id'     => $request->position_id,
                'name' => $request->name,
            ]);

            DB::commit();
            return redirect()->route('officer.index')->with('success', 'Officer berhasil diupdate');
        } catch (\Exception $e) {
            //            dd($e->getMessage());
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        // $id di sini adalah ID dari tabel officers
        $officer = Officer::find($id);

        if (!$officer) {
            return redirect()->back()->with('error', 'Data officer tidak ditemukan.');
        }

        // Ambil user_id dari officer
        $userId = $officer->user_id;

        // Hapus officer
        $officer->delete();

        // Hapus user berdasarkan user_id
        $user = User::find($userId);
        if ($user) {
            $user->delete();
        }

        return redirect()->back()->with('success', 'Data user dan officer berhasil dihapus.');
    }




    public function show($id)
    {
        $officer = Officer::with(['user', 'rolePetugas', 'unit', 'tahunAjaran'])->findOrFail($id);
        $show = true;

        // Filter units berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan semua unit di yayasan tersebut
            $units = Unit::where('status', '1')->where('yayasan_id', Auth::user()->yayasan_id)->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan unit tersebut saja
            $units = Unit::where('status', '1')->where('id', Auth::user()->unit_id)->get();
        } else {
            // Admin bisa melihat semua
            $units = Unit::all();
        }

        $roles = Roles_petugas::all();
        $positions = Positions::all();

        // Ambil logo dari unit milik officer
        $logoUnit = $officer->unit->image ?? null;
        return view('pages.data_master.officer.officer_create', compact('officer', 'show', 'units', 'roles',   'positions', 'logoUnit'));
    }
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,gif|max:1024',
        ]);

        $file = $request->file('file');
        $filename = Str::random(15) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/officer', $filename, 'public');

        return response()->json([
            'success' => true,
            'filepath' => 'storage/' . $path
        ]);
    }
    public function getByUnit($unitId)
    {
        $officers = Officer::where('unit_id', $unitId)
            ->with('user:id,name')
            ->get(['id', 'user_id']);


        return response()->json($officers);
    }
}
