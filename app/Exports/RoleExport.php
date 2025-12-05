<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;

class RoleExport implements FromCollection, WithHeadings, WithEvents
{
    public function collection()
    {
        // Return empty collection with 10 empty rows to show dropdown
        return new Collection([
            ['', ''],
            ['', ''],
            ['', ''],
            ['', ''],
            ['', ''],
            ['', ''],
            ['', ''],
            ['', ''],
            ['', ''],
            ['', ''],
        ]);
    }

    public function headings(): array
    {
        return [
            'name',
            'guard_name'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Ambil data untuk dropdown
                $guardNames = ['web', 'api'];

                // Dropdown untuk Guard Name (Column B)
                $sheet->getDataValidation('B2:B1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $guardNames) . '"');
            },
        ];
    }
}
