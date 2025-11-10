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

                <!-- Search and Filter Form -->
                <div class="col-lg-12 mb-3">
                    <form method="GET" action="{{ route('setting_akun.index') }}" class="row g-3">
                        @if(auth()->user()->unit_id === null)
                            <!-- Unit Filter for Admin -->
                            <div class="col-md-3">
                                <select name="unit_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Unit --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <!-- Search Input -->
                        <div class="col-md-{{ auth()->user()->unit_id === null ? '7' : '10' }}">
                            <input type="text" name="search" class="form-control" placeholder="Cari setting akun (Nama, Akun, Kategori, Unit...)" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ri-search-line"></i> Cari
                            </button>
                        </div>
                    </form>
                </div>

                <table class="table table-bordered table-striped">
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
                    @forelse($settings as $index => $item)
                        <tr>
                            <td>{{ $settings->firstItem() + $index }}</td>
                            <td>{{ $item->nama_setting ?? '-' }}</td>
                            <td>{{ $item->akun->nama_akun ?? '-' }}</td>
                            <td>{{ $item->keterangan ?? '-' }}</td>
                            <td>{{ $item->unit->nama_unit ?? '-' }}</td>
                            <td>
                                @php
                                    if($item->kategori == 'tabungan'){
                                        echo 'Tabungan-setor';
                                    }elseif ($item->kategori == 'tabungan-tarik'){
                                        echo $item->kategori ?? '-';
                                    }
                                    elseif($item->kategori == 'tagihan-masuk'){
                                        echo 'transaksi masuk';
                                    }
                                    else if($item->kategori == 'tagihan-keluar'){
                                        echo 'transaksi keluar';
                                    }
                                @endphp
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
                                    <a href="{{ route('setting_akun.destroy', $item->id) }}" class="link-danger text-muted">
                                        <i class="ri-delete-bin-5-line align-middle fs-20"></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Tidak ada data ditemukan</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="col-lg-12">
                    <div class="pagination-wrapper">
                        <div class="pagination-info">

                            Menampilkan {{ $settings->firstItem() ?? 0 }} sampai {{ $settings->lastItem() ?? 0 }} dari {{ $settings->total() }} data
                        </div>
                        <div>
                            {{ $settings->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            // SweetAlert2 untuk hapus
            $('.link-danger').on('click', function(e) {
                e.preventDefault();
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
                        window.location.href = url;
                    }
                });
            });
        });
    </script>

    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif
@endpush
