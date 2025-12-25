@extends('layouts.app')

@section('title', 'Daftar Potongan')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tabungan.css') }}">
@endpush
@section('content')
    @include('partials.page-title', [
        'title' => 'Daftar Potongan',
        'subTitle' => 'Potongan',
    ])

    <div class="card rounded-3 border-0 shadow-sm">
        <div class="card-body">
            <!-- Button to create a new Potongan -->
            <div class="col-lg-12 col-md-12 d-flex justify-content-between place-items-center">
                <h5 class="card-title">List Potongan</h5>
                <a href="{{ route('potongan.create') }}" class="btn btn-success mb-3 rounded-2">
                    <i class="fa fa-plus"></i> Tambah Potongan
                </a>
            </div>


            <!-- Potongan Table -->
            <div class="table-responsive">
                <table id="potongan_table" class="table-striped table-bordered table align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th width="5%">#</th>
                            <th>Unit</th>
                            <th>Kelas</th>
                            <th>Kategori Tagihan</th>
                            <th>Tipe Potongan</th>
                            <th width="15%">Nilai</th>
                            <th>Keterangan</th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($potongans as $potongan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $potongan->unit->nama_unit }}</td>
                                <td>{{ $potongan->kelas->nama_kelas }}</td>
                                <td>{{ $potongan->kategoriTagihan->nama_kategori }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ ucfirst($potongan->tipe_potongan) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>
                                        @if (strtolower($potongan->tipe_potongan) == 'persentase')
                                            {{ $potongan->nilai }}%
                                        @else
                                            Rp {{ number_format($potongan->nilai, 0, ',', '.') }}
                                        @endif
                                    </strong>
                                </td>
                                <td>{{ $potongan->keterangan ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-3">
                                        <a href="{{ route('potongan.show', $potongan->id) }}"
                                           class="link-primary text-muted">
                                            <i class="ri-eye-line fs-20 align-middle"></i>
                                            Show
                                        </a>
                                        <a href="#"  class="link-danger text-muted"
                                                onclick="deletePotongan({{ $potongan->id }})">
                                            <i class="ri-delete-bin-5-line fs-20 align-middle"></i>Hapus
                                        </a>

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">Belum ada data potongan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize DataTable hanya jika ada data
        $(document).ready(function() {
            @if ($potongans->count() > 0)
                $('#potongan_table').DataTable({
                    paging: true,
                    searching: true,
                    ordering: true,
                    responsive: true
                });
            @endif
        });

        function deletePotongan(id) {
            Swal.fire({
                title: 'Apakah Anda Yakin??',
                text: 'Yakin Ingin Menghapus Potongan Ini??',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then((result) => {
                    if (result.isConfirmed){
                        window.location.href = `{{ route('potongan.destroy', ':id') }}`.replace(':id', id);

                    }
            });
        }


    </script>
@endpush
