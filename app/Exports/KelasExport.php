<?php
namespace App\Exports;

use App\Models\User;
use App\Models\Jurusan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;

class KelasExport implements FromCollection, WithHeadings, WithEvents
{
    public function collection()
    {
        // Return empty collection with 10 empty rows to show dropdown
        return new Collection([
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
        ]);
    }

    public function headings(): array
    {
        return [
            'Nama Kelas',
            'Guru / Wali Kelas',
            'Status',
            'Jurusan'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Ambil data untuk dropdown
                $officerNames = User::whereHas('officer')
                    ->pluck('name')
                    ->toArray();
                $jurusanNames = Jurusan::pluck('nama_jurusan')->toArray();
                $status = ['1', '0'];

                // Dropdown untuk Guru/Wali Kelas (Column B)
                if (!empty($officerNames)) {
                    // Escape values that contain commas or quotes
                    $officerEscaped = array_map(function($item) {
                        return str_replace('"', '""', $item);
                    }, $officerNames);
                    $officerValidationList = implode(',', $officerEscaped);

                    $sheet->getDataValidation('B2:B1000')
                        ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                        ->setAllowBlank(true)
                        ->setFormula1('"' . $officerValidationList . '"');
                }

                // Dropdown untuk Status (Column C)
                $sheet->getDataValidation('C2:C1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $status) . '"');

                // Dropdown untuk Jurusan (Column D)
                if (!empty($jurusanNames)) {
                    // Escape values that contain commas or quotes
                    $jurusanEscaped = array_map(function($item) {
                        return str_replace('"', '""', $item);
                    }, $jurusanNames);
                    $jurusanValidationList = implode(',', $jurusanEscaped);

                    $sheet->getDataValidation('D2:D1000')
                        ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                        ->setAllowBlank(true)
                        ->setFormula1('"' . $jurusanValidationList . '"');
                }
            },
        ];
    }
}
