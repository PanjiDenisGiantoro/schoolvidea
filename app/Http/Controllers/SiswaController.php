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
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::all();

        // Build query
        $query = Siswa::with('unit', 'kelas', 'user', 'jurusan');

        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan siswa dari semua unit di yayasan tersebut
            $query->whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            });
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan siswa dari unit tersebut saja
            $query->where('unit_id', Auth::user()->unit_id);
        } elseif ($request->filled('unit_id')) {
            // Admin user filtering by unit
            $query->where('unit_id', $request->unit_id);
        }

        // Search functionality across all columns
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('va_siswa', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('tempat_lahir', 'like', "%{$search}%")
                  ->orWhere('nama_ortu', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('kelas', function ($q) use ($search) {
                      $q->where('nama_kelas', 'like', "%{$search}%");
                  })
                  ->orWhereHas('unit', function ($q) use ($search) {
                      $q->where('nama_unit', 'like', "%{$search}%");
                  })
                  ->orWhereHas('jurusan', function ($q) use ($search) {
                      $q->where('nama_jurusan', 'like', "%{$search}%");
                  });
            });
        }

        // Paginate results
        $siswa = $query->paginate(15)->appends($request->except('page'));

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

        return view('pages.data_master.siswa.siswa', compact('siswa', 'headers', 'logoUnit', 'units'));
    }
    public function create()
    {
        // Filter berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan data dari semua unit di yayasan tersebut
            $yayasan = Yayasan::where('id', Auth::user()->yayasan_id)->where('status', '1')->get();
            $units = Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->get();
            $kelas = Kelas::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->where('status', '1')->get();
            $jurusans = Jurusan::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->where('status', 1)->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan data dari unit tersebut saja
            $yayasan = Yayasan::with('units')
                ->when(Auth::user()->unit_id, function ($query, $unitId) {
                    $query->whereHas('units', function ($q) use ($unitId) {
                        $q->where('id', $unitId);
                    });
                })->where('status', '1')->get();
            $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('id', $unitId);
            })->where('status', '1')->get();
            $kelas = Kelas::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id', $unitId);
            })->where('status', '1')->get();
            $jurusans = Jurusan::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id', $unitId);
            })->where('status', 1)->get();
        } else {
            // Admin bisa melihat semua
            $yayasan = Yayasan::where('status', '1')->get();
            $units = Unit::where('status', '1')->get();
            $kelas = Kelas::where('status', '1')->get();
            $jurusans = Jurusan::where('status', 1)->get();
        }

        $tahunajaran = Tahun_ajaran::where('status', '1')->get();
        $logoUnit = $units->first()->image ?? null;

        return view('pages.data_master.siswa.siswa_create', compact(
            'kelas',
            'yayasan',
            'units',
            'jurusans',
            'logoUnit',
            'tahunajaran',
        ));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nisn' => 'required|max:20',
            'name' => 'required|string|max:255',
            'username' => 'nullable|string',
            'email' => 'required|string|email|max:255',
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
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'alamat' => 'nullable|string',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'no_hp' => 'nullable|string|digits_between:0,14',
            'tahun_ajaran_id' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->with('danger', $validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            // Buat user baru
            $user = User::create([
                'name'       => $request->name,
                'username'   => $request->nisn,
                'email'      => $request->email,
                'password'   => bcrypt($request->nisn),
                'rfid_no'    => $request->rfid_no,
                'unit_id'    => $request->unit_id,
                'yayasan_id' => Auth::user()->yayasan_id,
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
                'jurusan_id'      => $request->jurusan_id,
                'no_rekening'     => $request->no_rekening,
                'alamat'          => $request->alamat,
                'name'            => $request->name,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
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
        $kelas = Kelas::where('unit_id', $siswa->unit_id)->where('status', '1')->get();
        $jurusans = Jurusan::where('unit_id', $siswa->unit_id)->where('status', 1)->get();

        $logoUnit = $siswa->unit->image ?? null;
        $tahunajaran = Tahun_ajaran::where('status', '1')->get();

        return view('pages.data_master.siswa.siswa_create', compact(
            'siswa',
            'units',
            'kelas',
            'jurusans',
            'logoUnit',
            'tahunajaran'
        ));
    }

    public function update(Request $request, $id)
    {
        Log::info('=== UPDATE SISWA START ===');
        Log::info('Siswa ID: ' . $id);

        $siswa = Siswa::findOrFail($id);
        $user = $siswa->user;

        Log::info('Found Siswa: ' . $siswa->nisn);
        Log::info('Found User: ' . $user->email);

        // Validator dengan exclude current records
        $validator = Validator::make($request->all(), [
            'nisn' => 'required|unique:siswas,nisn,' . $siswa->id,
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'kelas_id' => 'required',
            'unit_id' => 'required',
            'rfid_no' => 'nullable|string|max:255|unique:siswas,rfid_no,' . $siswa->id,
            'va_siswa' => 'nullable|string|max:255|unique:siswas,va_siswa,' . $siswa->id,
            'nis' => 'nullable|string|max:20|unique:siswas,nis,' . $siswa->id,
            'nik' => 'nullable|string|max:20|unique:siswas,nik,' . $siswa->id,
            'jenis_kelamin' => 'nullable|in:L,P',
            'agama' => 'nullable|string|max:50',
            'no_hp_ortu' => 'nullable|string|max:20',
            'nama_ortu' => 'nullable|string|max:100',
            'bank' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:50|unique:siswas,no_rekening,' . $siswa->id,
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'alamat' => 'nullable|string',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'no_hp' => 'nullable|string|digits_between:0,14|unique:siswas,no_hp,' . $siswa->id,
            'password' => 'nullable|string|min:6',
            'tahun_ajaran_id' => 'required'
        ], [
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'username.unique' => 'Username sudah digunakan oleh user lain.',
            'nisn.unique' => 'NISN sudah digunakan oleh siswa lain.',
        ]);

        if ($validator->fails()) {
            Log::error('Validation Errors: ', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Terdapat kesalahan dalam pengisian form.');
        }

        //        DB::beginTransaction();
        try {
            Log::info('Starting transaction update...');

            // Update User data
            $userData = [
                'name'       => $request->name,
                'username'   => $request->username,
                'email'      => $request->email,
                'unit_id'    => $request->unit_id,
                'yayasan_id' => Auth::user()->yayasan_id,
            ];

            // Update password hanya jika diisi
            if ($request->filled('password')) {
                $userData['password'] = bcrypt($request->password);
                Log::info('Password updated');
            }

            $user->update($userData);
            Log::info('User updated: ' . $user->id);

            // Update Siswa data
            $siswaData = [
                'nisn' => $request->nisn,
                'nis' => $request->nis,
                'nik' => $request->nik,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'agama' => $request->agama,
                'no_hp' => $request->no_hp,
                'no_hp_ortu' => $request->no_hp_ortu,
                'nama_ortu' => $request->nama_ortu,
                'bank' => $request->bank,
                'no_rekening' => $request->no_rekening,
                'jurusan_id' => $request->jurusan_id,
                'alamat' => $request->alamat,
                'unit_id' => $request->unit_id,
                'kelas_id' => $request->kelas_id,
                'status' => $request->status,
                'rfid_no' => $request->rfid_no,
                'va_siswa' => $request->va_siswa,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
            ];

            // Handle image
            if ($request->filled('image')) {
                $siswaData['image'] = $request->image;
                Log::info('Image updated');
            }

            $siswa->update($siswaData);
            Log::info('Siswa updated: ' . $siswa->id);

            // QR Code generation (optional, bisa skip jika error)
            try {
                if ($siswa->nis && $siswa->nisn) {
                    $qrcodeValue = $siswa->nisn . '-' . $siswa->nis;
                    $fileName = $siswa->nis . '.png';
                    $path = 'qrcodes/' . $fileName;

                    // Create directory if not exists
                    if (!Storage::exists('qrcodes')) {
                        Storage::makeDirectory('qrcodes');
                    }

                    $qrCode = QrCode::format('png')
                        ->size(300)
                        ->generate($qrcodeValue);

                    Storage::put($path, $qrCode);

                    $siswa->update([
                        'qrcode' => $qrcodeValue,
                        'qrcode_image' => $path,
                    ]);
                    Log::info('QR Code generated');
                }
            } catch (\Exception $qrException) {
                Log::warning('QR Code generation skipped: ' . $qrException->getMessage());
            }

            //            DB::commit();
            Log::info('=== UPDATE SISWA SUCCESS ===');

            return redirect()->route('siswa.index')
                ->with('success', 'Data siswa berhasil diperbarui!');
        } catch (\Exception $e) {
            //            DB::rollBack();
            dd($e->getMessage());
            Log::error('UPDATE FAILED: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile());
            Log::error('Line: ' . $e->getLine());

            return redirect()->back()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage())
                ->withInput();
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
        $tahunajaran = Tahun_ajaran::where('status', '1')->get();

        // Logo unit siswa
        $logoUnit = $siswa->unit->image ?? null;

        return view('pages.data_master.siswa.siswa_create', compact(
            'siswa',
            'show',
            'units',
            'kelas',
            'jurusans',
            'logoUnit',
            'tahunajaran'
        ))->with('show', true);
    }
    public function getByKelas($kelasId)
    {
        $siswas = \App\Models\Siswa::with('user')
            ->where('status','1')
            ->where('kelas_id', $kelasId)->get();
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
            'va'        => $siswa->va_siswa ?? '-',
            'bank'   => $siswa->bank ?? '-',
            'norek'  => $siswa->no_rekening ?? '-',
            'no_hp'          => $siswa->no_hp ?? '-',
            'foto'           => $siswa->image
                ? asset($siswa->image)
                : asset('assets/images/default-avatar.png'),
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

    public function getKelasByUnit($unitId)
    {
        $kelas = Kelas::where('unit_id', $unitId)
            ->where('status', '1')
            ->get();
        return response()->json($kelas);
    }
    public function checkUnique(Request $request)
    {
        $nisn = $request->nisn;
        $email = $request->email;
        $nis = $request->nis;
        $nik = $request->nik;
        $rfid_no = $request->rfid_no;
        $va_siswa = $request->va_siswa;
        $no_rekening = $request->no_rekening;
        $no_hp = $request->no_hp;

        $duplicates = [];

        if ($nisn && \App\Models\Siswa::where('nisn', $nisn)->exists()) {
            $duplicates[] = 'nisn';
        }
        if ($nis && \App\Models\Siswa::where('nis', $nis)->exists()) {
            $duplicates[] = 'nis';
        }
        if ($nik && \App\Models\Siswa::where('nik', $nik)->exists()) {
            $duplicates[] = 'nik';
        }
        if ($rfid_no && \App\Models\Siswa::where('rfid_no', $rfid_no)->exists()) {
            $duplicates[] = 'rfid_no';
        }
        if ($va_siswa && \App\Models\Siswa::where('va_siswa', $va_siswa)->exists()) {
            $duplicates[] = 'va_siswa';
        }
        if ($no_rekening && \App\Models\Siswa::where('no_rekening', $nisn)->exists()) {
            $duplicates[] = 'no_rekening';
        }
        if ($no_hp && \App\Models\Siswa::where('no_hp', $no_hp)->exists()) {
            $duplicates[] = 'no_hp';
        }
        if ($email && \App\Models\User::where('email', $email)->exists()) {
            $duplicates[] = 'email';
        }

        // bisa kamu tambahkan lagi field lain kalau perlu
        // if ($request->no_hp && User::where('no_hp', $request->no_hp)->exists()) {
        //     $duplicates[] = 'no_hp';
        // }

        return response()->json([
            'exists' => count($duplicates) > 0,
            'duplicates' => $duplicates
        ]);
    }
}
