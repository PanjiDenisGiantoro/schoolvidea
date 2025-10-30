@extends('layouts.app')
@section('title', 'Detail Transaksi')

@section('content')
    @include('partials.page-title', [
        'title' => 'Detail Transaksi',
        'subTitle' => 'Keuangan / Transaksi'
    ])

    <div class="row g-4">
        {{-- Detail Transaksi --}}
        <div class="col-md-5">
            <div class="card p-4 shadow-sm rounded-4 border-0">
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bx bx-receipt"></i> Informasi Transaksi
                </h5>
                <hr>

                <div class="mb-3">
                    <span class="badge bg-secondary fs-6">{{ $transaksi->code_pembayaran }}</span>
                </div>

                <ul class="list-unstyled">
                    <li class="mb-2">
                        <strong>Jenis Transaksi:</strong><br>
                        @php
                            $badgeColor = match($transaksi->jenis_transaksi) {
                                'setoran_tabungan' => 'success',
                                'penarikan_tabungan' => 'warning',
                                'pembayaran' => 'info',
                                default => 'secondary',
                            };
                            $jenisText = match($transaksi->jenis_transaksi) {
                                'setoran_tabungan' => 'Setoran Tabungan',
                                'penarikan_tabungan' => 'Penarikan Tabungan',
                                'pembayaran' => 'Pembayaran',
                                default => ucwords(str_replace('_', ' ', $transaksi->jenis_transaksi)),
                            };
                        @endphp
                        <span class="badge rounded-pill bg-{{ $badgeColor }}">{{ $jenisText }}</span>
                    </li>

                    <li class="mb-2">
                        <strong>Jumlah:</strong><br>
                        @if(in_array($transaksi->jenis_transaksi, ['setoran_tabungan', 'pembayaran']))
                            <span class="text-success fw-bold fs-5">+ Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</span>
                        @else
                            <span class="text-danger fw-bold fs-5">- Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</span>
                        @endif
                    </li>

                    <li class="mb-2">
                        <strong>Metode:</strong><br>
                        @php
                            $metodeBadge = match($transaksi->metode) {
                                'CASH' => 'primary',
                                'TRANSFER' => 'info',
                                'SALDO_TABUNGAN' => 'warning',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $metodeBadge }}">{{ $transaksi->metode }}</span>
                    </li>

                    <li class="mb-2">
                        <strong>Tanggal Transaksi:</strong><br>
                        {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d F Y') }}
                    </li>

                    @if($transaksi->referensi_tagihan_id)
                    <li class="mb-2">
                        <strong>Referensi Tagihan:</strong><br>
                        {{ $transaksi->referensi_tagihan_id }}
                    </li>
                    @endif

                    @if($transaksi->keterangan)
                    <li class="mb-2">
                        <strong>Keterangan:</strong><br>
                        {{ $transaksi->keterangan }}
                    </li>
                    @endif
                </ul>

                <hr>

                {{-- Detail Tagihan jika jenis transaksi adalah tagihan/pembayaran --}}
                @if(in_array($transaksi->jenis_transaksi, ['tagihan', 'pembayaran']) && $transaksi->pembayaranTagihan)
                <h6 class="fw-bold text-primary mb-2">
                    <i class="bx bx-receipt"></i> Detail Tagihan
                </h6>
                <ul class="list-unstyled small">
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
                        <li class="mt-2"><strong>Kategori Tagihan:</strong></li>
                        <ul class="mt-1">
                            @foreach($tagihan->items as $item)
                                <li>
                                    {{ $item->kategori->nama_kategori ?? '-' }}
                                    - Rp {{ number_format($item->nominal ?? 0, 0, ',', '.') }}
                                </li>
                            @endforeach
                        </ul>
                        @endif
                    @else
                        <li class="text-muted">Detail tagihan tidak tersedia</li>
                    @endif
                </ul>

                <hr>
                @endif

                <h6 class="fw-bold text-primary mb-2">
                    <i class="bx bx-user"></i> Penerima
                </h6>
                @if($transaksi->penerima)
                    @if($transaksi->penerima_tipe === 'App\Models\Siswa')
                        <ul class="list-unstyled small">
                            <li><strong>Nama:</strong> {{ $transaksi->penerima->user->name ?? '-' }}</li>
                            <li><strong>NISN:</strong> {{ $transaksi->penerima->nisn ?? '-' }}</li>
                            <li><strong>Kelas:</strong> {{ $transaksi->penerima->kelas->nama_kelas ?? '-' }}</li>
                            <li><strong>Unit:</strong> {{ $transaksi->penerima->unit->nama_unit ?? '-' }}</li>
                        </ul>
                    @else
                        <p class="small">{{ $transaksi->penerima->name ?? '-' }}</p>
                    @endif
                @else
                    <p class="text-muted small">-</p>
                @endif

                <hr>

                <ul class="list-unstyled small text-muted">
                    <li><strong>Dibuat Oleh:</strong> {{ $transaksi->creator->name ?? '-' }}</li>
                    <li><strong>Dibuat Pada:</strong> {{ \Carbon\Carbon::parse($transaksi->created_at)->format('d/m/Y H:i:s') }}</li>
                    @if($transaksi->updated_at != $transaksi->created_at)
                    <li><strong>Terakhir Update:</strong> {{ \Carbon\Carbon::parse($transaksi->updated_at)->format('d/m/Y H:i:s') }}</li>
                    @endif
                </ul>

                <div class="mt-3">
                    <a href="{{ route('keuangan_transaksi.index') }}" class="btn btn-secondary w-100 rounded-pill shadow-sm">
                        <i class="bx bx-arrow-back"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        {{-- History Jurnal --}}
        <div class="col-md-7">
            <div class="card p-4 shadow-sm rounded-4 border-0">
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bx bx-book"></i> History Jurnal
                </h5>
                <hr>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Akun</th>
                                <th>Debit</th>
                                <th>Kredit</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaksi->jurnals as $jurnal)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $jurnal->akun->kode_akun ?? '-' }}</strong><br>
                                        <small class="text-muted">{{ $jurnal->akun->nama_akun ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($jurnal->debit > 0)
                                            <span class="text-success fw-bold">Rp {{ number_format($jurnal->debit, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($jurnal->kredit > 0)
                                            <span class="text-danger fw-bold">Rp {{ number_format($jurnal->kredit, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $jurnal->keterangan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada jurnal</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">Total:</th>
                                <th class="text-success">Rp {{ number_format($transaksi->jurnals->sum('debit'), 0, ',', '.') }}</th>
                                <th class="text-danger">Rp {{ number_format($transaksi->jurnals->sum('kredit'), 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Activity Logs --}}
            @if(isset($logs) && $logs->count() > 0)
            <div class="card p-4 shadow-sm rounded-4 border-0 mt-4">
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bx bx-history"></i> Activity Log
                </h5>
                <hr>

                <div class="timeline">
                    @foreach($logs as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex gap-3">
                                <div>
                                    @php
                                        $iconColor = match($log->aksi) {
                                            'create' => 'success',
                                            'update' => 'warning',
                                            'delete' => 'danger',
                                            default => 'secondary',
                                        };
                                        $iconClass = match($log->aksi) {
                                            'create' => 'bx-plus-circle',
                                            'update' => 'bx-edit',
                                            'delete' => 'bx-trash',
                                            default => 'bx-info-circle',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $iconColor }} rounded-circle p-2">
                                        <i class="bx {{ $iconClass }}"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <strong class="text-capitalize">{{ $log->aksi }}</strong>
                                    oleh {{ $log->pelaku->name ?? '-' }}
                                    <br>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($log->dilakukan_pada)->format('d/m/Y H:i:s') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .timeline {
            position: relative;
        }
        .timeline-item {
            position: relative;
            padding-left: 10px;
        }
    </style>
@endpush