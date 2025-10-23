@extends('layouts.app')

@section('title', 'Migrasi Data')

@section('content')
    <div class="container mt-4">
        <div class="row g-5">
            <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">List Activity Log</h5>
            </div>

            <table id="datatable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Activity</th>
                    <th>User</th>
                </tr>
                </thead>
                <tbody>
                @forelse($activity as $key => $item)
                    @php
                        // Decode JSON properties ke array atau object
                        $properties = is_string($item->properties)
                            ? json_decode($item->properties)
                            : $item->properties;
                    @endphp
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->description ?? '-' }}</td>
                        <td>{{ $item->causer->name ?? '-' }}</td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data ditemukan</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

        </div>
    </div>
@endsection

@push('scripts')
    @if($activity->isNotEmpty())
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
