<?php
namespace App\Exports;

use App\Models\Roles_petugas;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;

class SiswaExport implements FromCollection, WithHeadings, WithEvents
{
    public function collection()
    {
        // Return empty collection with 10 empty rows to show dropdown
        return new Collection([
            ['','', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['','', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['','', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['','', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '','', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '','', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '','', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '','', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '','', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '','', '', '', '', '', '', '', '', ''],
        ]);
    }

    public function headings(): array
    {
        return [
           'NIS','NISN', 'Name', 'Email', 'Password', 'RFID No', 'Kelas', 'Status', 'VA Siswa', 'Jenis Kelamin', 'Agama', 'No HP Orang Tua', 'Nama Orang Tua', 'Bank', 'No Rekening'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Ambil data untuk dropdown
                $kelas = Kelas::pluck('nama_kelas')
                    ->when(Auth::user()->unit_id, function ($query) {
                        return $query->where('unit_id', Auth::user()->unit_id);
                    })
                    ->toArray();
                $jenisKelamin = ['Laki-laki', 'Perempuan'];
                $status = ['1', '0'];
                $agama = ['Islam', 'Protestan', 'Katholik', 'Hindu', 'Buddha'];

                // Dropdown untuk Kelas (Column F)
                if (!empty($kelas)) {
                    // Escape values that contain commas or quotes
                    $kelasEscaped = array_map(function($item) {
                        return str_replace('"', '""', $item);
                    }, $kelas);
                    $kelasValidationList = implode(',', $kelasEscaped);

                    $sheet->getDataValidation('G2:G1000')
                        ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                        ->setAllowBlank(true)
                        ->setFormula1('"' . $kelasValidationList . '"');
                }

                $sheet->getStyle('A1:O1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFFF00'); // Kuning

                // Bold untuk header
                $sheet->getStyle('A1:U1')->getFont()->setBold(true);
                // Dropdown untuk Status (Column G)
                $sheet->getDataValidation('G2:G1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $status) . '"');

                // Dropdown untuk Jenis Kelamin (Column I)
                $sheet->getDataValidation('J2:J1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $jenisKelamin) . '"');

                // Dropdown untuk Agama (Column J)
                $sheet->getDataValidation('K2:K1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $agama) . '"');
            },
        ];
    }
}
