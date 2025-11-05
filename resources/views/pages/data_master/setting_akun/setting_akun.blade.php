@extends('layouts.app')
@section('title', 'Setting Akun')

@section('content')

    @include('partials.page-title', [
        'title' => 'Setting Akun',
        'subTitle' => 'Data Master / Setting Akun'
    ])

    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List Setting Akun</h5>
                    <a href="{{ route('setting_akun.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Data
                    </a>
                </div>

                <table id="datatable" class="table table-bordered table-striped">
                    <thead>
                    @if(!empty($headers) && is_array($headers))
                        <tr>
                            @foreach($headers as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                    @else
                        <tr><th>No data</th></tr>
                    @endif
                    </thead>
                    <tbody>
                    @forelse($settings as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->nama_setting ?? '-' }}</td>
                            <td>{{ $item->akun->nama_akun ?? '-' }}</td>
                            <td>{{ $item->debit ?? 0 }}</td>
                            <td>{{ $item->kredit ?? 0 }}</td>
                            <td>{{ $item->keterangan ?? '-' }}</td>
                            <td>{{ $item->unit->nama_unit ?? '-' }}</td>
                            <td>{{ $item->kategori ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $item->status === '1' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $item->status === '1' ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-3">
                                    <a href="{{url('setting_akun/show'.$item->id)}}" class="link-primary text-muted">
                                        <i class="ri-eye-line align-middle fs-20"></i> Show
                                    </a>
                                    <a href="{{ route('setting_akun.edit', $item->id) }}" class="link-warning text-muted">
                                        <i class="ri-edit-line align-middle fs-20"></i> Edit
                                    </a>
                                    <form action="{{ route('setting_akun.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link link-danger text-muted p-0 m-0">
                                            <i class="ri-delete-bin-5-line align-middle fs-20"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($headers) }}" class="text-center">Tidak ada data ditemukan</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @if($settings->isNotEmpty())
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
