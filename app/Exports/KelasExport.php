<?php
namespace App\Exports;

use App\Models\User;
use App\Models\Jurusan;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class KelasExport implements WithHeadings, WithEvents
{
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
                $officerNames = User::whereHas('officer') // Only users that have an officer
                ->pluck('name', 'id')
                ->toArray();
                $officerValidationList = implode(',', array_values($officerNames)); // Values are the names
                $statusValidationList = '1,0';
                $jurusanNames = Jurusan::pluck('nama_jurusan', 'id')->toArray(); // `id` as key, `nama_jurusan` as value
                $jurusanValidationList = implode(',', array_values($jurusanNames)); // Values are the names
                $event->sheet->getDelegate()->getDataValidation('B2:B1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . $officerValidationList . '"');

                $event->sheet->getDelegate()->getDataValidation('C2:C1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . $statusValidationList . '"');

                $event->sheet->getDelegate()->getDataValidation('D2:D1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . $jurusanValidationList . '"');
            },
        ];
    }
}
