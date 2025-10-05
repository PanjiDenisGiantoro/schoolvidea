<?php
namespace App\Exports;

use App\Models\User;
use App\Models\Jurusan;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class JurusanExport implements WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'Nama Jurusan',
            'Kode Jurusan',
            'Keterangan',
            'Status'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $statusValidationList = '1,0';

                $event->sheet->getDelegate()->getDataValidation('D2:D1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . $statusValidationList . '"');

            },
        ];
    }
}
