<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi - {{ $transaksi->code_pembayaran }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #333;
        }

        .header h1 {
            font-size: 22px;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .header h2 {
            font-size: 16px;
            color: #7f8c8d;
            font-weight: normal;
            margin-bottom: 10px;
        }

        .header .code {
            font-size: 14px;
            font-weight: 600;
            background: #f8f9fa;
            padding: 8px 15px;
            display: inline-block;
            border-radius: 5px;
            margin-top: 10px;
        }

        /* Info Boxes */
        .info-section {
            margin-bottom: 25px;
        }

        .info-section h3 {
            font-size: 14px;
            margin-bottom: 12px;
            color: #2c3e50;
            padding-bottom: 5px;
            border-bottom: 2px solid #3498db;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .info-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .info-item .label {
            font-size: 10px;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .info-item .value {
            font-size: 13px;
            font-weight: 600;
            color: #2c3e50;
        }

        .info-item.full-width {
            grid-column: 1 / -1;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-primary {
            background: #cce5ff;
            color: #004085;
        }

        .badge-secondary {
            background: #e2e3e5;
            color: #383d41;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background: #2c3e50;
            color: #fff;
        }

        table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
        }

        table td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
            font-size: 11px;
        }

        table tfoot {
            background: #f8f9fa;
            font-weight: 600;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-muted {
            color: #6c757d;
            font-size: 10px;
        }

        .amount-large {
            font-size: 20px;
            font-weight: 700;
        }

        /* Lists */
        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            padding: 6px 0;
            border-bottom: 1px dotted #dee2e6;
        }

        ul li:last-child {
            border-bottom: none;
        }

        ul li strong {
            display: inline-block;
            width: 150px;
            color: #6c757d;
            font-size: 11px;
        }

        /* Signature */
        .signature {
            margin-top: 40px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 50px;
        }

        .signature-box {
            text-align: center;
        }

        .signature-box .label {
            margin-bottom: 60px;
            font-size: 11px;
        }

        .signature-box .name {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-weight: 600;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }

    </style>
</head>
<body>
    <div class="container">

        <!-- Header -->
        <div class="header">
            <h1>BUKTI TRANSAKSI KEUANGAN</h1>
            <h2>{{ Auth::user()->unit->nama_unit ?? config('app.name') }}</h2>
            <div class="code">{{ $transaksi->code_pembayaran }}</div>
        </div>

        <!-- Informasi Transaksi -->
        <div class="info-section">
            <h3>Informasi Transaksi</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Jenis Transaksi</div>
                    <div class="value">
                        @php
                            $badgeClass = match($transaksi->jenis_transaksi) {
                                'setoran_tabungan' => 'badge-success',
                                'penarikan_tabungan' => 'badge-warning',
                                'pembayaran', 'tagihan' => 'badge-info',
                                default => 'badge-secondary',
                            };
                            $jenisText = match($transaksi->jenis_transaksi) {
                                'setoran_tabungan' => 'Setoran Tabungan',
                                'penarikan_tabungan' => 'Penarikan Tabungan',
                                'pembayaran' => 'Pembayaran',
                                'tagihan' => 'Pembayaran Tagihan',
                                default => ucwords(str_replace('_', ' ', $transaksi->jenis_transaksi)),
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $jenisText }}</span>
                    </div>
                </div>

                <div class="info-item">
                    <div class="label">Tanggal Transaksi</div>
                    <div class="value">{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d F Y H:i') }}</div>
                </div>

                <div class="info-item">
                    <div class="label">Jumlah</div>
                    <div class="value">
                        @if(in_array($transaksi->jenis_transaksi, ['setoran_tabungan', 'pembayaran', 'tagihan']))
                            <span class="text-success amount-large">+ Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</span>
                        @else
                            <span class="text-danger amount-large">- Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>

                <div class="info-item">
                    <div class="label">Metode Pembayaran</div>
                    <div class="value">
                        @php
                            $metodeBadge = match($transaksi->metode) {
                                'CASH' => 'badge-primary',
                                'TRANSFER' => 'badge-info',
                                'SALDO_TABUNGAN' => 'badge-warning',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <span class="badge {{ $metodeBadge }}">{{ $transaksi->metode ?? '-' }}</span>
                    </div>
                </div>

                @if($transaksi->keterangan)
                <div class="info-item full-width">
                    <div class="label">Keterangan</div>
                    <div class="value">{{ $transaksi->keterangan }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Detail Tagihan (jika ada) -->
        @if(in_array($transaksi->jenis_transaksi, ['tagihan', 'pembayaran']) && $transaksi->pembayaranTagihan)
        <div class="info-section">
            <h3>Detail Tagihan</h3>
            <ul>
                @if($transaksi->pembayaranTagihan->tagihanSiswa)
                    @php
                        $tagihanSiswa = $transaksi->pembayaranTagihan->tagihanSiswa;
                        $tagihan = $tagihanSiswa->tagihan;
                    @endphp
                    <li><strong>Nama Tagihan:</strong> {{ $tagihan->nama_tagihan ?? '-' }}</li>
                    <li><strong>Periode:</strong> {{ $tagihan->bulan ?? '-' }} {{ $tagihan->tahun ?? '' }}</li>
                    <li><strong>Total Tagihan:</strong> Rp {{ number_format($tagihanSiswa->nominal ?? 0, 0, ',', '.') }}</li>
                    <li><strong>Dibayar:</strong> Rp {{ number_format($transaksi->pembayaranTagihan->jumlah_bayar ?? 0, 0, ',', '.') }}</li>
                    <li><strong>Sisa:</strong> Rp {{ number_format($tagihanSiswa->sisa_nominal ?? 0, 0, ',', '.') }}</li>

                    @if($tagihan && $tagihan->items && $tagihan->items->count() > 0)
                    <li style="margin-top: 10px;"><strong>Kategori Tagihan:</strong></li>
                    @foreach($tagihan->items as $item)
                        <li style="padding-left: 20px;">• {{ $item->kategori->nama_kategori ?? '-' }} - Rp {{ number_format($item->nominal ?? 0, 0, ',', '.') }}</li>
                    @endforeach
                    @endif
                @endif
            </ul>
        </div>
        @endif

        <!-- Informasi Penerima -->
        <div class="info-section">
            <h3>Informasi Penerima</h3>
            <ul>
                @if($transaksi->penerima)
                    @if($transaksi->penerima_tipe === 'App\Models\Siswa')
                        <li><strong>Nama:</strong> {{ $transaksi->penerima->user->name ?? '-' }}</li>
                        <li><strong>NISN:</strong> {{ $transaksi->penerima->nisn ?? '-' }}</li>
                        <li><strong>Kelas:</strong> {{ $transaksi->penerima->kelas->nama_kelas ?? '-' }}</li>
                        <li><strong>Unit:</strong> {{ $transaksi->penerima->unit->nama_unit ?? '-' }}</li>
                    @else
                        <li><strong>Nama:</strong> {{ $transaksi->penerima->name ?? '-' }}</li>
                    @endif
                @else
                    <li>-</li>
                @endif
            </ul>
        </div>

        <!-- Jurnal -->
        @if($transaksi->jurnals && $transaksi->jurnals->count() > 0)
        <div class="info-section">
            <h3>Jurnal Akuntansi</h3>
            <table>
                <thead>
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Debit</th>
                        <th>Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksi->jurnals as $jurnal)
                    <tr>
                        <td>{{ $jurnal->akun->kode_akun ?? '-' }}</td>
                        <td>{{ $jurnal->akun->nama_akun ?? '-' }}</td>
                        <td>
                            @if($jurnal->debit > 0)
                                <span class="text-success">Rp {{ number_format($jurnal->debit, 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($jurnal->kredit > 0)
                                <span class="text-danger">Rp {{ number_format($jurnal->kredit, 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align: right;">Total:</td>
                        <td class="text-success">Rp {{ number_format($transaksi->jurnals->sum('debit'), 0, ',', '.') }}</td>
                        <td class="text-danger">Rp {{ number_format($transaksi->jurnals->sum('kredit'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        <!-- Informasi Tambahan -->
        <div class="info-section">
            <h3>Informasi Tambahan</h3>
            <ul>
                <li><strong>Dibuat Oleh:</strong> {{ $transaksi->creator->name ?? '-' }}</li>
                <li><strong>Dibuat Pada:</strong> {{ \Carbon\Carbon::parse($transaksi->created_at)->format('d F Y H:i:s') }}</li>
                @if($transaksi->updated_at != $transaksi->created_at)
                <li><strong>Terakhir Update:</strong> {{ \Carbon\Carbon::parse($transaksi->updated_at)->format('d F Y H:i:s') }}</li>
                @endif
            </ul>
        </div>

        <!-- Signature -->
        <div class="signature">
            <div class="signature-box">
                <div class="label">Penerima,</div>
                <div class="name">(__________________)</div>
            </div>
            <div class="signature-box">
                <div class="label">{{ \Carbon\Carbon::now()->format('d F Y') }}<br>Petugas,</div>
                <div class="name">({{ $transaksi->creator->name ?? '__________________' }})</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dokumen ini dicetak secara otomatis dari Sistem Informasi Keuangan</p>
            <p>{{ Auth::user()->unit->nama_unit ?? config('app.name') }}</p>
        </div>
    </div>
</body>
</html>