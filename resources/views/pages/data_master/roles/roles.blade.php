@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    @include('partials.page-title', [
        'title' => 'Dashboard',
        'subTitle' => 'Data Role',
    ])

    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List Role</h5>
                    <a href="{{ url('roles/create') }}" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Tambah Data
                    </a>
                </div>

                <!-- Search Form -->
                <div class="col-lg-12 mb-3">
                    <form method="GET" action="{{ route('roles.index') }}" class="row g-3">
                        <!-- Search Input -->
                        <div class="col-md-10">
                            <input type="text" name="search" class="form-control p-3" style="font-size: 14px"
                                placeholder="Cari role (Nama Role...)" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ri-search-line"></i> Cari
                            </button>
                        </div>
                    </form>
                </div>

                <table class="table-bordered table-striped table">
                    <thead>
                        @if (!empty($headers) && is_array($headers))
                            @foreach ($headers as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        @else
                            <th>No data</th>
                        @endif
                    </thead>
                    <tbody>
                        @forelse($roles as $index => $item)
                            <tr>
                                <td>{{ $roles->firstItem() + $index }}</td>
                                <td>{{ $item->name ?? '-' }}</td>
                                <td>{{ $item->permissions_count ?? 0 }}</td>
                                <td>
                                    <div class="d-flex gap-3">
                                        <a href="{{ route('roles.permissions', $item->id) }}" class="link-info text-muted">
                                            <i class="ri-lock-line fs-20 align-middle"></i>
                                            Permission
                                        </a>
                                        <a href="{{ route('roles.show', $item->id) }}" class="link-primary text-muted">
                                            <i class="ri-eye-line fs-20 align-middle"></i>
                                            Show
                                        </a>
                                        <a href="{{ route('roles.edit', $item->id) }}" class="link-warning text-muted">
                                            <i class="ri-edit-line fs-20 align-middle"></i>
                                            Edit
                                        </a>
                                        <a href="{{ route('roles.destroy', $item->id) }}" class="link-danger text-muted">
                                            <i class="ri-delete-bin-5-line fs-20 align-middle"></i>
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="col-lg-12">
                    <div class="pagination-wrapper">
                        <div class="pagination-info">

                            Menampilkan {{ $roles->firstItem() ?? 0 }} sampai {{ $roles->lastItem() ?? 0 }} dari
                            {{ $roles->total() }} data
                        </div>
                        <div>
                            {{ $roles->links('vendor.pagination.custom') }}
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
