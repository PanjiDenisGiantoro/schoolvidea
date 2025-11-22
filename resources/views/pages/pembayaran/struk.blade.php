<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
        }

        .struk {
            width: 80mm;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
        }

        .header h1 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header p {
            font-size: 10px;
            margin: 1px 0;
        }

        .section {
            margin: 6px 0;
            padding: 6px 0;
        }

        .section-title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 3px;
            text-decoration: underline;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-size: 10px;
        }

        .row-left {
            flex: 1;
        }

        .row-right {
            text-align: right;
            flex: 0 0 auto;
            min-width: 30px;
        }

        .divider {
            border-bottom: 1px dashed #000;
            margin: 6px 0;
        }

        .total-section {
            background: #f9f9f9;
            padding: 4px;
            margin: 6px 0;
            text-align: center;
        }

        .total-amount {
            font-size: 13px;
            font-weight: bold;
            margin: 2px 0;
        }

        .footer {
            text-align: center;
            margin-top: 8px;
            font-size: 9px;
            border-top: 1px dashed #000;
            padding-top: 6px;
        }

        .badge {
            display: inline-block;
            padding: 2px 4px;
            background: #e0e0e0;
            font-size: 9px;
            margin-top: 2px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                width: 80mm;
            }
            .struk {
                width: 100%;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="struk">
        {{-- Header --}}
        <div class="header">
            <h1>STRUK PEMBAYARAN</h1>
            <p>Tagihan Siswa</p>
        </div>

        {{-- Informasi Sekolah / Instansi --}}
        <div class="section">
            <div class="section-title">INSTANSI</div>
            <div class="row">
                <div class="row-left">{{ optional($tagihan->unit)->nama_unit ?? '-' }}</div>
            </div>
        </div>

        {{-- Informasi Siswa --}}
        <div class="section">
            <div class="section-title">DATA SISWA</div>
            <div class="row">
                <div class="row-left">Nama:</div>
                <div class="row-right">{{ optional($siswa->user)->name ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="row-left">NISN:</div>
                <div class="row-right">{{ $siswa->nisn ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="row-left">Kelas:</div>
                <div class="row-right">{{ optional($tagihan->kelas)->nama_kelas ?? '-' }}</div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Informasi Tagihan --}}
        <div class="section">
            <div class="section-title">DETAIL PEMBAYARAN</div>

            <div class="row">
                <div class="row-left">Kategori:</div>
                <div class="row-right">{{ optional($tagihan->items->first()->kategori)->nama_kategori ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="row-left">Tanggal Bayar:</div>
                <div class="row-right">{{ $pembayaran->tanggal_bayar->format('d/m/Y') }}</div>
            </div>
            <div class="row">
                <div class="row-left">Waktu:</div>
                <div class="row-right">{{ $pembayaran->created_at->format('H:i') }}</div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Rincian Pembayaran --}}
        <div class="section">
            <div class="section-title">RINCIAN PEMBAYARAN</div>
            <div class="row">
                <div class="row-left">Nominal Tagihan:</div>
                <div class="row-right">Rp {{ number_format($tagihan->items->first()->nominal ?? 0, 0, ',', '.') }}</div>
            </div>
            @if ($tagihanSiswa->potonganSiswa->isNotEmpty())
                <div class="row">
                    <div class="row-left">Potongan:</div>
                    <div class="row-right">Rp {{ number_format($tagihanSiswa->potonganSiswa->sum('nominal'), 0, ',', '.') }}</div>
                </div>
            @endif
            <div class="divider"></div>
            <div class="total-section">
                <div class="row">
                    <div class="row-left">Jumlah Dibayar:</div>
                </div>
                <div class="total-amount">Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- Metode & Status --}}
        <div class="section">
            <div class="section-title">STATUS PEMBAYARAN</div>
            <div class="row">
                <div class="row-left">Metode:</div>
                <div class="row-right">{{ ucfirst($pembayaran->metode_bayar ?? 'Cash') }}</div>
            </div>
            <div class="row">
                <div class="row-left">Status:</div>
                <div class="row-right">
                    @php
                        $status = match($pembayaran->status_approval) {
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            default => 'Pending'
                        };
                    @endphp
                    {{ $status }}
                </div>
            </div>
            <div class="row">
                <div class="row-left">Petugas:</div>
                <div class="row-right">{{ optional($pembayaran->user)->name ?? '-' }}</div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Footer --}}
        <div class="footer">
            <p>Terima kasih atas pembayaran Anda</p>
            <p style="margin-top: 4px;">{{ now()->format('d/m/Y H:i') }}</p>
            <p style="margin-top: 4px; font-size: 8px; color: #666;">No. Transaksi: {{ $pembayaran->code_pembayaran ?? $pembayaran->id }}</p>
        </div>
    </div>
</body>
</html>
