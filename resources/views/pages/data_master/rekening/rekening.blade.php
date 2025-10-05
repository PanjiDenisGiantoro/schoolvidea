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
                        <td>{{ $item->user->name ?? '-' }}</td>
                        <td>{{ $item->unit->nama_unit ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $item->status === '1' ? 'bg-success' : 'bg-danger' }}">
                                {{ $item->status === '1' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('rekening.show', $item->id) }}" class="btn btn-sm btn-info">Show</a>
                                <a href="{{ route('rekening.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('rekening.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
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
    <script>
        $(document).ready(function () {
            $('#datatable').DataTable({
                responsive: true,
                language: {
                    url: '{{ asset("assets/datatables/id.json") }}'
                }
            });
        });
    </script>
@endpush
