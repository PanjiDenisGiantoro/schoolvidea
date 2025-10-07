<?php
namespace App\Exports;

use App\Models\Roles_petugas;
use App\Models\Siswa;
use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SiswaExport implements  WithHeadings, WithEvents
{

    public function headings(): array
    {
        return [
            'NISN', 'Name', 'Email', 'Password', 'RFID No', 'Kelas', 'Status', 'VA Siswa', 'Jenis Kelamin', 'Agama', 'No HP Orang Tua', 'Nama Orang Tua', 'Bank', 'No Rekening'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Ambil data untuk dropdown
                $kelas = Kelas::pluck('nama_kelas')->toArray();
                $roleValidationList = implode(',', $kelas);
                $jenisKelamin = ['Laki-laki' , 'Perempuan' ];
                $status = ['1' , '0' ];
                $agama = ['Islam', 'Protestan', 'Katholik', 'Hindu', 'Buddha'];

                $event->sheet->getDelegate()->getDataValidation('F2:F1000')  // Assuming role_id is in column D
                ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . $roleValidationList . '"');

                $event->sheet->getDelegate()->getDataValidation('I2:I1000')  // Jenis Kelamin
                ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $jenisKelamin) . '"');

                $event->sheet->getDelegate()->getDataValidation('G2:G1000')  // Jenis Kelamin
                ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $status) . '"');

                $event->sheet->getDelegate()->getDataValidation('J2:J1000')  // Agama
                ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $agama) . '"');
            },
        ];
    }
}
