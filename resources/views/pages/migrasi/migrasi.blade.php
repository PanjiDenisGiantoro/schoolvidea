@extends('layouts.app')

@section('title', 'Migrasi Data')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4 text-center">Migrasi Data (Import Excel/CSV)</h2>

        {{-- Statistik Total Data --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Rekap Migrasi Data per Unit</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle" id="datatable">
                        <thead class="table-light">
                        <tr>
                            <th>Unit</th>
                            <th>Total Siswa</th>
                            <th>Total Kelas</th>
                            <th>Total Officer</th>
                            <th>Total Jurusan</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($totals as $row)
                            <tr>
                                <td>{{ $row['unit'] }}</td>
                                <td>{{ $row['siswa'] }}</td>
                                <td>{{ $row['kelas'] }}</td>
                                <td>{{ $row['officer'] }}</td>
                                <td>{{ $row['jurusan'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Alert --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Upload Forms --}}
        <div class="row">
            {{-- Import Siswa --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">Import Data Siswa</div>
                    <div class="card-body">
                        <form action="{{ route('import.siswa') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                                <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" data-choices data-choices-sorting-false required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    @foreach($tahun_ajaran as $t)
                                        <option value="{{ $t->id }}"
                                            {{ old('tahun_ajaran_id', $officer->tahun_ajaran_id ?? ($tahun_ajaran_selected->id ?? '')) == $t->id ? 'selected' : '' }}>
                                            {{ $t->tahun_ajaran ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dropdown Unit -->
                            <div class="mb-3">
                                <label for="unit_id" class="form-label">Nama Unit</label>
                                <select name="unit_id" id="unit_id" class="form-select" data-choices data-choices-sorting-false required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($unit_migrasi as $u)
                                        <option value="{{ $u->id }}"
                                            {{ old('unit_id', $kelas->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="file" name="file" class="form-control mb-2" required>
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('export.exportSiswa') }}" class="btn btn-outline-secondary">Download Template</a>
                                <button type="submit" class="btn btn-dark">Import</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Import Kelas --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">Import Data Kelas</div>
                    <div class="card-body">
                        <form action="{{ route('import.kelas') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                                <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" data-choices data-choices-sorting-false required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    @foreach($tahun_ajaran as $t)
                                        <option value="{{ $t->id }}"
                                            {{ old('tahun_ajaran_id', $officer->tahun_ajaran_id ?? ($tahun_ajaran_selected->id ?? '')) == $t->id ? 'selected' : '' }}>
                                            {{ $t->tahun_ajaran ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dropdown Unit -->
                            <div class="mb-3">
                                <label for="unit_id" class="form-label">Nama Unit</label>
                                <select name="unit_id" id="unit_id" class="form-select" data-choices data-choices-sorting-false required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($unit_migrasi as $u)
                                        <option value="{{ $u->id }}"
                                            {{ old('unit_id', $kelas->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="file" name="file" class="form-control mb-2" required>
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('import.template', 'kelas') }}" class="btn btn-outline-secondary">Download Template</a>
                                <button type="submit" class="btn btn-dark">Import</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Import Officer --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">Import Data Officer (Guru & Staff)</div>
                    <div class="card-body">
                        <form action="{{ route('import.officer') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                                <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" data-choices data-choices-sorting-false required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    @foreach($tahun_ajaran as $t)
                                        <option value="{{ $t->id }}"
                                            {{ old('tahun_ajaran_id', $officer->tahun_ajaran_id ?? ($tahun_ajaran_selected->id ?? '')) == $t->id ? 'selected' : '' }}>
                                            {{ $t->tahun_ajaran ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dropdown Unit -->
                            <div class="mb-3">
                                <label for="unit_id" class="form-label">Nama Unit</label>
                                <select name="unit_id" id="unit_id" class="form-select" data-choices data-choices-sorting-false required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($unit_migrasi as $u)
                                        <option value="{{ $u->id }}"
                                            {{ old('unit_id', $kelas->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="file" name="file" class="form-control mb-2" required>
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('export.officerexport') }}" class="btn btn-outline-secondary">Download Template</a>
                                <button type="submit" class="btn btn-dark">Import</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Import Jurusan --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">Import Data Jurusan</div>
                    <div class="card-body">
                        <form action="{{ route('import.jurusan') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <!-- Upload File -->
                            <input type="file" name="file" class="form-control mb-2" required>

                            <!-- Dropdown Tahun Ajaran -->
                            <div class="mb-3">
                                <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                                <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" data-choices data-choices-sorting-false required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    @foreach($tahun_ajaran as $t)
                                        <option value="{{ $t->id }}"
                                            {{ old('tahun_ajaran_id', $officer->tahun_ajaran_id ?? ($tahun_ajaran_selected->id ?? '')) == $t->id ? 'selected' : '' }}>
                                            {{ $t->tahun_ajaran ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dropdown Unit -->
                            <div class="mb-3">
                                <label for="unit_id" class="form-label">Nama Unit</label>
                                <select name="unit_id" id="unit_id" class="form-select" data-choices data-choices-sorting-false required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($unit_migrasi as $u)
                                        <option value="{{ $u->id }}"
                                            {{ old('unit_id', $kelas->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Button untuk Download Template dan Import -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('export.jurusantkelas') }}" class="btn btn-outline-secondary">Download Template</a>
                                <button type="submit" class="btn btn-dark">Import</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')

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
@endpush
