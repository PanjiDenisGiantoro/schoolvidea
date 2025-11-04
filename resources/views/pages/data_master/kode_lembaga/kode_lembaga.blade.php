@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    @include('partials.page-title', [
        'title' => 'Dashboard',
        'subTitle' => 'Data Lembaga Unit'
    ])

    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List Lembaga Unit</h5>
                    <a href="{{ url('lembagaunit/create') }}" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Tambah Data
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
                            <th>No data</th>
                        @endif
                        </thead>
                        <tbody>
                        @forelse($lembagaunit as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_yayasan ?? '-' }}</td>
                                <td>{{ $item->central_code ?? '-' }}</td>
                                <td>{{ $item->no_hp ?? '-' }}</td>
                                <td>{{ $item->email ?? '-' }}</td>
                                <td>{{ $item->alamat ?? '-' }}</td>
                                <td>{{ $item->website ?? '-' }}</td>
                                <td>{{ $item->nama_pimpinan ?? '-' }}</td>
                                <td>
                                <span class="badge {{ $item->status === 'Aktif' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $item->status }}
                                </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-3">
                                        <a href="{{ url('/lembagaunit/show', $item->id) }}" class="link-primary text-muted">
                                            <i class="ri-eye-line align-middle fs-20"></i> Show
                                        </a>
                                        <a href="{{ route('lembagaunit.edit', $item->id) }}" class="link-warning text-muted">
                                            <i class="ri-edit-line align-middle fs-20"></i> Edit
                                        </a>
                                        <a href="{{ route('lembagaunit.destroy', $item->id) }}" class="link-danger text-muted">
                                            <i class="ri-delete-bin-5-line align-middle fs-20"></i> Hapus
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
    @if($lembagaunit->isNotEmpty())
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
