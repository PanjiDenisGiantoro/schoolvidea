@extends('layouts.app')

@section('title', 'Daftar Potongan')

@section('content')
    @include('partials.page-title', [
        'title' => 'Daftar Potongan',
        'subTitle' => 'Potongan',
    ])

    <div class="card rounded-3 border-0 shadow-sm">
        <div class="card-body">
            <!-- Button to create a new Potongan -->
            <a href="{{ route('potongan.create') }}" class="btn btn-success mb-3 rounded-2">
                <i class="fa fa-plus"></i> Tambah Potongan
            </a>

            <!-- Potongan Table -->
            <div class="table-responsive">
                <table id="potongan_table" class="table-striped table-bordered table align-middle">
                    <thead class="table-light">
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
                                        <button type="button" class="link-danger text-muted"
                                                onclick="deletePotongan({{ $potongan->id }})">
                                            <i class="ri-delete-bin-5-line fs-20 align-middle"></i>Hapus
                                        </button>

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
            if (confirm('Apakah Anda yakin ingin menghapus potongan ini?')) {
                window.location.href = `{{ route('potongan.destroy', ':id') }}`.replace(':id', id);
            }
        }
    </script>
@endpush
