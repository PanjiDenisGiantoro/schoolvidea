@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    @include('partials.page-title', [
        'title' => 'Dashboard',
        'subTitle' => 'Data Akun'
    ])

    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List Akun</h5>
                    <a href="{{ route('akun.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Data
                    </a>
                </div>

                <table id="datatable" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Kategori</th>
                        <th>Tipe</th>
                        <th>Parent</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        // Fungsi rekursif untuk menampilkan tree
                        function renderTree($akuns, $parent_id = null, $level = 0) {
                            foreach ($akuns->where('parent_id', $parent_id) as $akun) {
                                echo '<tr>';
                                echo '<td></td>'; // No bisa diatur dengan JS nanti
                                echo '<td>' . $akun->kode_akun . '</td>';
                                echo '<td>' . str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . ($level > 0 ? '└─ ' : '') . $akun->nama_akun . '</td>';
                                echo '<td>' . $akun->kategori_akun ?? '-' . '</td>';
                                echo '<td>' . $akun->tipe . '</td>';
                                echo '<td>' . ($akun->parent?->nama_akun ?? '-') . '</td>';
                                echo '<td>' . ($akun->unit?->nama_unit ?? '-') . '</td>';
                                echo '<td><span class="badge ' . ($akun->status == '1' ? 'bg-success' : 'bg-danger') . '">' . ($akun->status == '1' ? 'Aktif' : 'Tidak Aktif') . '</span></td>';
                                   echo '<td>
                                        <div class="d-flex gap-3">
                                            <a href="' . route('akun.show', $akun->id) . '" class="link-primary text-muted">
                                                <i class="ri-eye-line align-middle fs-20"></i> Show
                                            </a>
                                            <a href="' . route('akun.edit', $akun->id) . '" class="link-warning text-muted">
                                                <i class="ri-edit-line align-middle fs-20"></i> Edit
                                            </a>
                                            <a href="' . route('akun.destroy', $akun->id) . '" class="link-danger text-muted" onclick="return confirm(\'Yakin hapus data ini?\');">
                                                <i class="ri-delete-bin-5-line align-middle fs-20"></i> Hapus
                                            </a>
                                        </div>
                                      </td>';

                                // Panggil rekursif untuk child
                                renderTree($akuns, $akun->id, $level + 1);
                            }
                        }
                    @endphp

                    {!! renderTree($akuns) !!}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @if($akuns->isNotEmpty())
        <script>
            $(document).ready(function () {
                $('#datatable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    language: {
                        url: '{{ asset("assets/datatables/id.json") }}'
                    },
                    "columnDefs": [
                        { "orderable": false, "targets": [7] } // Action tidak bisa di-sort
                    ]
                });
            });
        </script>
    @endif
@endpush
