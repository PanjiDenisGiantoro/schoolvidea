<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Officer;
use App\Models\Positions;
use App\Models\Roles_petugas;
use App\Models\Tahun_ajaran;
use App\Models\Unit;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class OfficerController extends Controller
{
    public function index()
    {
        $officer = User::with('officer.unit', 'roles')

            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id', $unitId);
            })
            ->whereHas('roles', function ($query) {
                $query->whereNotIn('name', [ 'siswa', 'admin', 'user']);
            })
            ->get();
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

        return view('pages.data_master.officer.officer', compact('officer', 'headers'));
    }

    public function create()
    {
        $units = Unit::all();
        $roles = Roles_petugas::all();
        $jurusans = Jurusan::all();
        $tahun_ajaran = Tahun_ajaran::orderBy('id', 'desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();
        $positions = Positions::all();

        // Ambil logo unit pertama (atau kosong)
        $logoUnit = $units->first()->image ?? null;

        return view('pages.data_master.officer.officer_create', compact(
            'units',
            'roles',
            'jurusans',
            'tahun_ajaran',
            'tahun_ajaran_selected',
            'logoUnit',
            'positions'
        ));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6', // ✅ tambah confirmed
            'role_id' => 'required|exists:roles,id',       // ✅ validasi harus ada di Spatie roles
            'image' => 'nullable|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'jurusan' => 'required|array|min:1',  // Make sure at least one jurusan is selected
            'no_hp' => 'nullable|string|max:20',
            'unit_id' => 'required|exists:units,id',
            'rfid_no' => 'nullable|string|max:255',
            'nip'             => 'required|string|max:50|unique:officers,nip',
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
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat user baru
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'rfid_no' => $request->rfid_no,
                'unit_id' => $request->unit_id,
            ]);

            $rolePetugas = Roles_petugas::findOrFail($request->role_id);
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );
            $user->assignRole($roleSpatie->name);

            $tahunajaran = Tahun_ajaran::where('status','1')->orderBy('id', 'desc')->first();
            if(empty($tahunajaran)){
                return redirect()->route('officer.index')->with('error', 'Tahun Ajaran Tidak Ditemukan');
            }

            $officer =   Officer::create([
                'nip'             => $request->nip,
                'image'           => $request->image,
                'tempat_lahir'    => $request->tempat_lahir,
                'no_hp'           => $request->no_hp,
                'unit_id'         => $request->unit_id,
                'tahun_ajaran_id' => $tahunajaran->id,
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
                'jurusan' => json_encode($request->jurusan),  // Menyimpan sebagai JSON
                'va_guru'         => $request->va_guru,
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
        $units = Unit::all();
        $roles = Roles_petugas::all();
        $jurusans = Jurusan::all();
        $tahun_ajaran = Tahun_ajaran::orderBy('id', 'desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();
        $positions = Positions::all();


        // Ambil logo dari unit milik officer
        $logoUnit = $officer->unit->image ?? null;

        return view('pages.data_master.officer.officer_create', compact(
            'officer',
            'units',
            'roles',
            'jurusans',
            'tahun_ajaran',
            'tahun_ajaran_selected',
            'logoUnit',
            'positions'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'role_id' => 'required',       // ✅ validasi harus ada di Spatie roles
            'image' => 'nullable|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'unit_id' => 'required',
            'tahun_ajaran_id' => 'required',
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
        ]);

        DB::beginTransaction();
        try {
            $officer = Officer::findOrFail($id);
            $user    = $officer->user;

            // Update user
            $user->update([
                'name'  => $request->name,
                'email' => $request->email,
                'password' => $request->password ? bcrypt($request->password) : $user->password,
                'rfid_no' => $request->rfid_no,
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
                'iamge'           => $request->image,
                'tempat_lahir'    => $request->tempat_lahir,
                'no_hp'           => $request->no_hp,
                'unit_id'         => $request->unit_id,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'role_id'         => $rolePetugas->id,
                'nik'             => $request->nik,
                'jenis_kelamin'   => $request->jenis_kelamin,
                'agama'           => $request->agama,
                'tanggal_lahir'   => $request->tanggal_lahir,
                'alamat'          => $request->alamat,
                'bank'            => $request->bank,
                'no_rekening'     => $request->no_rekening,
                'no_kartu_rfid'   => $request->no_kartu_rfid,
                'qr_code'         => $request->qr_code,
                'jurusan' => json_encode($request->jurusan),  // Menyimpan sebagai JSON
                'va_guru'         => $request->va_guru,
            ]);

            DB::commit();
            return redirect()->route('officer.index')->with('success', 'Officer berhasil diupdate');
        } catch (\Exception $e) {
//            dd($e->getMessage());
            DB::rollBack();
            return back()->with('error','Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        // $id di sini adalah user_id
        $officer = Officer::where('user_id', $id)->first();

        if ($officer) {
            $officer->delete();
        }

        $user = User::find($id);
        if ($user) {
            $user->delete();
        }

        return redirect()->back()->with('success', 'Data user dan officer berhasil dihapus.');
    }



    public function show($id)
    {
        $officer = Officer::with(['user', 'rolePetugas', 'unit', 'tahunAjaran'])->findOrFail($id);
        $show = true;

        return view('pages.data_master.officer.officer_create', compact('officer', 'show'));
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

}
