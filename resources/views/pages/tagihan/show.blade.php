@extends('layouts.app')

@section('title', 'Detail Tagihan')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@endpush
@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">DETAIL TAGIHAN</h3>

        <a href="{{ url()->previous() }}" class="btn btn-secondary rounded-pill mb-3">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>


{{-- Info Siswa --}}
@php
    $siswa = $tagihanSiswa->first()->siswa ?? null;
@endphp
        <div class="col-md-12">
            <div class="card rounded-4 overflow-hidden border-0 pr-4 shadow-lg">
                <div class="row align-items-center g-4">

                    {{-- Kolom kiri: Foto + Nama --}}
                    <div class="col-md-3 profile-card text-center">
                        <div class="profile-photo-wrapper mx-auto mb-3">
<img
    src="{{ $siswa && $siswa->image
        ? asset($siswa->image)
        : asset('assets/images/videa.png') }}"
    class="profile-photo"
    alt="Foto Siswa"
>
                        </div>
                        <h5 class="fw-bold mb-1 text-white" style="font-size: 18px" id="detail_nama">
                        {{ optional($siswa->user)->name ?? '-' }}</h5>
                        <p class="mb-0 opacity-75" id="detail_nisn">
                        ({{ $siswa->nisn ?? '-' }})</p>
                    </div>

                    {{-- Kolom kanan: Detail Siswa --}}
                    <div class="col-md-8 p-4">
                        <ul class="list-unstyled small mb-0 px-4" style="font-size: 14px">
                            <li class="mb-2"><i class="ri-building-line text-primary me-2"></i>
                                <strong>Unit :</strong> <span id="detail_unit" class="float-end">
                                {{ optional($tagihanSiswa->first()->tagihan->unit)->nama_unit ?? '-' }}</span>
                            </li>
                            <li class="mb-2"><i class="ri-book-line text-success me-2"></i>
                                <strong>Kelas :</strong> <span id="detail_kelas" class="float-end">
                                {{ optional($tagihanSiswa->first()->tagihan->kelas)->nama_kelas ?? '-' }}</span>
                            </li>
                            <li class="mb-2"><i class="ri-calendar-line text-warning me-2"></i>
                                <strong>Jenis Tagihan :</strong> <span id="detail_va" class="float-end">
                                {{ ucfirst($tagihanSiswa->first()->tagihan->jenis_tagihan) ?? '-' }}</span>
                            </li>
                            <li class="mb-2"><i class="ri-user-line text-secondary me-2"></i>
                                <strong>Periode :</strong> <span id="detail_bank" class="float-end">
                                {{ $tagihanSiswa->first()->tagihan->periode ?? 1 }} bulan</span>
                            </li>
                            <li class="mb-2"><i class="ri-map-pin-line text-danger me-2"></i>
                                <strong>Bulan Mulai :</strong> <span id="detail_norek" class="float-end">
                                {{ $tagihanSiswa->first()->tagihan->bulan_mulai ?? '-' }}/{{ $tagihanSiswa->first()->tagihan->tahun_mulai ?? '-' }}</span>
                            </li>
                            <li><i class="ri-phone-line text-success me-2"></i>
                                <strong>Telepon :</strong> <span id="detail_telp" class="float-end">{{ $siswa->no_hp }}</span>
                            </li>
                            <li><i class="ri-wallet-line text-success me-2"></i>
                                <strong>No VA :</strong> <span id="detail_telp" class="float-end">{{ $siswa->va_siswa }}</span>
                            </li>

                        </ul>
                    </div>

                </div>
            </div>
        </div>

        {{-- Jumlah Tagihan Seluruh Periode --}}
        <div class="card rounded-3 mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h5>Tagihan Seluruh Periode</h5>
                <div class="table-responsive">
                    <table class="table-bordered table-hover table overflow-hidden text-center align-middle">
                        <thead class="table-primary text-center text-nowrap align-middle">
                            <tr>
                                <th>#</th>
                                <th>Kode Kategori</th>
                                <th>Kode Tagihan</th>
                                <th>No Invoice</th>
                                <th>Kode Pembayaran</th>
                                <th>Nama Tagihan</th>
                                <th>Bulan</th>
                                <th>Biaya Tagihan</th>
                                <th>Nominal Akhir</th>
                                <th>Total Bayar</th>
                                <th>Status</th>
                                <th>Tgl. Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dataPerbulan as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row['kode_kategori'] ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-secondary" title="{{ $row['kode_tagihan'] }}">
                                            {{ substr($row['kode_tagihan'], 0, 50) }}
                                        </span>
                                    </td>
                                    <td>
                                            <span class="badge bg-info" title="{{ $row['no_invoice'] }}">
                                                {{ $row['no_invoice'] }}
                                            </span>
                                    </td>
                                    <td>
                                        @if ($row['status'] === 'Lunas' && $row['kode_pembayaran'])
                                            <span class="badge bg-success">
                                                {{ $row['kode_pembayaran'] }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $row['nama_kategori'] }}</td>
                                    <td>{{ $row['bulan'] }} {{ $row['tahun'] }}</td>
                                    <td>Rp {{ number_format($row['nominal'], 0, ',', '.') }}</td>
                                    <td><strong>Rp {{ number_format($row['nominal_akhir'], 0, ',', '.') }}</strong></td>
                                    <td>
                                        @if ($row['total_bayar'] > 0)
                                            <strong class="text-success">Rp {{ number_format($row['total_bayar'], 0, ',', '.') }}</strong>
                                            @if ($row['status'] !== 'Lunas' && $row['total_bayar'] < $row['nominal_akhir'])
                                                <br><small class="text-warning">(Cicilan)</small>
                                            @endif
                                        @else
                                            <span class="text-muted">Rp 0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row['status'] === 'Lunas')
                                            <span class="badge bg-success">Lunas</span>
                                        @else
                                            <span class="badge bg-danger">Belum Lunas</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $row['tanggal_bayar'] ? \Carbon\Carbon::parse($row['tanggal_bayar'])->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        @if ($row['status'] === 'Lunas')
                                            <button type="button" class="btn btn-sm btn-success rounded-2"
                                                onclick="cetakInvoice({{ $row['id'] }}, '{{ $row['kode_pembayaran'] }}', '{{ $row['kode_tagihan'] }}')">
                                                <i class="bx bx-printer"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-primary rounded-2"
                                                onclick="cetakStrukPDF({{ $row['id'] }})">
                                                <i class="bx bx-printer"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14">Tidak ada data tagihan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="9" class="text-end">TOTAL DIBAYAR:</th>
                                <th class="text-center">
                                    @php
                                        $totalDibayar = $dataPerbulan->sum('total_bayar');
                                        $totalTagihan = $dataPerbulan->sum('nominal_akhir');
                                    @endphp
                                    <strong class="text-success">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</strong>
                                </th>
                                <th colspan="4"></th>
                            </tr>
                            <tr>
                                <th colspan="9" class="text-end">TOTAL TAGIHAN:</th>
                                <th class="text-center">
                                    <strong class="text-primary">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</strong>
                                </th>
                                <th colspan="4"></th>
                            </tr>
                            <tr class="table-warning">
                                <th colspan="9" class="text-end">SISA TAGIHAN:</th>
                                <th class="text-center">
                                    <strong class="text-danger">Rp {{ number_format($totalTagihan - $totalDibayar, 0, ',', '.') }}</strong>
                                </th>
                                <th colspan="4"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card rounded-3 mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h5>Riwayat Pembayaran</h5>
                <div class="table-responsive">
                    <table class="table-bordered table-hover table overflow-hidden text-center align-middle">
                        <thead class="table-primary text-center text-nowrap align-middle">
                            <tr>
                                <th>#</th>
                                <th>Kode Kategori</th>
                                <th>Bulan</th>
                                <th>Kode Pembayaran</th>
                                <th>Jumlah Bayar</th>
                                <th>Wkt. Transaksi</th>
                                <th>Metode Bayar</th>
                                <th>Status</th>
                                <th>Petugas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pembayaranSiswa as $index => $pembayaran)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $pembayaran['kode_kategori'] ?? '-' }}</td>

                                    <td>{{ $pembayaran['bulan'] ?? '-' }} {{ $pembayaran['tahun'] ?? '' }}</td>
                                    <td>
                                        @if($pembayaran['status_approval'] === 'approved' && $pembayaran['kode_pembayaran'] !== '-')
                                            <span class="badge bg-success">
                                                {{ $pembayaran['kode_pembayaran'] }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><strong>Rp {{ number_format($pembayaran['jumlah_bayar'], 0, ',', '.') }}</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($pembayaran['waktu_transaksi'])->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ ucfirst($pembayaran['metode_bayar']) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColor = match($pembayaran['status_approval']) {
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                default => 'warning'
                                            };
                                            $statusText = match($pembayaran['status_approval']) {
                                                'approved' => 'Disetujui',
                                                'rejected' => 'Ditolak',
                                                default => 'Pending'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">{{ $statusText }}</span>
                                    </td>
                                    <td>{{ $pembayaran['create_by'] ?? '-' }}</td>
                                    <td>
                                        @if($pembayaran['id'])
                                            <button type="button" class="btn btn-sm btn-warning rounded-pill"
                                                onclick="cetakStrukPDF({{ $pembayaran['id'] }})"
                                                title="Cetak Struk">
                                                <i class="bx bx-printer"></i>
                                            </button>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-3">Belum ada pembayaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="4" class="text-end">TOTAL PEMBAYARAN:</th>
                                <th class="text-center">
                                    @php
                                        $totalPembayaran = $pembayaranSiswa->where('status_approval', 'approved')->sum('jumlah_bayar');
                                    @endphp
                                    <strong class="text-success">Rp {{ number_format($totalPembayaran, 0, ',', '.') }}</strong>
                                </th>
                                <th colspan="5"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        function bayarTagihan(tagihanId, bulan, tahun, nominal, kategoriId) {
            if (!confirm(`Yakin ingin bayar ${bulan}/${tahun} sebesar Rp ${parseInt(nominal).toLocaleString('id-ID')} ?`)) {
                return;
            }

            fetch('/pembayaran/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        tagihan_siswa_id: tagihanId,
                        bulan: bulan,
                        tahun: tahun,
                        nominal: nominal,
                        kategori_id: kategoriId,
                        metode: 'manual',
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status == 1) {
                        alert('Pembayaran berhasil!');
                        location.reload(); // reload halaman supaya status berubah
                    } else {
                        alert('Gagal: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Terjadi kesalahan saat membayar.');
                });
        }

        function printStruk(pembayaranId, kodeTagihan, jumlahBayar) {
            // Buka halaman cetak struk dalam tab baru
            window.open(`/pembayaran/${pembayaranId}/print-struk`, '_blank');
        }

        function cetakStrukPDF(pembayaranId) {
            // Validasi parameter
            if (!pembayaranId || pembayaranId === '' || pembayaranId === 'null') {
                alert('Error: ID Pembayaran tidak valid');
                console.error('Invalid pembayaranId:', pembayaranId);
                return;
            }

            const url = `/tagihan/${pembayaranId}/cetak-struk`;
            console.log('Membuka URL:', url);

            // Buka halaman cetak struk PDF dalam tab baru
            const pdfWindow = window.open(url, '_blank');

            if (!pdfWindow || pdfWindow.closed || typeof pdfWindow.closed === 'undefined') {
                alert('Popup browser mungkin diblokir. Silakan izinkan popup untuk domain ini.');
                console.error('Gagal membuka popup');
            }
        }

        function cetakInvoice(tagihanSiswaId, kodePembayaran, kodeTagihan) {
            // Validasi parameter
            if (!tagihanSiswaId || tagihanSiswaId === '' || tagihanSiswaId === 'null') {
                alert('Error: ID Tagihan Siswa tidak valid');
                console.error('Invalid tagihanSiswaId:', tagihanSiswaId);
                return;
            }

            // Encode kode tagihan untuk URL
            const encodedKodeTagihan = encodeURIComponent(kodeTagihan || '');
            const url = `/tagihan/${tagihanSiswaId}/cetak-invoice?kode_tagihan=${encodedKodeTagihan}`;
            console.log('Membuka URL Invoice:', url);
            console.log('Kode Tagihan:', kodeTagihan);

            // Buka halaman cetak invoice PDF dalam tab baru
            const pdfWindow = window.open(url, '_blank');

            if (!pdfWindow || pdfWindow.closed || typeof pdfWindow.closed === 'undefined') {
                alert('Popup browser mungkin diblokir. Silakan izinkan popup untuk domain ini.');
                console.error('Gagal membuka popup');
            }
        }

        {{--function cetakStruklanas(kodeTagihan, bulan, tahun, nominal, namaKategori = '') {--}}
        {{--    // Generate struk untuk tagihan--}}
        {{--    let striped = `--}}
        {{--        <html>--}}
        {{--        <head>--}}
        {{--            <meta charset="utf-8">--}}
        {{--            <style>--}}
        {{--                body {--}}
        {{--                    width: 80mm;--}}
        {{--                    margin: 0;--}}
        {{--                    padding: 5mm;--}}
        {{--                    font-family: 'Courier New', monospace;--}}
        {{--                    font-size: 11px;--}}
        {{--                    line-height: 1.4;--}}
        {{--                }--}}
        {{--                .center { text-align: center; }--}}
        {{--                .bold { font-weight: bold; }--}}
        {{--                .divider { border-top: 1px dashed #000; margin: 5px 0; }--}}
        {{--                .text-right { text-align: right; }--}}
        {{--                .row { display: flex; justify-content: space-between; margin: 3px 0; }--}}
        {{--                .label { width: 50%; }--}}
        {{--                .value { width: 50%; text-align: right; }--}}
        {{--            </style>--}}
        {{--        </head>--}}
        {{--        <body>--}}
        {{--            <div class="center bold" style="font-size: 13px; margin-bottom: 3px;">STRUK TAGIHAN</div>--}}
        {{--            <div class="divider"></div>--}}

        {{--            <div class="row">--}}
        {{--                <div class="label bold">Kode Tagihan:</div>--}}
        {{--                <div class="value">${kodeTagihan}</div>--}}
        {{--            </div>--}}
        {{--            <div class="row">--}}
        {{--                <div class="label">Jenis:</div>--}}
        {{--                <div class="value">${namaKategori}</div>--}}
        {{--            </div>--}}
        {{--            <div class="row">--}}
        {{--                <div class="label">Periode:</div>--}}
        {{--                <div class="value">${bulan}/${tahun}</div>--}}
        {{--            </div>--}}
        {{--            <div class="row">--}}
        {{--                <div class="label">Tanggal:</div>--}}
        {{--                <div class="value">${new Date().toLocaleDateString('id-ID')}</div>--}}
        {{--            </div>--}}
        {{--            <div class="row">--}}
        {{--                <div class="label">Waktu:</div>--}}
        {{--                <div class="value">${new Date().toLocaleTimeString('id-ID')}</div>--}}
        {{--            </div>--}}

        {{--            <div class="divider"></div>--}}

        {{--            <div class="row" style="margin: 8px 0;">--}}
        {{--                <div class="label bold">Total Tagihan:</div>--}}
        {{--                <div class="value bold">Rp ${parseInt(nominal).toLocaleString('id-ID')}</div>--}}
        {{--            </div>--}}

        {{--            <div class="divider"></div>--}}

        {{--            <div class="center">--}}
        {{--                <div style="margin-top: 5px;">Terima Kasih</div>--}}
        {{--                <div>Atas Perhatian Anda</div>--}}
        {{--            </div>--}}

        {{--            <script>--}}
        {{--                window.print();--}}
        {{--                window.onafterprint = function() { window.close(); };--}}
        {{--            </script>--}}
        {{--        </body>--}}
        {{--        </html>--}}
        {{--    `;--}}

        {{--    let printWindow = window.open('', '', 'width=300,height=500');--}}
        {{--    printWindow.document.write(striped);--}}
        {{--    printWindow.document.close();--}}
        {{--}--}}
    </script>

@endsection