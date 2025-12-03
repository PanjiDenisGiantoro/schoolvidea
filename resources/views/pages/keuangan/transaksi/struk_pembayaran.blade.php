<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $transaksi->code_pembayaran }}</title>
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
            padding: 5px;
        }

        .struk-container {
            width: 100%;
            max-width: 80mm;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .header {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .header p {
            font-size: 10px;
            margin: 2px 0;
        }

        .section {
            margin: 8px 0;
            padding: 5px 0;
        }

        .section-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin: 3px 0;
        }

        .info-label {
            display: table-cell;
            width: 35%;
            font-weight: normal;
        }

        .info-value {
            display: table-cell;
            width: 65%;
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .divider-solid {
            border-top: 1px solid #000;
            margin: 8px 0;
        }

        .total-section {
            margin: 10px 0;
            padding: 8px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        .total-row {
            display: table;
            width: 100%;
            margin: 5px 0;
        }

        .total-label {
            display: table-cell;
            width: 50%;
            font-size: 12px;
            font-weight: bold;
        }

        .total-value {
            display: table-cell;
            width: 50%;
            text-align: right;
            font-size: 12px;
            font-weight: bold;
        }

        .footer {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }

        .item-table {
            width: 100%;
            margin: 5px 0;
            font-size: 10px;
        }

        .item-table th {
            border-bottom: 1px solid #000;
            padding: 3px 0;
            text-align: left;
            font-weight: bold;
        }

        .item-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .item-table .text-right {
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border: 1px solid #000;
            font-weight: bold;
            font-size: 10px;
            margin: 3px 0;
        }

        .qr-code {
            text-align: center;
            margin: 10px 0;
        }

        .bold {
            font-weight: bold;
        }

        .small {
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="struk-container">
        {{-- Header Sekolah --}}
        <div class="header text-center">
            <h2>{{ $transaksi->penerima->unit->nama_unit ?? 'SEKOLAH' }}</h2>
            <p>{{ $transaksi->penerima->unit->alamat ?? '-' }}</p>
            @if(isset($transaksi->penerima->unit->no_telepon))
            <p>Telp: {{ $transaksi->penerima->unit->no_telepon }}</p>
            @endif
        </div>

        {{-- Judul Struk --}}
        <div class="text-center section">
            <div class="bold" style="font-size: 12px;">BUKTI PEMBAYARAN</div>
            @if($isMultiple)
                <div class="small">PEMBAYARAN TAGIHAN MULTIPLE</div>
            @else
                <div class="small">{{ strtoupper($transaksi->jenis_transaksi === 'tagihan' || $transaksi->jenis_transaksi === 'pembayaran' ? 'PEMBAYARAN TAGIHAN' : str_replace('_', ' ', $transaksi->jenis_transaksi)) }}</div>
            @endif
        </div>

        <div class="divider"></div>

        {{-- Informasi Transaksi --}}
        @php
            // Determine if this is multiple or single payment
            $isMultiple = false;
            $headTagihan = null;
            $pembayaranDetail = null;

            if (in_array($transaksi->jenis_transaksi, ['tagihan', 'pembayaran']) && $transaksi->pembayaranTagihan) {
                if ($transaksi->pembayaranTagihan->is_master === true && $transaksi->pembayaranTagihan->head_tagihan) {
                    $isMultiple = true;
                    $headTagihan = $transaksi->pembayaranTagihan->head_tagihan;
                    $pembayaranDetail = \App\Models\PembayaranTagihanDetail::where('head_tagihan', $headTagihan)->get();
                }
            }
        @endphp

        <div class="section">
            <div class="info-row">
                <div class="info-label">No. Transaksi</div>
                <div class="info-value">:
                    @if($isMultiple)
                        {{ $headTagihan }}
                    @else
                        {{ $transaksi->code_pembayaran }}
                    @endif
                </div>
            </div>
            @if($isMultiple)
            <div class="info-row">
                <div class="info-label">Kode Pembayaran</div>
                <div class="info-value">: {{ $transaksi->code_pembayaran }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Tanggal</div>
                <div class="info-value">: {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Kasir</div>
                <div class="info-value">: {{ $transaksi->creator->name ?? '-' }}</div>
            </div>
            @if($isMultiple)
            <div class="info-row">
                <div class="info-label">Jenis</div>
                <div class="info-value">: PEMBAYARAN MULTIPLE</div>
            </div>
            <div class="info-row">
                <div class="info-label">Jumlah Tagihan</div>
                <div class="info-value">: {{ $pembayaranDetail->count() }} Tagihan</div>
            </div>
            @endif
        </div>

        <div class="divider"></div>

        {{-- Informasi Siswa --}}
        <div class="section">
            <div class="section-title">DATA SISWA</div>
            <div class="info-row">
                <div class="info-label">Nama</div>
                <div class="info-value">: {{ $transaksi->penerima->user->name ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">NISN</div>
                <div class="info-value">: {{ $transaksi->penerima->nisn ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Kelas</div>
                <div class="info-value">: {{ $transaksi->penerima->kelas->nama_kelas ?? '-' }}</div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Detail Pembayaran --}}
        <div class="section">
            @if($isMultiple)
                {{-- PEMBAYARAN MULTIPLE --}}
                <div class="section-title">RINCIAN PEMBAYARAN MULTIPLE ({{ $pembayaranDetail->count() }} Tagihan)</div>
                <table class="item-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">No</th>
                            <th style="width: 50%;">Nama Tagihan</th>
                            <th style="width: 40%;" class="text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalDetail = 0; @endphp
                        @forelse($pembayaranDetail as $detail)
                            <tr>
                                <td>{{ $detail->urutan }}</td>
                                <td>
                                    {{ $detail->tagihanSiswa->tagihan->jenis_tagihan ?? 'Tagihan' }}
                                    <br><small style="font-size: 9px;">Periode: {{ $detail->periode ?? '-' }} Tahun {{ $detail->tahun ?? '-' }}</small>
                                </td>
                                <td class="text-right bold">Rp {{ number_format($detail->jumlah_bayar_detail, 0, ',', '.') }}</td>
                            </tr>
                            @php $totalDetail += $detail->jumlah_bayar_detail; @endphp
                        @empty
                            <tr>
                                <td colspan="3" class="text-center small">Tidak ada detail tagihan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Additional info for multiple payments --}}
                <div style="margin-top: 8px; padding: 5px; background: #f5f5f5; border: 1px dashed #000;">
                    <div class="small bold">CATATAN PEMBAYARAN MULTIPLE:</div>
                    <div class="small" style="margin-top: 3px;">
                        - Pembayaran ini mencakup {{ $pembayaranDetail->count() }} item tagihan<br>
                        - Setiap item akan diproses sesuai urutan<br>
                        - Total pembayaran: Rp {{ number_format($totalDetail, 0, ',', '.') }}
                    </div>
                </div>

            @elseif(in_array($transaksi->jenis_transaksi, ['pembayaran', 'tagihan']) && $transaksi->pembayaranTagihan)
                {{-- PEMBAYARAN SINGLE --}}
                <div class="section-title">RINCIAN PEMBAYARAN</div>
                <table class="item-table">
                    <thead>
                        <tr>
                            <th style="width: 60%;">Item</th>
                            <th style="width: 40%;" class="text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $transaksi->pembayaranTagihan->tagihanSiswa->tagihan->nama_tagihan ?? 'Tagihan' }}</td>
                            <td class="text-right bold">Rp {{ number_format($transaksi->pembayaranTagihan->jumlah_bayar, 0, ',', '.') }}</td>
                        </tr>
                        @if($transaksi->pembayaranTagihan->tagihanSiswa->tagihan->items->count() > 0)
                            <tr>
                                <td colspan="2" class="small" style="padding-left: 5px;">
                                    Detail:
                                    @foreach($transaksi->pembayaranTagihan->tagihanSiswa->tagihan->items as $item)
                                        <br>- {{ $item->kategori->nama_kategori ?? 'Item' }}
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>

            @elseif($transaksi->jenis_transaksi === 'setoran_tabungan')
                {{-- Setoran Tabungan --}}
                <div class="section-title">RINCIAN PEMBAYARAN</div>
                <div class="info-row">
                    <div class="info-label">Setoran Tabungan</div>
                    <div class="info-value text-right bold">Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</div>
                </div>

            @elseif($transaksi->jenis_transaksi === 'penarikan_tabungan')
                {{-- Penarikan Tabungan --}}
                <div class="section-title">RINCIAN PEMBAYARAN</div>
                <div class="info-row">
                    <div class="info-label">Penarikan Tabungan</div>
                    <div class="info-value text-right bold">Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</div>
                </div>
            @endif
        </div>

        <div class="divider-solid"></div>

        {{-- Total Pembayaran --}}
        <div class="total-section">
            <div class="total-row">
                <div class="total-label">TOTAL BAYAR</div>
                <div class="total-value">Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</div>
            </div>
            <div class="total-row" style="margin-top: 3px;">
                <div class="total-label">Metode</div>
                <div class="total-value">{{ strtoupper($transaksi->metode) }}</div>
            </div>
        </div>

        {{-- Status Verifikasi --}}
        <div class="section text-center">
            <div class="status-badge">
                STATUS: {{ strtoupper($transaksi->status_verifikasi) }}
            </div>
        </div>

        <div class="divider"></div>

        {{-- Footer --}}
        <div class="footer text-center">
            <p class="bold">Terima kasih atas pembayarannya</p>
            <p class="small" style="margin-top: 5px;">Struk ini adalah bukti pembayaran yang sah</p>
            <p class="small">Harap simpan sebagai bukti</p>
            <p class="small" style="margin-top: 8px;">Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
            <p class="small">{{ url()->current() }}</p>
        </div>

        {{-- QR Code placeholder (optional) --}}
        <div class="qr-code">
            <p class="small">{{ $transaksi->code_pembayaran }}</p>
        </div>
    </div>
</body>
</html>
