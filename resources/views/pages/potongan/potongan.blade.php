@extends('layouts.app')

@section('title', 'Daftar Potongan')

@section('content')
    @include('partials.page-title', [
        'title' => 'Daftar Potongan',
        'subTitle' => 'Potongan',
    ])

    <div class="card">
        <div class="card-body">
            <!-- Button to create a new Potongan -->
            <a href="{{ route('potongan.create') }}" class="btn btn-success mb-3">
                <i class="fa fa-plus"></i> Tambah Potongan
            </a>

            <!-- Potongan Table -->
            <div class="table-responsive">
                <table id="potongan_table" class="table-striped table-bordered table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Unit</th>
                        <th>Kelas</th>
                        <th>Kategori Tagihan</th>
                        <th>Tipe Potongan</th>
                        <th>Nilai</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($potongans as $potongan)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $potongan->unit->nama_unit }}</td>
                            <td>{{ $potongan->kelas->nama_kelas }}</td>
                            <td>{{ $potongan->kategoriTagihan->nama_kategori }}</td>
                            <td>{{ $potongan->tipe_potongan }}</td>

                            <td>
                                @php
                                    if ($potongan->tipe_potongan == 'Persen') {
                                        $nilai = $potongan->nilai . '%';
                                    } else {
                                        $nilai = 'Rp ' . number_format($potongan->nilai, 2, ',', '.');
                                    }
                                @endphp
                                {{ $nilai }}
                            </td>
                            <td>{{ $potongan->keterangan ?? '-' }}</td>
                            <td>
                                <!-- View Button -->
                                <a href="{{ route('potongan.show', $potongan->id) }}" class="btn btn-info btn-sm">
                                    <i class="fa fa-eye"></i> Lihat
                                </a>

                                <a href="{{ route('potongan.destroy', $potongan->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus potongan ini?')">
                                    <i class="fa fa-trash"></i> Hapus
                                </a>
                                {{--                            <!-- Delete Button -->--}}
                                {{--                            <form action="{{ route('potongan.destroy', $potongan->id) }}" method="POST" style="display:inline;">--}}
                                {{--                                @csrf--}}
                                {{--                                @method('DELETE')--}}
                                {{--                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus potongan ini?')">--}}
                                {{--                                    <i class="fa fa-trash"></i> Hapus--}}
                                {{--                                </button>--}}
                                {{--                            </form>--}}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#potongan_table').DataTable({
                paging: true,
                searching: true,
                responsive: true
            });
        });
    </script>
@endpush
