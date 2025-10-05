<?php
namespace App\Imports;

use App\Models\User;
use App\Models\Officer;
use App\Models\Roles_petugas;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OfficerImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $user = User::create([
            'name'     => $row['name'],
            'email'    => $row['email'],
            'password' => Hash::make($row['password'] ?? '123456'),
            'rfid_no'  => $row['no_kartu_rfid'],
            'unit_id'  => $row['unit_id'],
        ]);

        $rolePetugas = Roles_petugas::find($row['role_id']);
        if ($rolePetugas) {
            $user->assignRole($rolePetugas->name);
        }

        return new Officer([
            'nip'             => $row['nip'],
            'nuptk'           => $row['nuptk'],
            'nik'             => $row['nik'],
            'image'           => $row['image'],
            'tempat_lahir'    => $row['tempat_lahir'],
            'tanggal_lahir'   => $row['tanggal_lahir'],
            'jenis_kelamin'   => $row['jenis_kelamin'],
            'agama'           => $row['agama'],
            'alamat'          => $row['alamat'],
            'no_hp'           => $row['no_hp'],
            'unit_id'         => $row['unit_id'],
            'tahun_ajaran_id' => $row['tahun_ajaran_id'],
            'user_id'         => $user->id,
            'role_id'         => $row['role_id'],
            'bank'            => $row['bank'],
            'no_rekening'     => $row['no_rekening'],
            'no_kartu_rfid'   => $row['no_kartu_rfid'],
            'qr_code'         => $row['qr_code'],
            'jurusan'         => $row['jurusan'],
            'va_guru'         => $row['va_guru'],
        ]);
    }
}
