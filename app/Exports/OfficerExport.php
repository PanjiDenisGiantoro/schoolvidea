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
            'NIP *',
            'NUPTK',
            'PASSWORD',
            'NAMA LENGKAP *',
            'NIK *',
            'JENIS KELAMIN *',
            'TEMPAT LAHIR',
            'TANGGAL LAHIR (DD/MM/YYYY)',
            'AGAMA *',
            'NO HP *',
            'EMAIL *',
            'ROLE *',
            'AKSES YAYASAN * ( 1 = Ya/ 0 = Tidak)',
            'STATUS * ( 1 = Aktif / 0 = Tidak Aktif)',
            'ALAMAT *',
            'JABATAN *',
            'NO RFID',
            'NO VA',
            'BANK',
            'NO REKENING'
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
                $aksesYayasan = ['1', '0'];
                $status = ['1', '0'];

                // Set background color kuning untuk header row pertama
                $sheet->getStyle('A1:T1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFFF00'); // Kuning

                // Bold untuk header
                $sheet->getStyle('A1:T1')->getFont()->setBold(true);

                // Dropdown untuk Jenis Kelamin (Column F - kolom ke-6)
                $sheet->getDataValidation('F2:F1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $jenisKelamin) . '"');

                // Dropdown untuk Agama (Column I - kolom ke-9)
                $sheet->getDataValidation('I2:I1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $agama) . '"');

                // Dropdown untuk Role (Column L - kolom ke-12)
                if (!empty($rolePetugas)) {
                    // Escape values that contain commas or quotes
                    $roleEscaped = array_map(function($item) {
                        return str_replace('"', '""', $item);
                    }, $rolePetugas);
                    $roleValidationList = implode(',', $roleEscaped);

                    $sheet->getDataValidation('L2:L1000')
                        ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                        ->setAllowBlank(true)
                        ->setFormula1('"' . $roleValidationList . '"');
                }

                // Dropdown untuk Akses Yayasan (Column M - kolom ke-13)
                $sheet->getDataValidation('M2:M1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $aksesYayasan) . '"');

                // Dropdown untuk Status (Column N - kolom ke-14)
                $sheet->getDataValidation('N2:N1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $status) . '"');
            },
        ];
    }
}
