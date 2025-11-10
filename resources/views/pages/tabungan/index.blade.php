@extends('layouts.app')
@section('title', 'Tabungan Siswa')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@endpush
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
                               class="form-control p-3" placeholder="NISN, Nama, Kelas..."
                               value="{{ request('search') }}">
                    </div>

                    {{-- Filter Tanggal Dari --}}
                    <div class="col-md-2">
                        <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                        <input type="date" name="dari_tanggal" id="dari_tanggal"
                               class="form-control p-3" value="{{ request('dari_tanggal') }}">
                    </div>

                    {{-- Filter Tanggal Sampai --}}
                    <div class="col-md-2">
                        <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                        <input type="date" name="sampai_tanggal" id="sampai_tanggal"
                               class="form-control p-3" value="{{ request('sampai_tanggal') }}">
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

                    <a href="{{ route('tabungan.print_laporan', request()->all()) }}" target="_blank"
                        class="btn btn-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                        <i class="bx bx-printer"></i> Cetak
                    </a>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="table-responsive">
            <div class="custom-card-header">
                <div class="col-md-4 ">
                    <span><i class="fa fa-list"></i> Daftar Tabungan Siswa</span>
                    <button id="btnProsesPembayaran" class="custom-btn-info">
                        <i class="ri-checkbox-multiple-line"></i>Aktifkan Semua Status
                    </button>
                </div>
                <div>
                    <label for="filter" class="form-label text-white">Filter Data Status</label>
                    <select class="form-select form-select-sm rounded-3 filter-status">
                        <option value="semua" data-status="all">Semua</option>
                        <option value="aktif" data-status="1">Aktif</option>
                        <option value="nonaktif" data-status="0">Non Aktif</option>
                    </select>
                </div>
            </div>
                <table class="table-bordered table-hover rounded-3 table overflow-hidden text-center align-middle" id="table-tabungan">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th><input class="custom-checkbox" type="checkbox" id="checkAll"></th>
                            <th>#</th>
                            <th>Nama Unit</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Kelas Sekarang</th>
                            <th>Tahun Ajaran</th>
                            <th>Status</th>
                            <th>Saldo Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-tabungan">
                        @forelse($transaksis as $siswa)
                                    @php
                                        $saldo = $siswa->user->saldo ?? null;
                                        $statusBadge = $saldo && $saldo->status == 1 ? 'primary' : 'secondary';
                                        $statusText = $saldo && $saldo->status == 1 ? 'Aktif' : 'Tidak Aktif';
                                    @endphp
                            <tr data-status="{{ $saldo ? $saldo->status : '0' }}">                                <td><input type="checkbox" name="checkbox" id="checkbox" class="custom-checkbox"
                                    data-id="{{ $saldo->id ?? '' }}"></td>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $siswa->unit->nama_unit ?? '-' }}</td>
                                <td>{{ $siswa->nisn ?? '-' }}</td>
                                <td>{{ $siswa->user->name ?? '-' }}</td>
                                <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $siswa->tahun_ajaran->tahun_ajaran ?? '-' }}</td>
                                <td>
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
                                            class="btn btn-sm btn-primary rounded-pill confirm-status">Aktif</a>
                                    @elseif($saldo)
                                        <a href="{{ route('tabungan.status', $saldo->id) }}"
                                            class="btn btn-sm btn-danger rounded-pill confirm-status">Non Aktif</a>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function changePerPage(perPage) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.delete('page'); // Reset to page 1
            window.location.href = url.toString();
        }
document.addEventListener('DOMContentLoaded', function () {
    const filterDropdown = document.querySelector('.filter-status'); // Select dropdown
    const rows = document.querySelectorAll('#tbody-tabungan tr');

    // Fungsi filter data
    function filterData(status) {
        rows.forEach(row => {
            if (status === 'all' || status === 'semua') {
                row.style.display = '';
            } else if (status === '1' || status === 'aktif') {
                row.style.display = row.dataset.status === '1' ? '' : 'none';
            } else if (status === '0' || status === 'nonaktif') {
                row.style.display = row.dataset.status === '0' ? '' : 'none';
            }
        });
    }

    // Filter dengan dropdown
    filterDropdown.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const status = selectedOption.dataset.status; // Ambil dari data-status
        filterData(status);
    });

    // SweetAlert Konfirmasi Status
    document.querySelectorAll('.confirm-status').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href');

            Swal.fire({
                title: `Anda yakin ingin mengubah status ini?`,
                text: "Tindakan ini akan mengubah status tabungan siswa.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, konfirmasi!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });

    // ✅ Check All Checkbox
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('input[name="checkbox"]');

    checkAll.addEventListener('change', function () {
        checkboxes.forEach(chk => {
            chk.checked = checkAll.checked;
        });
    });

    checkboxes.forEach(chk => {
        chk.addEventListener('change', function () {
            if (!this.checked) {
                checkAll.checked = false;
            } else if (Array.from(checkboxes).every(c => c.checked)) {
                checkAll.checked = true;
            }
        });
    });
// Pengaktifan Masal
const btnMass = document.getElementById('btnProsesPembayaran');

btnMass.addEventListener('click', function () {
    const selected = Array.from(document.querySelectorAll('input[name="checkbox"]:checked'))
        .map(chk => chk.dataset.id)
        .filter(id => id); // hanya ambil yang punya ID saldo

    if (selected.length === 0) {
        Swal.fire('Peringatan', 'Silakan pilih minimal satu siswa!', 'warning');
        return;
    }

    Swal.fire({
        title: 'Yakin ingin mengubah status?',
        text: "Status semua tabungan terpilih akan diubah (aktif/nonaktif).",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, ubah!',
        cancelButtonText: 'Batal',
    }).then(result => {
        if (result.isConfirmed) {
            fetch("{{ route('tabungan.massStatus') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids: selected })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error!', 'Terjadi kesalahan saat memproses.', 'error');
            });
        }
    });
});

});
    </script>
@endpush
