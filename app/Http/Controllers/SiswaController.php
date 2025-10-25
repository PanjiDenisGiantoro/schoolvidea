<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Roles_petugas;
use App\Models\Saldo_keuangan;
use App\Models\Siswa;
use App\Models\Unit;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class SiswaController extends Controller
{
    public function index()
    {
        $units = Unit::all();
        $siswa = Siswa::with('unit', 'kelas', 'user')
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id', $unitId);
            })
            ->where('status', '1')
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

        $logoUnit = $units->first()->image ?? null;

        return view('pages.data_master.siswa.siswa', compact('siswa', 'headers', 'logoUnit'));
    }
    public function create()
    {
        $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('id', $unitId);
        })->where('status', '1')->get();

        $yayasan = Yayasan::with('units')
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->whereHas('units', function ($q) use ($unitId) {
                    $q->where('id', $unitId);
                });
            })->where('status', '1')->get();

        $kelas = Kelas::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('unit_id', $unitId);
        })->where('status', '1')->get();

        $jurusans = Jurusan::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('unit_id', $unitId);
        })->where('status', 1)->get();

        $logoUnit = $units->first()->image ?? null;

        return view('pages.data_master.siswa.siswa_create', compact(
            'kelas', 'yayasan', 'units', 'jurusans', 'logoUnit'
        ));
    }
        public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nisn' => 'required|unique:siswas,nisn',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'kelas_id' => 'required',
            'unit_id' => 'required',
            'rfid_no' => 'nullable|string|max:255',
            'va_siswa' => 'nullable|string|max:255',
            'nis' => 'nullable|string|max:20|unique:siswas,nis',
            'nik' => 'nullable|string|max:20|unique:siswas,nik',
            'jenis_kelamin' => 'nullable|in:L,P',
            'agama' => 'nullable|string|max:50',
            'no_hp_ortu' => 'nullable|string|max:20',
            'nama_ortu' => 'nullable|string|max:100',
            'bank' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:50',
            'jurusan' => 'required|array|min:1',  // Make sure at least one jurusan is selected

            ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Buat user baru
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => bcrypt($request->password),
                'rfid_no'  => $request->rfid_no,
                'unit_id'  => $request->unit_id,
            ]);

            // Ambil role "siswa"
            $rolePetugas = Roles_petugas::where('name', 'siswa')->first();
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rolePetugas->name],
                ['guard_name' => 'web']
            );
            $user->assignRole($roleSpatie->name);

            // Buat siswa
            $siswa = Siswa::create([
                'nisn'            => $request->nisn,
                'tempat_lahir'    => $request->tempat_lahir,
                'tanggal_lahir'   => $request->tanggal_lahir,
                'no_hp'           => $request->no_hp,
                'image'           => $request->image ?? null,
                'user_id'         => $user->id,
                'unit_id'         => $request->unit_id,
                'kelas_id'        => $request->kelas_id,
                'status'          => $request->status,
                'rfid_no'         => $request->rfid_no,
                'va_siswa'        => $request->va_siswa,
                'nis'             => $request->nis,
                'nik'             => $request->nik,
                'jenis_kelamin'   => $request->jenis_kelamin,
                'agama'           => $request->agama,
                'no_hp_ortu'      => $request->no_hp_ortu,
                'nama_ortu'       => $request->nama_ortu,
                'bank'            => $request->bank,
                'jurusan' => json_encode($request->jurusan),  // Menyimpan sebagai JSON
                'no_rekening'     => $request->no_rekening,
            ]);
            // Jika VA belum diisi, generate otomatis dari NIS + NISN
            if (empty($request->va_siswa)) {
                $nis  = str_pad(substr($request->nis ?? '', 0, 8), 8, '0', STR_PAD_RIGHT);

                $nisn_raw = $request->nisn ?? '';
                // Jika NISN diawali 0, ambil mulai dari digit ke-2
                if (strlen($nisn_raw) > 0 && $nisn_raw[0] === '0') {
                    $nisn_raw = substr($nisn_raw, 1);
                }
                $nisn = str_pad(substr($nisn_raw, 0, 8), 8, '0', STR_PAD_RIGHT);

                // Gabungkan jadi 16 digit
                $va_siswa = $nis . $nisn;
            } else {
                $va_siswa = $request->va_siswa;
            }

            // Simpan ke array data
            $data = $request->all();
            $data['va_siswa'] = $va_siswa;

            // Simpan ke database
            if (isset($siswa)) {
                $siswa->update($data);
            } else {
                Siswa::create($data);
            }

            // Generate QR Code
            $qrcodeValue = $siswa->nisn . '-' . $siswa->nis;
            $fileName = $siswa->nis . '.png';
            $path = 'qrcodes/' . $fileName;
//
//            Storage::disk('local')->put($path, QrCode::format('png')->size(300)->generate($qrcodeValue));
//
//            $siswa->update([
//                'qrcode' => $qrcodeValue,
//                'qrcode_image' => $path,
//            ]);

            // Buat saldo awal
            Saldo_keuangan::create([
                'user_id' => $user->id,
                'saldo_akhir' => 0,
                'status' => 0,
            ]);

            DB::commit();
            return redirect()->route('siswa.index')->with('success', 'Siswa berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('danger', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $siswa = Siswa::findOrFail($id);
        $units = Unit::where('status', '1')->get();
        $kelas = Kelas::where('status', '1')->get();
        $jurusans = Jurusan::where('status', 1)->get();
        $logoUnit = $siswa->unit->image ?? null;

        return view('pages.data_master.siswa.siswa_create', compact(
            'siswa', 'units', 'kelas', 'jurusans', 'logoUnit'
        ));
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        $user  = $siswa->user;

        $request->validate([
            'nisn' => 'required',
            'name' => 'required|string|max:255',
            'email' => 'required',
            'password' => 'nullable|string|min:6',
        ]);

        try {
            $user->update([
                'name'  => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password')
                    ? bcrypt($request->password)
                    : $user->password,
            ]);

            $siswa->update($request->all());

            // Regenerate QR code jika NIS/NISN berubah
            $qrcodeValue = $siswa->nisn . '-' . $siswa->nis;
            $fileName = $siswa->nis . '.png';
            $path = 'qrcodes/' . $fileName;
            Storage::disk('local')->put($path, QrCode::format('png')->size(300)->generate($qrcodeValue));

            $siswa->update([
                'qrcode' => $qrcodeValue,
                'qrcode_image' => $path,
            ]);

            return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function showQr($id)
    {
        $siswa = Siswa::findOrFail($id);
        $filePath = storage_path('app/' . $siswa->qrcode_image);

        if (!file_exists($filePath)) {
            abort(404, 'QR code tidak ditemukan');
        }

        return response()->file($filePath);
    }

    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        DB::beginTransaction();
        try {
            if ($siswa->user) {
                $siswa->user->delete();
            }
            $siswa->delete();
            DB::commit();

            return redirect()->route('siswa.index')->with('success', 'Siswa berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('danger', $e->getMessage());
        }
    }

    public function show($id)
    {
        $siswa = Siswa::findOrFail($id);
        $units = Unit::isactive()->get();
        $kelas = Kelas::all();
        $jurusans = Jurusan::all();
        $show = true;

        // Logo unit siswa
        $logoUnit = $siswa->unit->image ?? null;

        return view('pages.data_master.siswa.siswa_create', compact(
            'siswa','show','units','kelas','jurusans','logoUnit'
        ));
    }
    public function getByKelas($kelasId)
    {
        $siswas = \App\Models\Siswa::with('user')->where('kelas_id', $kelasId)->get();
        return response()->json($siswas);
    }
    public function showdetail($id)
    {
        $siswa = \App\Models\Siswa::with([
            'kelas.jurusan',
            'unit',
            'user.saldo',
        ])->findOrFail($id);

        return response()->json([
            'nama_lengkap'  => $siswa->user->name ?? '-',
            'nisn'           => $siswa->nisn ?? '-',
            'unit'           => $siswa->unit->nama_unit ?? '-',
            'kelas'          => $siswa->kelas->nama_kelas ?? '-',
            'jurusan'        => $siswa->kelas->jurusan->nama_jurusan ?? '-',
            'tempat_lahir'   => $siswa->tempat_lahir ?? '-',
            'tanggal_lahir'  => $siswa->tanggal_lahir ?? '-',
            'no_hp'          => $siswa->no_hp ?? '-',
            'foto'           => $siswa->image
                ? asset('storage/' . $siswa->image)
                : asset('images/default-avatar.png'),
            'qrcode'         => $siswa->qrcode_image
                ? asset('storage/' . $siswa->qrcode_image)
                : null,
            'saldo_akhir'    => $siswa->user->saldo->saldo_akhir ?? 0,
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

    public function getJurusanByUnit($unitId)
    {
        $jurusans = Jurusan::where('unit_id', $unitId)
            ->where('status', '1')
            ->get();
        return response()->json($jurusans);
    }

}
