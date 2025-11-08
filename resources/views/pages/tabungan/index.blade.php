@extends('layouts.app')
@section('title', 'Tabungan Siswa')

@section('content')
    @include('partials.page-title', [
        'title' => 'Tabungan Siswa',
        'subTitle' => 'Kelola transaksi tabungan siswa',
    ])

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-success">
                    <h6>Total Setoran</h6>
                    <h4>Rp {{ number_format($total_setoran ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-danger">
                    <h6>Total Penarikan</h6>
                    <h4>Rp {{ number_format($total_penarikan ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-primary">
                    <h6>Saldo Tabungan</h6>
                    <h4>Rp {{ number_format($total_setoran - $total_penarikan ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-info">
                    <h6>Jumlah Transaksi</h6>
                    <h4>{{ number_format($jumlah_transaksi ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Statistics --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-warning">
                    <h6>Total Pending Penarikan</h6>
                    <h4>Rp {{ number_format($total_pending ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-success">
                    <h6>Total Approved Penarikan</h6>
                    <h4>Rp {{ number_format($total_approved ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-danger">
                    <h6>Total Reject Penarikan</h6>
                    <h4>Rp {{ number_format($total_rejected ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    @if(auth()->user()->yayasan_id && !auth()->user()->unit_id || !auth()->user()->yayasan_id && !auth()->user()->unit_id)
    <div class="card rounded-3 border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold text-primary mb-3">
                <i class="bx bx-filter"></i> Filter Tabungan
            </h5>
            <form action="{{ route('tabungan.index') }}" method="GET">
                <div class="row g-3">
                    {{-- Filter Unit --}}
                    <div class="col-md-3">
                        <label for="unit_id" class="form-label">Unit</label>
                        <select name="unit_id" id="unit_id" class="form-select">
                            <option value="">Semua Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Search --}}
                    <div class="col-md-3">
                        <label for="search" class="form-label">Cari Siswa</label>
                        <input type="text" name="search" id="search"
                               class="form-control" placeholder="NISN, Nama, Kelas..."
                               value="{{ request('search') }}">
                    </div>

                    {{-- Filter Tanggal Dari --}}
                    <div class="col-md-2">
                        <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                        <input type="date" name="dari_tanggal" id="dari_tanggal"
                               class="form-control" value="{{ request('dari_tanggal') }}">
                    </div>

                    {{-- Filter Tanggal Sampai --}}
                    <div class="col-md-2">
                        <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                        <input type="date" name="sampai_tanggal" id="sampai_tanggal"
                               class="form-control" value="{{ request('sampai_tanggal') }}">
                    </div>

                    {{-- Tombol Filter --}}
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-search"></i> Filter
                        </button>
                        <a href="{{ route('tabungan.index') }}" class="btn btn-secondary">
                            <i class="bx bx-refresh"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

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
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('tabungan.create') }}"
                        class="btn btn-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                        <i class="bx bx-wallet"></i> Setor
                    </a>

                    <a href="{{ url('tabungan/tarik') }}"
                        class="btn btn-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                        <i class="bx bx-money-withdraw"></i> Tarik
                    </a>

                    <a href="#"
                        class="btn btn-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                        <i class="bx bx-transfer"></i> Transaksi
                    </a>

                    <a href="{{ route('tabungan.print_laporan', request()->all()) }}" target="_blank"
                        class="btn btn-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                        <i class="bx bx-printer"></i> Cetak
                    </a>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="table-responsive">
                <table class="table-bordered table-hover rounded-3 table overflow-hidden text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Nama Unit</th>
                            <th>Nomor Induk</th>
                            <th>Nama Lengkap</th>
                            <th>Kelas Sekarang</th>
                            <th>Tahun Ajaran</th>
                            <th>Status</th>
                            <th>Saldo Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaksis as $siswa)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $siswa->unit->nama_unit ?? '-' }}</td>
                                <td>{{ $siswa->nisn ?? '-' }}</td>
                                <td>{{ $siswa->user->name ?? '-' }}</td>
                                <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $siswa->tahun_ajaran->tahun_ajaran ?? '-' }}</td>
                                <td>
                                    @php
                                        $saldo = $siswa->user->saldo ?? null;
                                        $statusBadge = $saldo && $saldo->status == 1 ? 'primary' : 'secondary';
                                        $statusText = $saldo && $saldo->status == 1 ? 'Aktif' : 'Tidak Aktif';
                                    @endphp
                                    <span class="badge rounded-pill bg-{{ $statusBadge }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td>Rp {{ number_format($saldo->saldo_akhir ?? 0, 0, ',', '.') }}</td>
                                <td class="d-flex gap-2">
                                    <a href="{{ route('tabungan.show', $siswa->id) }}"
                                        class="btn btn-sm btn-outline-primary rounded-pill">Detail</a>

                                    @if ($saldo && $saldo->status == 0)
                                        <a href="{{ route('tabungan.status', $saldo->id) }}"
                                            class="btn btn-sm btn-primary rounded-pill">Aktif</a>
                                    @elseif($saldo)
                                        <a href="{{ route('tabungan.status', $saldo->id) }}"
                                            class="btn btn-sm btn-danger rounded-pill">Non Aktif</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Belum ada data siswa</td>
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
                    {{ $transaksis->links() }}
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
    <script>
        function changePerPage(perPage) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.delete('page'); // Reset to page 1
            window.location.href = url.toString();
        }
    </script>
@endpush
