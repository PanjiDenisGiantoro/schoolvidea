<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Roles_petugas;
use App\Models\Saldo_keuangan;
use App\Models\Siswa;
use App\Models\Tahun_ajaran;
use App\Models\Unit;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SiswaController extends Controller
{
    public function index(){
        $siswa = Siswa::with('unit','kelas','user')
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id',$unitId);
            })
            ->where('status','1')
            ->get();
        $headers = [
            'No',
            'Unit',
            'NISN',
            'Kelas',
            'Nama',
            'VA Siswa',
            'Status',
            'Action'
        ];

        return view('pages.data_master.siswa.siswa', compact('siswa','headers'));
    }
    public function create()
    {
        $yayasan = Yayasan::with('units')
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->whereHas('units', function ($q) use ($unitId) {
                    $q->where('id', $unitId);
                });
            })->where('status','1')->get();
        $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('id', $unitId);
        })->where('status','1')->get();

        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();
        $kelas = Kelas::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('unit_id',$unitId);
        })->where('status','1')->get();

        return view('pages.data_master.siswa.siswa_create', compact('kelas','yayasan','units','tahun_ajaran','tahun_ajaran_selected'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nisn'            => 'required|unique:siswas,nisn',
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255',
            'password'        => 'required|string|min:6',
            'kelas_id'        => 'required',
            'unit_id'         => 'required',
            'tahun_ajaran_id' => 'required',
            'status'          => 'required|in:0,1',
            'rfid_no'         => 'nullable|string|max:255',
            'va_siswa'        => 'nullable|string|max:255',
            'nis'           => 'nullable|string|max:20|unique:siswas,nis',
            'nik'           => 'nullable|string|max:20|unique:siswas,nik',
            'jenis_kelamin' => 'nullable|in:L,P',
            'agama'         => 'nullable|string|max:50',
            'no_hp_ortu'    => 'nullable|string|max:20',
            'nama_ortu'     => 'nullable|string|max:100',
            'bank'          => 'nullable|string|max:100',
            'no_rekening'   => 'nullable|string|max:50',
            'qrcode'        => 'nullable|string',
        ]);

        // ✅ 2. Cek kalau gagal
        if ($validator->fails()) {
            return back()
                ->withErrors($validator) // kirim pesan error ke view
                ->withInput(); // isi form tetap tersimpan
        }

        DB::beginTransaction();
        try {
            // 1️⃣ Buat user baru
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => bcrypt($request->password),
                'rfid_no'  => $request->rfid_no,
                'unit_id'  => $request->unit_id,
            ]);

            // 2️⃣ Ambil role "siswa" dari tabel roles_petugas
            $rolePetugas = Roles_petugas::where('name', 'siswa')->first();

            // 3️⃣ Sinkronkan ke Spatie Role
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );

            // 4️⃣ Assign role ke user
            $user->assignRole($roleSpatie->name);


            // 5️⃣ Buat data siswa
           $siswa =  Siswa::create([
                'nisn'            => $request->nisn,
                'tempat_lahir'    => $request->tempat_lahir,
                'tanggal_lahir'   => $request->tanggal_lahir,
                'no_hp'           => $request->no_hp,
                'image'           => $request->image ?? null,
                'user_id'         => $user->id,
                'unit_id'         => $request->unit_id,
                'kelas_id'        => $request->kelas_id,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'status'          => $request->status,
                'rfid_no'         => $request->rfid_no,
               'va_siswa' => $request->va_siswa,
               'nis' => $request->nis,
               'nik'           => $request->nik,
               'jenis_kelamin' => $request->jenis_kelamin,
               'agama'         => $request->agama,
               'no_hp_ortu'    => $request->no_hp_ortu,
               'nama_ortu'     => $request->nama_ortu,
               'bank'          => $request->bank,
               'no_rekening'   => $request->no_rekening,
               'qrcode'        => $request->qrcode,


            ]);

            $tabungan = Saldo_keuangan::create([
                'user_id' => $user->id,
                'saldo_akhir' => 0,
                'status' => 0
            ]);

            DB::commit();

            return redirect()->route('siswa.index')
                ->with('success', 'Siswa berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('danger' ,$e->getMessage());
        }
    }

    public function edit($id)
    {
        $siswa = Siswa::findOrFail($id);
        $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('id', $unitId);
        })->where('status','1')->get();
        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();
        $kelas = Kelas::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('id', $unitId);
            })->where('status','1')->get();
        $jurusans = Jurusan::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('id', $unitId);
            })->where('status','1')->get();

        return view('pages.data_master.siswa.siswa_create', compact(
            'siswa','units','tahun_ajaran','tahun_ajaran_selected','kelas','jurusans'
        ));
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        $user  = $siswa->user; // relasi ke tabel users

        $request->validate([
            'nisn'            => 'required',
            'name'            => 'required|string|max:255',
            'email'           => 'required',
            'password'        => 'nullable|string|min:6',
            'kelas_id'        => 'required',
            'unit_id'         => 'required',
            'tahun_ajaran_id' => 'required',
            'status'          => 'required|in:0,1',
            'tempat_lahir'    => 'required|string|max:255',
            'no_hp' => 'nullable|string|min:6',
            'rfid_no'         => 'nullable|string|max:255',
            'va_siswa'        => 'nullable|string|max:255',
            'nis'           => 'nullable|string|max:20|unique:siswas,nis,' . $siswa->id,
            'nik'           => 'nullable|string|max:20|unique:siswas,nik,' . $siswa->id,
            'jenis_kelamin' => 'nullable|in:L,P',
            'agama'         => 'nullable|string|max:50',
            'no_hp_ortu'    => 'nullable|string|max:20',
            'nama_ortu'     => 'nullable|string|max:100',
            'bank'          => 'nullable|string|max:100',
            'no_rekening'   => 'nullable|string|max:50',
            'qrcode'        => 'nullable|string',
        ]);

//        DB::beginTransaction();
        try {
            // 🔹 Update user
            $user->update([
                'name'  => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password')
                    ? bcrypt($request->password)
                    : $user->password,
            ]);

            // 🔹 Update siswa
            $siswa->update([
                'nisn'            => $request->nisn,
                'tempat_lahir'    => $request->tempat_lahir,
                'tanggal_lahir'   => $request->tanggal_lahir,
                'no_hp'           => $request->no_hp,
                'image'           => $request->image ?? $siswa->image,
                'unit_id'         => $request->unit_id,
                'kelas_id'        => $request->kelas_id,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'status'          => $request->status,
                'rfid_no'         => $request->rfid_no,
                'va_siswa'        => $request->va_siswa,
                'nis' => $request->nis,
                'nik'           => $request->nik,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama'         => $request->agama,
                'no_hp_ortu'    => $request->no_hp_ortu,
                'nama_ortu'     => $request->nama_ortu,
                'bank'          => $request->bank,
                'no_rekening'   => $request->no_rekening,
                'qrcode'        => $request->qrcode,
            ]);

//            DB::commit();
            return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);

        DB::beginTransaction();
        try {
            // 🔹 Hapus user juga supaya konsisten
            if ($siswa->user) {
                $siswa->user->delete();
            }
            $siswa->delete();

            DB::commit();
            return redirect()->route('siswa.index')->with('success', 'Siswa berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $siswa = Siswa::findOrFail($id);
        $units = Unit::isactive()->get();
        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();
        $kelas = Kelas::all();
        $jurusans = Jurusan::all();
        $show = true;
        return view('pages.data_master.siswa.siswa_create', compact(
            'siswa','show','units','tahun_ajaran','tahun_ajaran_selected','kelas','jurusans'
        ));
    }
    public function getByKelas($kelasId)
    {
        $siswas = \App\Models\Siswa::with('user')->where('kelas_id', $kelasId)->get();
        return response()->json($siswas);
    }
    public function showdetail($id)
    {
        $siswa = \App\Models\Siswa::with(['kelas.jurusan', 'unit','user.saldo','tahun_ajaran'])->find($id);
        return response()->json([
            'nama_lengkap' => $siswa->user->nama,
            'nisn' => $siswa->nisn,
            'unit' => $siswa->unit->nama_unit ?? '-',
            'kelas' => $siswa->kelas->nama_kelas ?? '-',
            'jurusan' => $siswa->jurusan->jurusan->nama_jurusan ?? '-',
            'tahun_ajaran' => $siswa->tahun_ajaran->tahun_ajaran ?? '-',
            'tempat_lahir' => $siswa->tempat_lahir,
            'tanggal_lahir' => $siswa->tanggal_lahir,
            'no_hp' => $siswa->no_hp,
            'foto' => $siswa->foto,
            'saldo_akhir' => $siswa->user->saldo->saldo_akhir ?? 0,
        ]);
    }
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,gif|max:1024',
        ]);

        $file = $request->file('file');
        $filename = Str::random(15) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/siswa', $filename, 'public');

        return response()->json([
            'success' => true,
            'filepath' => 'storage/' . $path
        ]);
    }


}
