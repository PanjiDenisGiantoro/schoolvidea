@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    @include('partials.page-title', [
        'title' => 'Dashboard',
        'subTitle' => 'Data Guru & Staff'
    ])

    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List Guru & Staff</h5>
                    <a href="{{ url('officer/create') }}" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Tambah Data
                    </a>
                </div>

                <!-- Search and Filter Form -->
                <div class="col-lg-12 mb-3">
                    <form method="GET" action="{{ route('officer.index') }}" class="row g-3">
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
                            <input type="text" name="search" class="form-control" placeholder="Cari guru/staff (Nama, NIP, Email, Role, Unit, dll...)" value="{{ request('search') }}">
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
                        <tr>
                            @foreach($headers as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                    @else
                        <th>No data</th>
                    @endif
                    </thead>
                    <tbody>
                    @forelse($officer as $index => $item)
                        <tr>
                            <td>{{ $officer->firstItem() + $index }}</td>
                            <td>{{ $item->officer->unit->nama_unit ?? '-' }}</td>
                            <td>{{ $item->name ?? '-' }}</td>
                            <td>{{ $item->roles->pluck('name')->join(', ') ?: '-' }}</td>
                            <td>{{ $item->officer->nip ?? '-' }}</td>
                            <td>{{ $item->email ?? '-' }}</td>
                            <td>{{ $item->officer->va_guru ?? '-' }}</td>
                            <td>
                                <div class="d-flex gap-3">
                                    @if($item->officer)
                                        <a href="{{ url('officer/show/'.$item->officer->id) }}" class="link-primary text-muted">
                                            <i class="ri-eye-line align-middle fs-20"></i> Show
                                        </a>
                                        <a href="{{ route('officer.edit', $item->officer->id) }}" class="link-warning text-muted">
                                            <i class="ri-edit-line align-middle fs-20"></i> Edit
                                        </a>
                                        <a href="{{ route('officer.destroy', $item->officer->id) }}" class="link-danger text-muted">
                                            <i class="ri-delete-bin-5-line align-middle fs-20"></i> Hapus
                                        </a>
                                    @else
                                        <a href="{{ route('officer.destroy', $item->officer->id ?? $item->id) }}" class="link-danger text-muted">
                                            <i class="ri-delete-bin-5-line align-middle fs-20"></i> Hapus
                                        </a>

                                    @endif
                                </div>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data ditemukan</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="col-lg-12">
                    <div class="pagination-wrapper">
                        <div class="pagination-info">

                            Menampilkan {{ $officer->firstItem() ?? 0 }} sampai {{ $officer->lastItem() ?? 0 }} dari {{ $officer->total() }} data
                        </div>
                        <div>
                            {{ $officer->links('vendor.pagination.custom') }}
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
