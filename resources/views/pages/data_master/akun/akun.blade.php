@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    @include('partials.page-title', [
        'title' => 'Dashboard',
        'subTitle' => 'Data Akun',
    ])

    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List Akun</h5>
                    <a href="{{ route('akun.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Data
                    </a>
                </div>

                <!-- Search and Filter Form -->
                <div class="col-lg-12 mb-3">
                    <form method="GET" action="{{ route('akun.index') }}" class="row g-3">
                        @if (auth()->user()->unit_id === null)
                            <!-- Unit Filter for Admin -->
                            <div class="col-md-3">
                                <select name="unit_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Unit --</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <!-- Search Input -->
                        <div class="col-md-{{ auth()->user()->unit_id === null ? '7' : '10' }}">
                            <input type="text" name="search" class="form-control p-3" style="font-size: 14px"
                                placeholder="Cari akun (Kode, Nama, Kategori, dll...)" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ri-search-line"></i> Cari
                            </button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table-bordered table-striped table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th>Kategori</th>
                                <th>Tipe</th>
                                <th>Parent</th>
                                <th>Unit</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($akuns as $index => $akun)
                                <tr>
                                    <td>{{ $akuns->firstItem() + $index }}</td>
                                    <td>{{ $akun->kode_akun }}</td>
                                    <td>{{ $akun->nama_akun }}</td>
                                    <td>{{ $akun->kategori_akun ?? '-' }}</td>
                                    <td>{{ $akun->tipe }}</td>
                                    <td>{{ $akun->parent?->nama_akun ?? '-' }}</td>
                                    <td>{{ $akun->unit?->nama_unit ?? '-' }}</td>
                                    <td>
                                        @if ($akun->status == 1)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-3">
                                            <a href="{{ route('akun.show', $akun->id) }}" class="link-primary text-muted">
                                                <i class="ri-eye-line fs-20 align-middle"></i> Show
                                            </a>
                                            <a href="{{ route('akun.edit', $akun->id) }}" class="link-warning text-muted">
                                                <i class="ri-edit-line fs-20 align-middle"></i> Edit
                                            </a>
                                            <a href="{{ route('akun.destroy', $akun->id) }}"
                                                class="link-danger text-muted">
                                                <i class="ri-delete-bin-5-line fs-20 align-middle"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="row">
                        <div class="pagination-wrapper d-flex justify-content-between align-items-center">
                            <div class="pagination-info">

                                Menampilkan {{ $akuns->firstItem() ?? 0 }} sampai {{ $akuns->lastItem() ?? 0 }} dari
                                {{ $akuns->total() }} data
                            </div>
                            <div>
                                {{ $akuns->links('vendor.pagination.custom') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // SweetAlert2 untuk hapus
            $('.link-danger').on('click', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif
@endpush
