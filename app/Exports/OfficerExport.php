<?php
namespace App\Exports;

use App\Models\Roles_petugas;  // Import RolePetugas model for role_id dropdown
use App\Models\Jurusan;      // Import Jurusan model for jurusan dropdown
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class OfficerExport implements WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'name',
            'email',
            'password',
            'role_id',
            'image',
            'tempat_lahir',
            'no_hp',
            'rfid_no',
            'nip',
            'nuptk',
            'nik',
            'jenis_kelamin',
            'agama',
            'tanggal_lahir',
            'alamat',
            'bank',
            'no_rekening',
            'no_kartu_rfid',
            'qr_code',
            'va_guru'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Dropdown for `role_id` (Role from role_petugas table)
                $rolePetugas = Roles_petugas::pluck('name')->toArray();
                $roleValidationList = implode(',', $rolePetugas);

                $event->sheet->getDelegate()->getDataValidation('D2:D1000')  // Assuming role_id is in column D
                ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . $roleValidationList . '"');

                // Dropdown for `jenis_kelamin` (Gender options)
                $jenisKelaminList = 'Laki-laki,Perempuan';

                $event->sheet->getDelegate()->getDataValidation('L2:L1000')  // Assuming jenis_kelamin is in column L
                ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . $jenisKelaminList . '"');

                // Dropdown for `agama` (Religion options)
                $agamaList = 'Islam,Protestan,Katholik,Hindu,Buddha';

                $event->sheet->getDelegate()->getDataValidation('M2:M1000')  // Assuming agama is in column M
                ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . $agamaList . '"');


            },
        ];
    }
}
