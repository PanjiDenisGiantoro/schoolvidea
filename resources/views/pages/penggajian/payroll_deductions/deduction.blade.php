@extends('layouts.app')
@section('title', 'Daftar potongan')
@section('content')
    @include('partials.page-title', [
        'title' => 'Daftar potongan',
        'subTitle' => 'potongan'
    ])

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title ">List potongan</h5>
                <a href="{{ url('payroll_deductions/create') }}" class="btn btn-primary">
                    <span class="bi bi-plus-lg me-1">Tambah Data</span>
                </a>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped">
                    <thead>
                    @if(!empty($headers) && is_array($headers))
                    @foreach($headers as $header)
                    <th>{{ $header }}</th>
                    @endforeach
                    @else
                    <th>Tidak ada data</th>
                    @endif
                    </thead>
                    <tbody>
                        @forelse($payroll_deductions as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->type }}</td>
                            <td>{{ 'Rp ' . number_format($item->price, 2, ',', '.') }}</td>
                            
                            <td>
                                <span class="badge {{ $item->status === '1' ? 'bg-success' : 'bg-danger' }}">
                                {{ $item->status == 1 ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                
                                <div class="d-flex gap-3">
                                    <a href="{{ route('payroll_deductions.show', $item->id) }}" class="link-primary text-muted">
                                        <i class="ri-eye-line align-middle fs-20"></i>
                                        Show
                                    </a>
                                    <a href="{{ route('payroll_deductions.edit', $item->id) }}" class="link-warning text-muted">
                                        <i class="ri-edit-line align-middle fs-20"></i>
                                        Edit
                                    </a>
                                    <a href="{{ route('payroll_deductions.destroy', $item->id) }}" class="link-danger text-muted">
                                        <i class="ri-delete-bin-5-line align-middle fs-20"></i>
                                        Hapus
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
            </div>
        </div>
    </div>
</div>


@endsection
@push('scripts')
    @if($payroll_deductions->isNotEmpty())
        <script>
            $(document).ready(function () {
                $('#datatable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    language: {
                        url: '{{ asset("assets/datatables/id.json") }}'
                    }
                });

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
    @endif
@endpush