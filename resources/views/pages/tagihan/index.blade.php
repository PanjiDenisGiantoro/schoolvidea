@extends('layouts.app')

@section('title', 'Kelola Tagihan')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@endpush
@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">KELOLA TAGIHAN</h3>

        {{-- Action Buttons & Pagination Control --}}


        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card rounded-3 border-0 text-center text-white bg-info shadow-sm">
                    <div class="card-body">
                        <h6 class="text-white fw-bold" style="font-size: 14px">Jumlah Data</h6>
                        <h4 class="text-white">{{ $summary['jumlah_data'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-3 border-0 text-center text-white bg-success shadow-sm">
                    <div class="card-body">
                        <h6 class="text-white fw-bold" style="font-size: 14px">Nominal Tagihan</h6>
                        <h4 class="text-white">Rp {{ number_format($summary['nominal_tagihan'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-3 border-0 text-center shadow-sm text-white bg-warning">
                    <div class="card-body">
                        <h6 class="text-white fw-bold" style="font-size: 14px">Sudah Dibayar</h6>
                        <h4 class="text-white">Rp {{ number_format($summary['sudah_dibayar'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-3 border-0 text-center shadow-sm bg-danger">
                    <div class="card-body text-primary">
                        <h6 class="text-white fw-bold" style="font-size: 14px">Belum Dibayar</h6>
                        <h4 class="text-white">Rp {{ number_format($summary['belum_dibayar'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alert jika ada error --}}
        @if (session('error'))
            <div class="alert alert-danger rounded-3 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter --}}
        @if(auth()->user()->yayasan_id && !auth()->user()->unit_id || !auth()->user()->yayasan_id && !auth()->user()->unit_id)
        <div class="card rounded-3 mb-3 border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bx bx-filter"></i> Filter Tagihan
                </h5>
                <form method="GET" action="{{ route('tagihan.index') }}">
                    <div class="row g-3 align-items-end">
                        {{-- Filter Search --}}
                        <div class="col-md-2">
                            <label for="search" class="form-label">Cari Tagihan</label>
                            <input type="text" name="search" id="search"
                                   class="form-control p-3" placeholder="Nama tagihan, kelas..."
                                   value="{{ request('search') }}">
                        </div>
                        {{-- Filter Unit --}}
                        <div class="col-md-2">
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
                        <div class="col-md-2">
                            <label for="kelas_id" class="form-label">Kelas</label>

                            <select name="kelas_id" id="kelas_id" class="form-select"
                                @if (isset($show) && $show) disabled @endif>
                                <option value="">Semua Kelas</option>
                            </select>
                        </div>

                        {{-- Filter Tanggal Dari --}}
                        <div class="col-md-2">
                            <label class="form-label">Bulan Periode</label>
                            <select name="bulan_mulai" class="form-select" required>
                                <option value="">Pilih Bulan</option>
                                @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $bulan)
                                    <option value="{{ $i + 1 }}">{{ $bulan }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Tanggal Sampai --}}
                        <div class="col-md-2">
                            <label class="form-label">Tahun Periode</label>
                            <select name="tahun_mulai" class="form-select" required>
                                <option value="">Pilih Tahun</option>
                                @for ($y = date('Y'); $y <= date('Y') + 5; $y++)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status Tagihan</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Pilih Status</option>
                                <option value="Lunas">Lunas</option>
                                <option value="Belum Lunas">Belum Lunas</option>
                            </select>
                        </div>

                        {{-- Tombol Filter --}}
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search"></i> Filter
                            </button>
                            <a href="{{ route('tagihan.index') }}" class="btn btn-secondary">
                                <i class="bx bx-refresh"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @else

        <div class="card rounded-3 mb-3 border-0 shadow-sm">
            <div class="card-body">
                <form method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Filter Data</label>
                            <select name="filter" class="form-control rounded-pill">
                                <option value="all">All</option>
                                <option value="lunas">Lunas</option>
                                <option value="belum">Belum Lunas</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" class="form-control rounded-pill"
                                value="{{ request('tanggal') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Tabel --}}
        <div class="card rounded-3 border-0 shadow-sm">

            <div class="card-body">
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
                    <div class="d-flex gap-2">
                        <a href="{{ url('tagihan/create') }}" class="btn btn-primary rounded-pill shadow-sm">
                            <i class="fa fa-plus"></i> Tambah
                        </a>
                        <a href="#" class="btn btn-primary rounded-pill shadow-sm">
                            <i class="fa fa-upload"></i> Impor
                        </a>
                        <a href="#" class="btn btn-primary rounded-pill shadow-sm">
                            <i class="fa fa-download"></i> Ekspor
                        </a>
                        <a href="{{ route('tagihan.print_laporan', request()->all()) }}" target="_blank" class="btn btn-primary rounded-pill shadow-sm">
                            <i class="fa fa-print"></i> Cetak
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table-bordered table-hover rounded-3 table overflow-hidden text-center align-middle">
                        <thead class="table-primary text-center text-nowrap align-middle">
                            <tr>
                                <th>#</th>
                                <th>NISN</th>
                                <th>Nama Lengkap</th>
                                <th>Tagihan Unit</th>
                                <th>Tagihan Kelas</th>
                                <th>Nama Tagihan</th>
                                <th>Tipe Tagihan</th>
                                <th>Periode</th>
                                <th>Jml. Tagihan</th>
                                <th>Jml. Dibayar</th>
                                <th>Jml. Tunggakan</th>
                                <th>Status</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tagihans as $tagihan)
                                @php
                                    $siswa = $tagihan->tagihanSiswa->first()?->siswa;

                                    $total_tagihan = $tagihan->items->sum('nominal') * ($tagihan->periode ?? 1);

                                    $jumlah_dibayar = $siswa
                                        ? $tagihan->tagihanSiswa
                                                ->where('siswa_id', $siswa->id)
                                                ->where('status', 1)
                                                ->count() * $tagihan->items->sum('nominal')
                                        : 0;

                                    $tunggakan = max($total_tagihan - $jumlah_dibayar, 0);
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $siswa?->nisn ?? '-' }}</td>
                                    <td>{{ $siswa?->user->name ?? '-' }}</td>
                                    <td>{{ $tagihan->unit->nama_unit ?? '-' }}</td>
                                    <td>{{ $tagihan->kelas->nama_kelas ?? '-' }}</td>
                                    <td>
                                        @foreach ($tagihan->items as $item)
                                            {{ $item->kategori->nama_kategori ?? '-' }}<br>
                                        @endforeach
                                    </td>
                                    <td>{{ $tagihan->jenis_tagihan ?? '-' }}</td>
                                    <td>{{ $tagihan->periode ?? '-' }} Bulan</td>

                                    <td>Rp {{ number_format($total_tagihan , 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($jumlah_dibayar, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($tunggakan, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($tunggakan <= 0)
                                            <span class="badge bg-success rounded-pill">Lunas</span>
                                        @else
                                            <span class="badge bg-warning text-dark rounded-pill">Belum Lunas</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($siswa)
                                            <a href="{{ route('tagihan.show', [$tagihan->id, $siswa->id]) }}"
                                                class="btn btn-primary rounded-pill">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Menampilkan {{ $tagihans->firstItem() ?? 0 }} sampai {{ $tagihans->lastItem() ?? 0 }} dari {{ $tagihans->total() }} data
                    </div>
                    <div>
                        {{ $tagihans->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

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
