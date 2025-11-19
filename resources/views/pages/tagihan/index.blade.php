di @extends('layouts.app')

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
        <div class="card rounded-3 mb-3 border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bx bx-filter"></i> Filter Tagihan
                </h5>
                <form method="GET" action="{{ route('tagihan.index') }}">
                    <div class="row g-3">
                        {{-- Filter Search Nama/Tagihan --}}
                        <div class="col-md-2">
                            <label for="search" class="form-label fw-semibold">Cari Nama/Tagihan</label>
                            <input type="text" name="search" id="search"
                                   class="form-control" placeholder="Nama siswa, tagihan..."
                                   value="{{ request('search') }}">
                        </div>

                        {{-- Filter Unit --}}
                        @if(auth()->user()->yayasan_id && !auth()->user()->unit_id || !auth()->user()->yayasan_id && !auth()->user()->unit_id)
                        <div class="col-md-2">
                            <label for="unit_id" class="form-label fw-semibold">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-select">
                                <option value="">Semua Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Filter Kelas --}}
                        <div class="col-md-2">
                            <label for="kelas_id" class="form-label fw-semibold">Kelas</label>
                            <select name="kelas_id" id="kelas_id" class="form-select">
                                <option value="">Semua Kelas</option>
                            </select>
                        </div>

                        {{-- Filter Tagihan Status --}}
                        <div class="col-md-2">
                            <label for="tagihan_status" class="form-label fw-semibold">Status Pembayaran</label>
                            <select name="tagihan_status" id="tagihan_status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="lunas" {{ request('tagihan_status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                <option value="belum_lunas" {{ request('tagihan_status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            </select>
                        </div>

                        {{-- Filter Jenis Tagihan --}}
                        <div class="col-md-2">
                            <label for="jenis_tagihan" class="form-label fw-semibold">Jenis Tagihan</label>
                            <select name="jenis_tagihan" id="jenis_tagihan" class="form-select">
                                <option value="">Semua Jenis</option>
                                <option value="bulanan" {{ request('jenis_tagihan') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                <option value="bebas" {{ request('jenis_tagihan') == 'bebas' ? 'selected' : '' }}>Bebas</option>
                            </select>
                        </div>

                        {{-- Tombol Filter --}}
                        <div class="col-md-2 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-search"></i> Filter
                            </button>
                            <a href="{{ route('tagihan.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bx bx-refresh"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

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
                            @foreach ($tagihans as $tagihanSiswa)
                                @php
                                    $siswa = $tagihanSiswa->siswa;
                                    $tagihan = $tagihanSiswa->tagihan;

                                    $total_tagihan = $tagihan->items->sum('nominal');

                                    $jumlah_dibayar = $siswa->pembayaranTagihan
                                        ->where('status_approval', 'approved')
                                        ->sum('jumlah_bayar');

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

        // Load kelas berdasarkan unit yang dipilih
        document.getElementById('unit_id')?.addEventListener('change', function() {
            const unitId = this.value;
            const kelasSelect = document.getElementById('kelas_id');

            if (!unitId) {
                kelasSelect.innerHTML = '<option value="">Semua Kelas</option>';
                return;
            }

            fetch(`/api/kelas-by-unit/${unitId}`)
                .then(response => response.json())
                .then(data => {
                    let html = '<option value="">Semua Kelas</option>';
                    if (data.kelas && Array.isArray(data.kelas)) {
                        data.kelas.forEach(kelas => {
                            html += `<option value="${kelas.id}">${kelas.nama_kelas}</option>`;
                        });
                    }
                    kelasSelect.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading kelas:', error);
                    kelasSelect.innerHTML = '<option value="">Semua Kelas</option>';
                });
        });

        // Trigger change event ketika halaman pertama kali load jika ada unit_id
        document.addEventListener('DOMContentLoaded', function() {
            const unitSelect = document.getElementById('unit_id');
            const selectedUnitId = '{{ request("unit_id") }}';

            if (unitSelect && selectedUnitId) {
                unitSelect.value = selectedUnitId;
                unitSelect.dispatchEvent(new Event('change'));

                // Set kelas yang dipilih setelah kelas di-load
                setTimeout(() => {
                    const kelasSelect = document.getElementById('kelas_id');
                    if (kelasSelect) {
                        kelasSelect.value = '{{ request("kelas_id") }}';
                    }
                }, 300);
            }
        });
    </script>
@endpush
