@extends('layouts.app')
@section('title', 'Data Siswa')

@section('content')

    @include('partials.page-title', [
        'title' => 'Data Master',
        'subTitle' => 'Siswa'
    ])

    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List Siswa</h5>
                    <a href="{{ route('siswa.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Data
                    </a>
                </div>

                <table id="datatable" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        @foreach($headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($siswa as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->unit->nama_unit ?? '-' }}</td>
                            <td>{{ $item->nisn }}</td>
                            <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ $item->user->name ?? '-' }}</td>
                            <td>
                                @if($item->status == 1)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Non Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-3">
                                    <a href="{{ route('siswa.show', $item->id ?? '') }}" class="link-primary text-muted">
                                        <i class="ri-eye-line align-middle fs-20"></i>
                                        Show
                                    </a>
                                    <a href="{{ route('siswa.edit', $item->id ?? '') }}" class="link-warning text-muted">
                                        <i class="ri-edit-line align-middle fs-20"></i>
                                        Edit
                                    </a>
                                    <a href="{{ route('siswa.destroy', $item->id ?? '') }}" class="link-danger text-muted">
                                        <i class="ri-delete-bin-5-line align-middle fs-20"></i>
                                        Hapus
                                    </a>
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
    @if($siswa->isNotEmpty())
        <script>
            $(document).ready(function () {
                $('#datatable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    language: {
                        url: '{{ asset("assets/datatables/id.json") }}'
                    }
                });
            });
        </script>
    @endif
@endpush
