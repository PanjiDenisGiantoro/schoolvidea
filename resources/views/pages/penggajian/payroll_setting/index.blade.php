@extends('layouts.app')
@section('title', 'Daftar Setting Penggajian')
@section('content')
<<<<<<< HEAD
@include('partials.page-title', [
    'title' => 'Setting Penggajian',
    'subTitle' => 'Daftar Pengaturan Gaji'
])

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">List Setting Penggajian</h5>
                <a href="{{ route('payroll_settings.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Data
                </a>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light text-center">
=======
    @include('partials.page-title', [
        'title' => 'Setting Penggajian',
        'subTitle' => 'Daftar Pengaturan Gaji'
    ])

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List Setting Penggajian</h5>
                    <a href="{{ route('payroll_settings.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Data
                    </a>
                </div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped align-middle">
                        <thead class="table-light text-center">
>>>>>>> 035ca4fe5cec0facf9625bb51946e19ed53787c7
                        <tr>
                            <th>No</th>
                            <th>Unit</th>
                            <th>Nama Guru & Staff</th>
                            <th>Periode Gaji</th>
                            <th>Gaji Pokok</th>
                            <th>Aksi</th>
                        </tr>
<<<<<<< HEAD
                    </thead>
                    <tbody>
                        @forelse($settings as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $item->unit->nama_unit ?? '-' }}</td>
                            <td>{{ $item->officer->user->name ?? '-' }}</td>
                            <td>{{ $item->billing_period ? "$item->billing_period Bulan" :'-'  }}</td>
                            <td>Rp {{ number_format($item->salary ?? 0, 0, ',', '.') }}</td>

                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('payroll_settings.show', $item->id) }}" class="link-primary text-muted">
                                        <i class="ri-eye-line align-middle fs-20"></i> Show
                                    </a>
                                    <a href="{{ route('payroll_settings.edit', $item->id) }}" class="link-warning text-muted">
                                        <i class="ri-edit-line align-middle fs-20"></i> Edit
                                    </a>
                                    <a href="{{ route('payroll_settings.destroy', $item->id) }}" class="link-danger text-muted">
                                        <i class="ri-delete-bin-5-line align-middle fs-20"></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Tidak ada data ditemukan</td>
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
=======
                        </thead>
                        <tbody>
                        @forelse($settings as $item)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $item->unit->nama_unit ?? '-' }}</td>
                                <td>{{ $item->officer->user->name ?? '-' }}</td>
                                <td>{{ $item->billing_period ? "$item->billing_period Bulan" :'-'  }}</td>
                                <td>Rp {{ number_format($item->salary ?? 0, 0, ',', '.') }}</td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('payroll_settings.show', $item->id) }}" class="link-primary text-muted">
                                            <i class="ri-eye-line align-middle fs-20"></i> Show
                                        </a>
                                        <a href="{{ route('payroll_settings.edit', $item->id) }}" class="link-warning text-muted">
                                            <i class="ri-edit-line align-middle fs-20"></i> Edit
                                        </a>
                                        <a href="{{ route('payroll_settings.destroy', $item->id) }}" class="link-danger text-muted">
                                            <i class="ri-delete-bin-5-line align-middle fs-20"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Tidak ada data ditemukan</td>
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
>>>>>>> 035ca4fe5cec0facf9625bb51946e19ed53787c7
@endpush
