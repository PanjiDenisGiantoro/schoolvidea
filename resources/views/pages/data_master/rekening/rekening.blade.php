@extends('layouts.app')
@section('title', 'Data Rekening')

@section('content')

    @include('partials.page-title', [
        'title' => 'Master Rekening',
        'subTitle' => 'List Rekening'
    ])

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">List Rekening</h5>
                <a href="{{ route('rekening.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i> Tambah Rekening
                </a>
            </div>

            <table id="datatable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Tipe Rekening</th>
                    <th>Nama Rekening</th>
                    <th>No Rekening</th>
                    <th>Bank</th>
                    <th>KCP</th>
                    <th>User</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rekenings as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->type_rekening ?? '-' }}</td>
                        <td>{{ $item->nama_rekening ?? '-' }}</td>
                        <td>{{ $item->no_rekening ?? '-' }}</td>
                        <td>{{ $item->bank ?? '-' }}</td>
                        <td>{{ $item->KCP ?? '-' }}</td>
                        <td>{{ $item->nama_pemilik_rekening ?? '-' }}</td>
                        <td>{{ $item->unit->nama_unit ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $item->status === '1' ? 'bg-success' : 'bg-danger' }}">
                                {{ $item->status === '1' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-3">
                                <a href="{{ route('rekening.show', $item->id) }}" class="link-primary text-muted">
                                    <i class="ri-eye-line align-middle fs-20"></i> Show
                                </a>
                                <a href="{{ route('rekening.edit', $item->id) }}" class="link-warning text-muted">
                                    <i class="ri-edit-line align-middle fs-20"></i> Edit
                                </a>
                                <form action="{{ route('rekening.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link link-danger text-muted p-0 m-0">
                                        <i class="ri-delete-bin-5-line align-middle fs-20"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            $('#datatable').DataTable({
                responsive: true,
                language: {
                    url: '{{ asset("assets/datatables/id.json") }}'
                }
            });
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
