<?php
namespace App\Exports;

use App\Models\User;
use App\Models\Jurusan;
use Illuminate\Support\Facades\Auth;
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
            ['','',  '', '', ''],
            ['', '','',  '', ''],
            ['', '', '','',  ''],
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '', '', '', ''],
        ]);
    }

    public function headings(): array
    {
        return [
            'Kode Kelas',
            'Nama Kelas',
            'Guru / Wali Kelas',
            'Jurusan',
            'Status'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Ambil data untuk dropdown
                $officerNames = User::whereHas('officer')
                    ->when(Auth::user()->unit_id, function ($query) {
                        return $query->where('unit_id', Auth::user()->unit_id);
                    })
                    ->pluck('name')
                    ->toArray();
                $jurusanNames = Jurusan::pluck('nama_jurusan')
                    ->when(Auth::user()->unit_id, function ($query) {
                        return $query->where('unit_id', Auth::user()->unit_id);
                    })
                    ->toArray();
                $status = ['aktif', 'non_aktif'];

                // Set background color kuning untuk header row pertama
                $sheet->getStyle('A1:D1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFFF00'); // Kuning

                // Bold untuk header
                $sheet->getStyle('A1:D1')->getFont()->setBold(true);

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
                $sheet->getDataValidation('E2:E1000')
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
