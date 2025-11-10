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

    {{-- Filter Card --}}
    <div class="card rounded-3 border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold text-primary mb-3">
                <i class="bx bx-filter"></i> Filter Transaksi
            </h5>
            <form action="{{ route('keuangan_transaksi.index') }}" method="GET">
                <div class="row g-3">
                    {{-- Filter Unit --}}
                    <div class="col-md-3">
                        <label for="unit_id" class="form-label">Unit</label>
                        <select name="unit_id" id="unit_id" class="form-select">
                            <option value="">Pilih Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Jenis Transaksi --}}
                    <div class="col-md-3">
                        <label for="jenis_transaksi" class="form-label">Jenis Transaksi</label>
                        <select name="jenis_transaksi" id="jenis_transaksi" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="setoran_tabungan" {{ request('jenis_transaksi') == 'setoran_tabungan' ? 'selected' : '' }}>
                                Setoran Tabungan
                            </option>
                            <option value="penarikan_tabungan" {{ request('jenis_transaksi') == 'penarikan_tabungan' ? 'selected' : '' }}>
                                Penarikan Tabungan
                            </option>
                            <option value="pembayaran" {{ request('jenis_transaksi') == 'pembayaran' ? 'selected' : '' }}>
                                Pembayaran
                            </option>
                            <option value="tagihan" {{ request('jenis_transaksi') == 'tagihan' ? 'selected' : '' }}>
                                Pembayaran Tagihan
                            </option>
                        </select>
                    </div>


                    {{-- Filter Kode Pembayaran --}}
                    <div class="col-md-3">
                        <label for="kode_pembayaran" class="form-label">Kode Pembayaran</label>
                        <input type="text" name="kode_pembayaran" id="kode_pembayaran"
                               class="form-control p-3" placeholder="Cari kode pembayaran"
                               value="{{ request('kode_pembayaran') }}">
                    </div>

                    {{-- Filter Nama Siswa --}}
                    <div class="col-md-3">
                        <label for="nama_siswa" class="form-label">Nama/NISN Siswa</label>
                        <input type="text" name="nama_siswa" id="nama_siswa"
                               class="form-control p-3" placeholder="Cari nama atau NISN"
                               value="{{ request('nama_siswa') }}">
                    </div>

                    {{-- Filter Tanggal Dari --}}
                    <div class="col-md-3">
                        <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                        <input type="text" name="dari_tanggal" id="dari_tanggal"
                               class="form-control datepicker p-3" placeholder="DD/MM/YYYY"
                               value="{{ request('dari_tanggal') ? \Carbon\Carbon::parse(request('dari_tanggal'))->format('d/m/Y') : '' }}">
                    </div>

                    {{-- Filter Tanggal Sampai --}}
                    <div class="col-md-3">
                        <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                        <input type="text" name="sampai_tanggal" id="sampai_tanggal"
                               class="form-control datepicker p-3" placeholder="DD/MM/YYYY"
                               value="{{ request('sampai_tanggal') ? \Carbon\Carbon::parse(request('sampai_tanggal'))->format('d/m/Y') : '' }}">
                    </div>

                    {{-- Tombol Filter --}}
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-search"></i> Filter
                        </button>
                        <a href="{{ route('keuangan_transaksi.index') }}" class="btn btn-secondary">
                            <i class="bx bx-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Card Utama --}}
    <div class="card rounded-3 border-0 shadow-sm">
        <div class="card-body">
            {{-- Action Buttons & Pagination Control --}}
            <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <label for="per_page" class="mb-0">Tampilkan:</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                    </select>
                    <span class="text-muted">data per halaman</span>
                </div>
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
                            <th>Siswa</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>Petugas</th>
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
                                    @if(in_array($transaksi->jenis_transaksi, ['setoran_tabungan', 'pembayaran', 'tagihan']))
                                        <span class="text-success fw-bold">+ Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-danger fw-bold">- Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $metodeBadge = match($transaksi->metode) {
                                            'TUNAI' => 'primary',
                                            'CASH' => 'primary',
                                            'TRANSFER' => 'info',
                                            'NONTUNAI' => 'info',
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

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $transaksis->firstItem() ?? 0 }} sampai {{ $transaksis->lastItem() ?? 0 }} dari {{ $transaksis->total() }} data
                </div>
                <div>
                    {{ $transaksis->links('vendor.pagination.custom') }}
                </div>
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

@push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        function changePerPage(perPage) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.delete('page'); // Reset to page 1
            window.location.href = url.toString();
        }

        // Initialize datepicker dengan format DD/MM/YYYY
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('.datepicker', {
                dateFormat: 'd/m/Y',
                allowInput: true,
                onChange: function(selectedDates, dateStr, instance) {
                    // Convert DD/MM/YYYY to YYYY-MM-DD for backend
                    if (selectedDates.length > 0) {
                        const date = selectedDates[0];
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        const formattedDate = `${year}-${month}-${day}`;

                        // Set the actual input value to backend format
                        if (instance.element.id === 'dari_tanggal') {
                            instance.element.setAttribute('data-value', formattedDate);
                        } else if (instance.element.id === 'sampai_tanggal') {
                            instance.element.setAttribute('data-value', formattedDate);
                        }
                    }
                }
            });

            // Convert dates before form submission
            document.querySelector('form').addEventListener('submit', function(e) {
                const dariTanggal = document.getElementById('dari_tanggal');
                const sampaiTanggal = document.getElementById('sampai_tanggal');

                // Convert dari_tanggal
                if (dariTanggal.value) {
                    const dataValue = dariTanggal.getAttribute('data-value');
                    if (dataValue) {
                        dariTanggal.value = dataValue;
                    } else {
                        const parts = dariTanggal.value.split('/');
                        if (parts.length === 3) {
                            dariTanggal.value = `${parts[2]}-${parts[1]}-${parts[0]}`;
                        }
                    }
                }

                // Convert sampai_tanggal
                if (sampaiTanggal.value) {
                    const dataValue = sampaiTanggal.getAttribute('data-value');
                    if (dataValue) {
                        sampaiTanggal.value = dataValue;
                    } else {
                        const parts = sampaiTanggal.value.split('/');
                        if (parts.length === 3) {
                            sampaiTanggal.value = `${parts[2]}-${parts[1]}-${parts[0]}`;
                        }
                    }
                }
            });
        });
    </script>
@endpush
