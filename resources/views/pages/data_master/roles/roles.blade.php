@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    @include('partials.page-title', [
        'title' => 'Dashboard',
        'subTitle' => 'Data Role'
    ])

    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List Role</h5>
                    <a href="{{ url('roles/create') }}" class="btn btn-primary">
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
                    @forelse($roles as $item)
                        <tr>

                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->name ?? '-' }}</td>

                            <td>
                                <div class="d-flex gap-3">
                                <a href="{{ route('roles.show', $item->id) }}" class="link-primary text-muted">
                                    <i class="ri-eye-line align-middle fs-20"></i>
                                    Show
                                </a>
                                <a href="{{ route('roles.edit', $item->id) }}" class="link-warning text-muted">
                                    <i class="ri-edit-line align-middle fs-20"></i>
                                    Edit
                                </a>
                                <a href="{{ route('roles.destroy', $item->id) }}" class="link-danger text-muted">
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
    @if($roles->isNotEmpty())

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
