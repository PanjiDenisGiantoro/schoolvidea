<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TabunganReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $rekap;
    protected $summary;
    protected $from;
    protected $to;

    public function __construct($rekap, $summary, $from, $to)
    {
        $this->rekap = $rekap;
        $this->summary = $summary;
        $this->from = $from;
        $this->to = $to;
    }

    public function collection()
    {
        $data = collect();

        // Add title and period
        $data->push([
            'LAPORAN REKAP TABUNGAN SISWA',
            '', '', '', '', '', ''
        ]);
        $data->push([
            'Periode: ' . date('d/m/Y', strtotime($this->from)) . ' s/d ' . date('d/m/Y', strtotime($this->to)),
            '', '', '', '', '', ''
        ]);
        $data->push(['', '', '', '', '', '', '']); // Empty row

        // Add summary
        $data->push(['RINGKASAN', '', '', '', '', '', '']);
        $data->push(['Total Siswa', $this->summary['jumlah_siswa'], '', '', '', '', '']);
        $data->push(['Total Setoran', 'Rp ' . number_format($this->summary['total_setoran'], 0, ',', '.'), '', '', '', '', '']);
        $data->push(['Total Penarikan', 'Rp ' . number_format($this->summary['total_penarikan'], 0, ',', '.'), '', '', '', '', '']);
        $data->push(['Total Saldo', 'Rp ' . number_format($this->summary['total_saldo'], 0, ',', '.'), '', '', '', '', '']);
        $data->push(['', '', '', '', '', '', '']); // Empty row
        $data->push(['', '', '', '', '', '', '']); // Empty row

        // Add column headers
        $data->push([
            'No',
            'NISN',
            'Nama Siswa',
            'Kelas',
            'Unit',
            'Total Setoran',
            'Total Penarikan',
            'Saldo Akhir'
        ]);

        // Add data rows
        $no = 1;
        foreach ($this->rekap as $row) {
            $data->push([
                $no++,
                $row['nisn'],
                $row['nama'],
                $row['kelas'],
                $row['unit'],
                $row['setoran'],
                $row['penarikan'],
                $row['saldo_akhir']
            ]);
        }

        // Add total row
        $data->push([
            '',
            '',
            '',
            '',
            'TOTAL:',
            $this->summary['total_setoran'],
            $this->summary['total_penarikan'],
            $this->summary['total_saldo']
        ]);

        return $data;
    }

    public function headings(): array
    {
        return []; // We'll handle headings in collection()
    }

    public function styles(Worksheet $sheet)
    {
        // Title style
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Period style
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Summary header
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('A5:A8')->getFont()->setBold(true);

        // Column header style (row 11)
        $headerRow = 11;
        $sheet->getStyle("A{$headerRow}:H{$headerRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerRow}:H{$headerRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFDAE8FC');
        $sheet->getStyle("A{$headerRow}:H{$headerRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Number format for currency columns
        $lastRow = count($this->rekap) + 12;
        $sheet->getStyle("F12:H{$lastRow}")->getNumberFormat()
            ->setFormatCode('#,##0');

        // Total row style
        $sheet->getStyle("A{$lastRow}:H{$lastRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$lastRow}:H{$lastRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF2F2F2');

        return [];
    }

    public function title(): string
    {
        return 'Laporan Tabungan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 15,
            'C' => 25,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
        ];
    }
}
