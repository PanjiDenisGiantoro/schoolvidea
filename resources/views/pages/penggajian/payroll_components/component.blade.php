@extends('layouts.app')
@section('title', 'Daftar Komponen')
@section('content')
    @include('partials.page-title', [
        'title' => 'Daftar Komponen',
        'subTitle' => 'Komponen',
    ])

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title">List Komponen</h5>
                    <a href="{{ url('payroll-components/create') }}" class="btn btn-primary">
                        <span class="bi bi-plus-lg me-1">Tambah Data</span>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            @if (!empty($headers) && is_array($headers))
                                @foreach ($headers as $header)
                                    <th class="text-center">{{ $header }}</th>
                                @endforeach
                            @else
                                <th>Tidak ada data</th>
                            @endif
                        </thead>
                        <tbody>
                            @forelse($payroll_components as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $payroll_components->firstItem() + $index }}</td>
                                    <td class="text-center">{{ $item->name }}</td>
                                    <td class="text-center">{{ 'Rp ' . number_format($item->price, 0, ',', '.') }}</td>

                                    <td class="text-center">
                                        <span class="badge {{ $item->status === '1' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $item->status == 1 ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td>

                                        <div class="d-flex justify-content-center gap-3">
                                            <a href="{{ route('payroll_components.show', $item->id) }}"
                                                class="link-primary text-muted">
                                                <i class="ri-eye-line fs-20 align-middle"></i>
                                                Show
                                            </a>
                                            <a href="{{ route('payroll_components.edit', $item->id) }}"
                                                class="link-warning text-muted">
                                                <i class="ri-edit-line fs-20 align-middle"></i>
                                                Edit
                                            </a>
                                            <a href="{{ route('payroll_components.destroy', $item->id) }}"
                                                class="link-danger text-muted">
                                                <i class="ri-delete-bin-5-line fs-20 align-middle"></i>
                                                Hapus
                                            </a>
                                        </div>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper mt-3">
                    <div class="pagination-info">
                        Menampilkan {{ $payroll_components->firstItem() ?? 0 }} sampai {{ $payroll_components->lastItem() ?? 0 }} dari {{ $payroll_components->total() }} data
                    </div>
                    <div>
                        {{ $payroll_components->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            // SweetAlert2 untuk hapus
            $('.link-danger').on('click', function(e) {
                e.preventDefault(); // cegah link langsung ke href
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
                        // redirect ke URL hapus
                        window.location.href = url;
                    }
                });
            });
        });
    </script>
@endpush
