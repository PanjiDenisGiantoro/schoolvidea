<?php

namespace App\Http\Controllers;

use App\Mail\TrialRegistrationConfirmation;
use App\Mail\TrialRegistrationConfirmationnext;
use App\Models\Roles;
use App\Models\Roles_petugas;
use App\Models\TrialRegistration;
use App\Models\TipeUnit;
use App\Models\Unit;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TrialRegistrationController extends Controller
{
    public function showForm()
    {
        // Mengambil data tipe unit dan yayasan untuk dropdown
        $tipeunit = TipeUnit::all();
        $yayasans = Yayasan::all();
        return view('registerpublic', compact('tipeunit', 'yayasans'));
    }

    public function store(Request $request)
    {
        // Validasi input data
        $data = $request->validate([
            'school_name'    => 'required|string|max:150',
            'npsn'           => 'required|string|max:30',
            'address'        => 'required|string|max:255',
            'full_name'      => 'required|string|max:150',
            'email'          => 'required|email|max:150',
            'no_hp'          => 'required|string|max:30',
            'agree'          => 'accepted',
            'tipe_unit_id'   => 'required|integer',
            'yayasan_id'     => 'nullable',
        ]);

        $yayasan_id = $data['yayasan_id'] ?? null;
        $trialRegistration =  TrialRegistration::create([
            'school_name'    => $data['school_name'],
            'npsn'           => $data['npsn'],
            'address'        => $data['address'],
            'full_name'      => $data['full_name'],
            'email'          => $data['email'],
            'no_hp'          => $data['no_hp'],
            'tipe_unit_id'   => $data['tipe_unit_id'],
            'yayasan_id'     => $yayasan_id,
            'status'         => '0',  // Status default
        ]);

        Mail::to($trialRegistration->email)->send(new TrialRegistrationConfirmation($trialRegistration));

        return redirect()
            ->route('landing.registerpublic') // Sesuaikan dengan route yang sesuai
            ->with('success', 'Pendaftaran berhasil! Kami akan segera menghubungi Anda.');
    }
    public function registrationPortal($id)
    {
        $trialRegistration = TrialRegistration::findOrFail($id);
        return view('registration_portal', compact('trialRegistration'));
    }


        public function storePortal(Request $request, $id)
    {
        // Buat Unit berdasarkan data yang disubmit

//        dd($request->all());
        $trialUser = TrialRegistration::where('id', $id)->firstOrFail();

        $centralCode = 'U' . strtoupper(Str::random(7));
        $unit = Unit::create([
            'nama_unit' => $request->school_name,   // Menggunakan nama sekolah
            'code' => $centralCode,         // Generate kode unik untuk unit
            'image' => null,                        // Set image null
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'alamat' => $request->address,          // Alamat
            'website' => '',         // Website, jika ada
            'tipe_unit_id' => $trialUser->tipe_unit_id,
            'status' => 1,                          // Status aktif
        ]);
//
//        $yayasan_id = null;
//        if ($request->has('yayasan_id') && !empty($request->yayasan_id)) {
//            $yayasan = Yayasan::create([
//                'nama_yayasan' => $request->yayasan_name, // Nama yayasan
//                'nama_pimpinan' => $request->yayasan_leader, // Nama pimpinan yayasan
//                'central_code' => strtoupper(uniqid()), // Generate central code
//                'image' => null, // Set image null
//                'no_hp' => $data['no_hp'],
//                'email' => $data['email'],
//                'alamat' => $request->address, // Alamat yayasan
//                'website' => $request->website, // Website yayasan
//                'status' => 'active',
//            ]);
//            $yayasan_id = $yayasan->id;
//        }

        // Buat User sebagai admin
        $user = User::create([
            'name' => $request->username,
            'unit_id' =>$unit->id,
            'password' => Hash::make('123456'), // Menggunakan hash untuk password
            'email' => $request->email,
        ]);

            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => 'admin'],
                ['guard_name' => 'web']
            );
            $user->assignRole($roleSpatie->name);

            $trialUser->update([
                'status' => '1',
                'updated_at' => now()
            ]);
            Mail::to($user->email)->send(new TrialRegistrationConfirmationnext($user, $unit));
            return redirect()->route('landing.success') // Atau ke halaman yang sesuai
        ->with('success', 'Penyiapan portal berhasil!');
    }
}
