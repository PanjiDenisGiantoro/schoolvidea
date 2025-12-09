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
            'NISN *',
            'NIS *',
            'PASSWORD',
            'NAMA LENGKAP *',
            'NIK *',
            'JENIS KELAMIN *',
            'TANGGAL LAHIR (DD/MM/YYYY)',
            'AGAMA *',
            'NO HP SISWA',
            'NO HP ORTU *',
            'EMAIL *',
            'NAMA ORANG TUA *',
            'STATUS * ( 1 = Aktif / 0 = Tidak Aktif)',
            'ALAMAT',
            'KELAS *',
            'JURUSAN',
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
                $kelas = Kelas::when(Auth::user()->unit_id, function ($query) {
                        return $query->where('unit_id', Auth::user()->unit_id);
                    })
                    ->pluck('nama_kelas')
                    ->toArray();
                $jenisKelamin = ['Laki-laki', 'Perempuan'];
                $status = ['1', '0'];
                $agama = ['Islam', 'Protestan', 'Katholik', 'Hindu', 'Buddha'];

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

                // Dropdown untuk Agama (Column H - kolom ke-8)
                $sheet->getDataValidation('H2:H1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $agama) . '"');

                // Dropdown untuk Status (Column M - kolom ke-13)
                $sheet->getDataValidation('M2:M1000')
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setFormula1('"' . implode(',', $status) . '"');

                // Dropdown untuk Kelas (Column O - kolom ke-15)
                if (!empty($kelas)) {
                    // Escape values that contain commas or quotes
                    $kelasEscaped = array_map(function($item) {
                        return str_replace('"', '""', $item);
                    }, $kelas);
                    $kelasValidationList = implode(',', $kelasEscaped);

                    $sheet->getDataValidation('O2:O1000')
                        ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                        ->setAllowBlank(true)
                        ->setFormula1('"' . $kelasValidationList . '"');
                }
            },
        ];
    }
}
