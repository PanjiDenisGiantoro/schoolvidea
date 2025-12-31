@extends('layouts.app')
@section('title', 'Data Akun User')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@endpush
@section('content')
    @include('partials.page-title', [         'title' => 'Data Akun User',         'subtitle' => 'Data Akun User',     ])
    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3"><h5
                        class="card-title mb-0">List Akun
                        User</h5> {{-- <a href="{{ url('#') }}" class="btn btn-primary">                         <i class="bi bi-plus-circle me-1"></i> Tambah Data                     </a> --}}
                </div>
                <div class="col-lg-12 mb-3"><label for="" class="form-label">Filter</label>
                    <form method="GET" action="{{ route('akun-user.index') }}"
                          class="row g-3">                         @if (auth()->user()->unit_id === null)
                            <!-- Unit Filter untuk Admin -->
                            <div class="col-md-3"><select name="unit_id" class="form-select"
                                                          onchange="this.form.submit()">
                                    <option value="">-- Semua Unit --</option> @foreach ($units as $unit)
                                        <option
                                            value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>                                             {{ $unit->nama_unit }}                                         </option>
                                    @endforeach                                 </select></div>
                        @endif                          <!-- Input Pencarian -->
                        <div class="col-md-{{ auth()->user()->unit_id === null ? '7' : '10' }}"><input type="text"
                                                                                                       name="search"
                                                                                                       class="form-control p-3"
                                                                                                       style="font-size: 14px"
                                                                                                       placeholder="Cari pengguna (Nama, Email, Unit...)"
                                                                                                       value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="ri-search-line"></i> Cari
                            </button>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                <table id="datatable" class="table-bordered table-striped table">
                    <thead class="table-primary">
                    @if (!empty($headers) && is_array($headers))
                        <tr class="text-center align-middle">
                            @foreach ($headers as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                    @else
                        <tr>
                            <th>No data</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @forelse($user as $index => $item)
                        <tr class="align-middle">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $item->units->nama_unit ?? '-' }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->yayasan_id ? 'Ya' : 'Tidak' }}</td>
                            <td>
                                <div class="d-flex gap-3"><a href="{{ url('akun-user/show', $item->id ?? '') }}"
                                        class="link-primary text-muted"> <i class="ri-eye-line fs-20 align-middle"></i>
                                        Show </a> <a href="{{ route('akun-user.edit', $item->id ?? '') }}"
                                        class="link-warning text-muted"> <i class="ri-edit-line fs-20 align-middle"></i>
                                        Edit
                                    </a> {{-- <a href="{{ url('akun-user/delete', $item->id ?? '') }}"                                             class="link-danger text-muted">                                             <i class="ri-delete-bin-5-line fs-20 align-middle"></i>                                             Hapus                                         </a> --}}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data ditemukan</td>
                        </tr>
                    @endforelse                     </tbody>
                </table>

                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @if ($user->isNotEmpty())
        <script>
            $(document).ready(function() {
                $('#datatable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    searching: false,
                    language: {
                        url: '{{ asset('assets/datatables/id.json') }}'
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
@endpush
