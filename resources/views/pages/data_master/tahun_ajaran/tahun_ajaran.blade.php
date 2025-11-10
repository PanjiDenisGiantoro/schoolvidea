@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    @include('partials.page-title', [
        'title' => 'Dashboard',
        'subTitle' => 'Data Tahun Ajaran'
    ])

    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List Tahun Ajaran</h5>
                    <a href="{{ url('tahun_ajaran/create') }}" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Tambah Data
                    </a>
                </div>

                <!-- Search Form -->
                <div class="col-lg-12 mb-3">
                    <form method="GET" action="{{ route('tahun_ajaran.index') }}" class="row g-3">
                        <!-- Search Input -->
                        <div class="col-md-10">
                            <input type="text" name="search" class="form-control p-3" placeholder="Cari tahun ajaran (Tahun, Semester, Tanggal...)" value="{{ request('search') }}">
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
                    @if(!empty($headers) && is_array($headers))
                        @foreach($headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    @else
                        <th>No data</th>
                    @endif
                    </thead>
                    <tbody>
                    @forelse($tahun_ajaran as $index => $item)
                        <tr>
                            <td>{{ $tahun_ajaran->firstItem() + $index }}</td>
                            <td>{{ $item->tahun_ajaran ?? '-' }}</td>
                            <td>{{ $item->tanggal_mulai ?? '-' }}</td>
                            <td>{{ $item->tanggal_selesai ?? '-' }}</td>
                            <td>{{ $item->semester ?? '-' }}</td>
                            <td>
                                @if($item->status == 'Aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-3">
                                    <a href="{{ url('tahun_ajaran/show/'.$item->id) }}" class="link-primary text-muted">
                                        <i class="ri-eye-line align-middle fs-20"></i>
                                        Show
                                    </a>
                                    <a href="{{ route('tahun_ajaran.edit', $item->id) }}" class="link-warning text-muted">
                                        <i class="ri-edit-line align-middle fs-20"></i>
                                        Edit
                                    </a>
                                    <a href="{{ route('tahun_ajaran.destroy', $item->id) }}" class="link-danger text-muted">
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

                            Menampilkan {{ $tahun_ajaran->firstItem() ?? 0 }} sampai {{ $tahun_ajaran->lastItem() ?? 0 }} dari {{ $tahun_ajaran->total() }} data
                        </div>
                        <div>
                            {{ $tahun_ajaran->links('vendor.pagination.custom') }}
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
