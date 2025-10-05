<?php
namespace App\Exports;

use App\Models\Officer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OfficerExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Officer::with('user','unit')->get()->map(function ($o) {
            return [
                'nip'            => $o->nip,
                'nuptk'          => $o->nuptk,
                'nik'            => $o->nik,
                'name'           => $o->user->name ?? '',
                'email'          => $o->user->email ?? '',
                'unit'           => $o->unit->nama_unit ?? '',
                'role'           => $o->role->name ?? '',
                'jurusan'        => $o->jurusan,
                'no_hp'          => $o->no_hp,
                'bank'           => $o->bank,
                'no_rekening'    => $o->no_rekening,
                'qr_code'        => $o->qr_code,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NIP', 'NUPTK', 'NIK', 'Nama', 'Email', 'Unit', 'Role',
            'Jurusan', 'No HP', 'Bank', 'No Rekening', 'QR Code'
        ];
    }
}
