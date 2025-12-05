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

class TagihanReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $dataDetails;
    protected $summary;
    protected $from;
    protected $to;

    public function __construct($dataDetails, $summary, $from, $to)
    {
        $this->dataDetails = $dataDetails;
        $this->summary = $summary;
        $this->from = $from;
        $this->to = $to;
    }

    public function collection()
    {
        $data = collect();

        // Add title and period
        $data->push([
            'LAPORAN TAGIHAN SISWA',
            '', '', '', '', '', '', '', '', ''
        ]);
        $data->push([
            'Periode: ' . date('d/m/Y', strtotime($this->from)) . ' s/d ' . date('d/m/Y', strtotime($this->to)),
            '', '', '', '', '', '', '', '', ''
        ]);
        $data->push(['', '', '', '', '', '', '', '', '', '']); // Empty row

        // Add summary
        $data->push(['RINGKASAN', '', '', '', '', '', '', '', '', '']);
        $data->push(['Total Data', $this->summary['jumlah_data'], '', '', '', '', '', '', '', '']);
        $data->push(['Total Tagihan', 'Rp ' . number_format($this->summary['nominal_tagihan'], 0, ',', '.'), '', '', '', '', '', '', '', '']);
        $data->push(['Total Dibayar', 'Rp ' . number_format($this->summary['sudah_dibayar'], 0, ',', '.'), '', '', '', '', '', '', '', '']);
        $data->push(['Total Tunggakan', 'Rp ' . number_format($this->summary['belum_dibayar'], 0, ',', '.'), '', '', '', '', '', '', '', '']);
        $data->push(['', '', '', '', '', '', '', '', '', '']); // Empty row
        $data->push(['', '', '', '', '', '', '', '', '', '']); // Empty row

        // Add column headers
        $data->push([
            'No',
            'NISN',
            'Nama Siswa',
            'Unit',
            'Kelas',
            'Nama Tagihan',
            'Nominal Tagihan',
            'Potongan',
            'Jumlah Tagihan',
            'Sudah Dibayar',
            'Tunggakan',
            'Status'
        ]);

        // Add data rows
        $no = 1;
        foreach ($this->dataDetails as $detail) {
            $data->push([
                $no++,
                $detail['siswa']->nisn ?? '-',
                $detail['siswa']->user->name ?? '-',
                $detail['tagihan']->unit->nama_unit ?? '-',
                $detail['tagihan']->kelas->nama_kelas ?? '-',
                $detail['tagihan']->nama_tagihan ?? '-',
                $detail['nominal_tagihan'],
                $detail['potongan'],
                $detail['jumlah_tagihan'],
                $detail['sudah_dibayar'],
                $detail['tunggakan'],
                $detail['status']
            ]);
        }

        // Add total row
        $data->push([
            '',
            '',
            '',
            '',
            '',
            '',
            'TOTAL:',
            array_sum(array_column($this->dataDetails, 'potongan')),
            $this->summary['nominal_tagihan'],
            $this->summary['sudah_dibayar'],
            $this->summary['belum_dibayar'],
            ''
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
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Period style
        $sheet->mergeCells('A2:L2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Summary header
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('A5:A8')->getFont()->setBold(true);

        // Column header style (row 11)
        $headerRow = 11;
        $sheet->getStyle("A{$headerRow}:L{$headerRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerRow}:L{$headerRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2EFDA');
        $sheet->getStyle("A{$headerRow}:L{$headerRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Auto-size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Number format for currency columns
        $lastRow = count($this->dataDetails) + 12;
        $sheet->getStyle("G12:K{$lastRow}")->getNumberFormat()
            ->setFormatCode('#,##0');

        // Total row style
        $sheet->getStyle("A{$lastRow}:L{$lastRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$lastRow}:L{$lastRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF2F2F2');

        return [];
    }

    public function title(): string
    {
        return 'Laporan Tagihan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 15,
            'C' => 25,
            'D' => 15,
            'E' => 15,
            'F' => 20,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 12,
        ];
    }
}
