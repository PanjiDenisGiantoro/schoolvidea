@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    @include('partials.page-title', [
        'title' => 'Dashboard',
        'subTitle' => 'Data Unit'
    ])

    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List Unit</h5>
                    <a href="{{ url('unit/create') }}" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Tambah Data
                    </a>
                </div>

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
                    @forelse($unit as $item)
                        <tr>

                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->yayasan->nama_yayasan ?? '-' }}</td>
                            <td>{{ $item->tipe_unit->nama_tipe_unit ?? '-' }}</td>
                            <td>{{ $item->nama_unit ?? '-' }}</td>
                            <td>{{ $item->code ?? '-' }}</td>
{{--                            <td>{{ $item->image ?? '-' }}</td>--}}
                            <td>{{ $item->no_hp ?? '-' }}</td>
                            <td>{{ $item->email ?? '-' }}</td>
                            <td>{{ $item->alamat ?? '-' }}</td>
                            <td>{{ $item->website ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $item->status === 'Aktif' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-3">
                                <a href="{{ route('unit.show', $item->id) }}" class="link-primary text-muted">
                                    <i class="ri-eye-line align-middle fs-20"></i>
                                    Show
                                </a>
                                <a href="{{ route('unit.edit', $item->id) }}" class="link-warning text-muted">
                                    <i class="ri-edit-line align-middle fs-20"></i>
                                    Edit
                                </a>
                                <a href="{{ route('unit.destroy', $item->id) }}" class="link-danger text-muted">
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

@endsection
@push('scripts')
    @if($unit->isNotEmpty())

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
