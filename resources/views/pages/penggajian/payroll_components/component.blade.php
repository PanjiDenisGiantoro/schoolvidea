@extends('layouts.app')
@section('title', 'Daftar Komponen')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tabungan.css') }}">
@endpush
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
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead class="table-primary">
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

            </div>
        </div>
    </div>


@endsection
@push('scripts')
    @if ($payroll_components->isNotEmpty())
        <script>
            $(document).ready(function() {
                $('#datatable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    language: {
                        url: '{{ asset('assets/datatables/id.json') }}'
                    }
                });

                // ✅ Konfirmasi hapus data
                $('.btn-delete').on('click', function(e) {
                    e.preventDefault();
                    const form = $(this).closest('form');
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data penggajian ini akan dihapus permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        </script>
    @endif
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
