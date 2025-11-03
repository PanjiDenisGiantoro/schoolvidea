<?php
namespace App\Exports;

use App\Models\Roles_petugas;  // Import RolePetugas model for role_id dropdown
use App\Models\Jurusan;      // Import Jurusan model for jurusan dropdown
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;

class OfficerExport implements FromCollection, WithHeadings, WithEvents
{
    public function collection()
    {
        // Return empty collection with 10 empty rows to show dropdown
        return new Collection([
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
        ]);
    }

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
                $sheet = $event->sheet->getDelegate();

                // Ambil data untuk dropdown
                $rolePetugas = Roles_petugas::pluck('name')->toArray();
                $jenisKelamin = ['Laki-laki', 'Perempuan'];
                $agama = ['Islam', 'Protestan', 'Katholik', 'Hindu', 'Buddha'];

                // Dropdown untuk Role (Column D)
                if (!empty($rolePetugas)) {
                    // Escape values that contain commas or quotes
                    $roleEscaped = array_map(function($item) {
                        return str_replace('"', '""', $item);
                    }, $rolePetugas);
                    $roleValidationList = implode(',', $roleEscaped);

                    $sheet->getDataValidation('D2:D1000')
                        ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                        ->setAllowBlank(true)
                        ->setFormula1('"' . $roleValidationList . '"');
                }

                // Dropdown untuk Jenis Kelamin (Column L)
                $sheet->getDataValidation('L2:L1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $jenisKelamin) . '"');

                // Dropdown untuk Agama (Column M)
                $sheet->getDataValidation('M2:M1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $agama) . '"');
            },
        ];
    }
}
