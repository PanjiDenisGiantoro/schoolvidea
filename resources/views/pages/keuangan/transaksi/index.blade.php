@extends('layouts.app')
@section('title', 'Transaksi Keuangan')

@section('content')
    @include('partials.page-title', [
        'title' => 'Transaksi Keuangan',
        'subTitle' => 'Kelola semua transaksi keuangan',
    ])

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-success">
                    <h6>Total Pemasukan</h6>
                    <h4>Rp {{ number_format($total_pemasukan ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-danger">
                    <h6>Total Pengeluaran</h6>
                    <h4>Rp {{ number_format($total_pengeluaran ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-primary">
                    <h6>Total Transaksi</h6>
                    <h4>{{ number_format($total_transaksi ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Card Utama --}}
    <div class="card rounded-3 border-0 shadow-sm">
        <div class="card-body">
            {{-- Action Buttons --}}
            <div class="d-flex justify-content-end mb-3 flex-wrap gap-2 text-end">
                <a href="{{ route('keuangan_transaksi.print_laporan') }}" target="_blank"
                    class="btn btn-outline-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                    <i class="bx bx-printer"></i> Cetak Laporan
                </a>
            </div>

            {{-- Tabel --}}
            <div class="table-responsive">
                <table class="table-bordered table-hover rounded-3 table overflow-hidden text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Kode Pembayaran</th>
                            <th>Tanggal</th>
                            <th>Jenis Transaksi</th>
                            <th>Penerima</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaksis as $transaksi)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $transaksi->code_pembayaran }}</span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y') }}</td>
                                <td>
                                    @php
                                        $badgeColor = match($transaksi->jenis_transaksi) {
                                            'setoran_tabungan' => 'success',
                                            'penarikan_tabungan' => 'warning',
                                            'pembayaran' => 'info',
                                            'tagihan' => 'info',
                                            default => 'secondary',
                                        };
                                        $jenisText = match($transaksi->jenis_transaksi) {
                                            'setoran_tabungan' => 'Setoran Tabungan',
                                            'penarikan_tabungan' => 'Penarikan Tabungan',
                                            'pembayaran' => 'Pembayaran',
                                            'tagihan' => 'Pembayaran Tagihan',
                                            default => ucwords(str_replace('_', ' ', $transaksi->jenis_transaksi)),
                                        };
                                    @endphp
                                    <span class="badge rounded-pill bg-{{ $badgeColor }}">
                                        {{ $jenisText }}
                                    </span>
                                    @if(in_array($transaksi->jenis_transaksi, ['tagihan', 'pembayaran']) && $transaksi->pembayaranTagihan)
                                        <br>
                                        <small class="text-muted">
                                            @if($transaksi->pembayaranTagihan->tagihanSiswa && $transaksi->pembayaranTagihan->tagihanSiswa->tagihan)
                                                {{ $transaksi->pembayaranTagihan->tagihanSiswa->tagihan->nama_tagihan ?? '-' }}
                                            @endif
                                        </small>
                                        @if($transaksi->pembayaranTagihan->tagihanSiswa && $transaksi->pembayaranTagihan->tagihanSiswa->tagihan && $transaksi->pembayaranTagihan->tagihanSiswa->tagihan->items->count() > 0)
                                            <br>
                                            <small class="text-muted">
                                                @foreach($transaksi->pembayaranTagihan->tagihanSiswa->tagihan->items as $item)
                                                    <span class="badge badge-sm bg-light text-dark">{{ $item->kategori->nama_kategori ?? '-' }}</span>
                                                @endforeach
                                            </small>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($transaksi->penerima)
                                        @if($transaksi->penerima_tipe === 'App\Models\Siswa')
                                            {{ $transaksi->penerima->user->name ?? '-' }}
                                            <br>
                                            <small class="text-muted">NISN: {{ $transaksi->penerima->nisn ?? '-' }}</small>
                                        @else
                                            {{ $transaksi->penerima->name ?? '-' }}
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(in_array($transaksi->jenis_transaksi, ['setoran_tabungan', 'pembayaran']))
                                        <span class="text-success fw-bold">+ Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-danger fw-bold">- Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $metodeBadge = match($transaksi->metode) {
                                            'CASH' => 'primary',
                                            'TRANSFER' => 'info',
                                            'SALDO_TABUNGAN' => 'warning',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $metodeBadge }}">{{ $transaksi->metode }}</span>
                                </td>
                                <td>{{ $transaksi->creator->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('keuangan_transaksi.show', $transaksi->id) }}"
                                        class="btn btn-sm btn-primary rounded-pill"
                                        title="Lihat Detail">
                                        <i class="bx bx-show"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Belum ada data transaksi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .animate-btn {
            transition: all 0.3s ease-in-out;
        }

        .animate-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
        }
    </style>
@endpush
