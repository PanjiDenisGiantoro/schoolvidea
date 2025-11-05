@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    @include('partials.page-title', [
        'title' => 'Dashboard',
        'subTitle' => 'Data Kategori Tagihan'
    ])

    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List Kategori Tagihan</h5>
                    <a href="{{ url('kategoritagihan/create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Data
                    </a>
                </div>

                <!-- Search and Filter Form -->
                <div class="col-lg-12 mb-3">
                    <form method="GET" action="{{ route('kategoritagihan.index') }}" class="row g-3">
                        @if(auth()->user()->unit_id === null)
                            <!-- Unit Filter for Admin -->
                            <div class="col-md-3">
                                <select name="unit_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Unit --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <!-- Search Input -->
                        <div class="col-md-{{ auth()->user()->unit_id === null ? '7' : '10' }}">
                            <input type="text" name="search" class="form-control" placeholder="Cari kategori (Nama, Kode, Biaya, Unit...)" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ri-search-line"></i> Cari
                            </button>
                        </div>
                    </form>
                </div>

                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Unit</th>
                        <th>Nama Kategori</th>
                        <th>Kode Kategori</th>
                        <th>Biaya Tagihan</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($kategoritagihans as $index => $item)
                        <tr>
                            <td>{{ $kategoritagihans->firstItem() + $index }}</td>
                            <td>{{ $item->unit->nama_unit ?? '' }}</td>
                            <td>{{ $item->nama_kategori }}</td>
                            <td>{{ $item->kode_kategori }}</td>
                            <td>Rp {{ number_format($item->biaya_tagihan, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $item->status == '1' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $item->status == '1' ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-3">
                                    <a href="{{ route('kategoritagihan.show', $item->id) }}" class="link-primary text-muted">
                                        <i class="ri-eye-line align-middle fs-20"></i>
                                        Show
                                    </a>
                                    <a href="{{ route('kategoritagihan.edit', $item->id) }}" class="link-warning text-muted">
                                        <i class="ri-edit-line align-middle fs-20"></i>
                                        Edit
                                    </a>
                                    <a href="{{ route('kategoritagihan.destroy', $item->id) }}" class="link-danger text-muted">
                                        <i class="ri-delete-bin-5-line align-middle fs-20"></i>
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data ditemukan</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="col-lg-12">
                    <div class="pagination-wrapper">
                        <div class="pagination-info">

                            Menampilkan {{ $kategoritagihans->firstItem() ?? 0 }} sampai {{ $kategoritagihans->lastItem() ?? 0 }} dari {{ $kategoritagihans->total() }} data
                        </div>
                        <div>
                            {{ $kategoritagihans->links('vendor.pagination.custom') }}
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
        $(document).ready(function () {
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

    @if(session('success'))
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
