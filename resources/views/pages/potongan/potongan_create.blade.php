@extends('layouts.app')

@section('title', isset($potongan) ? (isset($show) && $show ? 'Lihat Potongan' : 'Edit Potongan') : 'Tambah Potongan')

@section('content')
    @include('partials.page-title', [
        'title' => isset($potongan)
            ? (isset($show) && $show
                ? 'Lihat Potongan'
                : 'Edit Potongan')
            : 'Tambah Potongan',
        'subTitle' => 'Potongan',
    ])

    <div class="card">
        <div class="card-body">
            <form id="potonganForm"
                action="{{ isset($potongan) ? route('potongan.update', $potongan->id) : route('potongan.store') }}"
                method="POST">
                @csrf
                @if (isset($potongan))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <!-- Pilih Unit -->
                    <div class="col-md-6">
                        <label for="unit_id" class="form-label">Pilih Unit</label>
                        <select class="form-control" id="unit_id" name="unit_id" required
                            {{ isset($potongan) ? 'disabled' : '' }}>
                            <option value="">-- Pilih Unit --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}"
                                    {{ isset($potongan) && $potongan->unit_id == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Switch: All Classes or Specific Class -->
                    <div class="col-md-6">
                        <label for="kelas_switch" class="form-label">Pilih Kelas</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="kelas_switch" id="kelas_all" value="all"
                                {{ isset($potongan) && empty($potongan->kelas_id) ? 'checked' : (!isset($potongan) ? 'checked' : '') }}>
                            <label class="form-check-label" for="kelas_all">
                                Semua Kelas
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="kelas_switch" id="kelas_select"
                                value="select" {{ isset($potongan) && !empty($potongan->kelas_id) ? 'checked' : '' }}>
                            <label class="form-check-label" for="kelas_select">
                                Pilih Kelas
                            </label>
                        </div>
                    </div>

                    <!-- Daftar Kelas (Only visible if "Pilih Kelas" is selected) -->
                    <div class="col-md-12" id="kelas_table"
                        style="{{ isset($potongan) && !empty($potongan->kelas_id) ? '' : 'display: none;' }}">
                        <label for="kelas_id" class="form-label">Pilih Kelas</label>
                        <select class="form-control" id="kelas_id" name="kelas_id"
                            {{ isset($potongan) ? 'disabled' : '' }}>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}"
                                    {{ isset($potongan) && $potongan->kelas_id == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Siswa -->
                    <div class="col-md-12" id="siswa_table"
                        style="{{ isset($potongan) && !empty($potongan->kelas_id) ? '' : 'display: none;' }}">
                        <label for="siswa_id" class="form-label">Pilih Siswa</label>
                        <table id="siswa_datatable" class="table-striped table-bordered table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="checkAll"> Select All
                                    </th>
                                    <th>Nama Lengkap</th>
                                    <th>NISN</th>
                                </tr>
                            </thead>
                            <tbody id="siswa_list">
                                @if (isset($potongan) && !empty($potongan->kelas_id))
                                    @foreach ($potongan->kelas->siswas as $siswa)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="siswa_checkbox" name="siswa_id[]"
                                                    value="{{ $siswa->id }}"
                                                    {{ isset($potongan->potonganSiswa) && in_array($siswa->id, $potongan->potonganSiswa->pluck('siswa_id')->toArray()) ? 'checked' : '' }}
                                                    disabled>
                                            </td>
                                            <td>{{ $siswa->user->name }}</td>
                                            <td>{{ $siswa->nisn }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Pilih Kategori Tagihan -->
                    <div class="col-md-6">
                        <label for="kategori_tagihan_id" class="form-label">Pilih Kategori Tagihan</label>
                        <select class="form-control" id="kategori_tagihan_id" name="kategori_tagihan_id" required
                            {{ isset($potongan) ? 'disabled' : '' }}>
                            <option value="">-- Pilih Kategori Tagihan --</option>
                            @foreach ($kategoriTagihan as $kategori)
                                <option value="{{ $kategori->id }}"
                                    {{ isset($potongan) && $potongan->kategori_tagihan_id == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tipe Potongan -->
                    <div class="col-md-6">
                        <label for="tipe_potongan" class="form-label">Tipe Potongan</label>
                        <select class="form-control" id="tipe_potongan" name="tipe_potongan" required
                            {{ isset($potongan) ? 'disabled' : '' }}>
                            <option value="nominal"
                                {{ isset($potongan) && $potongan->tipe_potongan == 'nominal' ? 'selected' : '' }}>Nominal
                                (Rp)</option>
                            <option value="persentase"
                                {{ isset($potongan) && $potongan->tipe_potongan == 'persentase' ? 'selected' : '' }}>
                                Persentase (%)</option>
                        </select>
                    </div>

                    <!-- Nominal Potongan -->
                    <div class="col-md-6">
                        <label for="nilai" class="form-label">Nominal Potongan</label>
                        <input type="number" class="form-control" id="nilai" name="nilai"
                            value="{{ isset($potongan) ? $potongan->nilai : '' }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" maxlength="64">{{ isset($potongan) ? $potongan->keterangan : '' }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3 gap-2">
                    <button type="submit" class="btn btn-primary">Proses Tambah Potongan</button>
                    <button class="btn btn-secondary" onclick="history.go(-1)">Kembali</button>

                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script>
        // Fetch Kelas based on the selected Unit using AJAX
        document.getElementById('unit_id').addEventListener('change', function() {
            const unitId = this.value;
            const kelasSelect = document.getElementById('kelas_id');
            const siswaTable = document.getElementById('siswa_table');

            if (unitId) {
                // Make AJAX request to fetch classes based on unit_id
                fetch(`/unit/${unitId}/kelas`)
                    .then(response => response.json())
                    .then(data => {
                        // Populate Kelas dropdown
                        kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
                        data.forEach(kelas => {
                            const option = document.createElement('option');
                            option.value = kelas.id;
                            option.text = `${kelas.nama_kelas} `;
                            kelasSelect.appendChild(option);
                        });

                        // Show the Kelas dropdown
                        document.getElementById("kelas_table").style.display = "block";
                    })
                    .catch(error => console.error('Error fetching classes:', error));
            } else {
                // Clear Kelas dropdown if no unit is selected
                kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
                document.getElementById("kelas_table").style.display = "none";
            }
        });

        // Load students dynamically when class is selected
        // Load students dynamically when class is selected
        document.getElementById('kelas_id').addEventListener('change', function() {
            const kelasId = this.value;
            if (kelasId) {
                fetch(`/kelas/${kelasId}/siswa`)
                    .then(response => response.json())
                    .then(data => {
                        const siswaList = document.getElementById('siswa_list');
                        siswaList.innerHTML = ''; // Clear previous list

                        // Loop through the student data
                        data.forEach(siswa => {
                            // Ensure the siswa.id is used for the checkbox
                            const row = `<tr>
                                    <td><input type="checkbox" class="siswa_checkbox" name="siswa_id[]" value="${siswa.id}" id="siswa_${siswa.id}"></td>
                                    <td>${siswa.user.name}</td>
                                    <td>${siswa.nisn}</td>
                                  </tr>`;
                            siswaList.innerHTML += row;
                        });

                        // Initialize DataTables
                        $('#siswa_datatable').DataTable({
                            paging: true,
                            searching: true,
                            destroy: true
                        });

                        // Display the siswa table
                        document.getElementById("siswa_table").style.display = "block";
                    })
                    .catch(err => console.error('Error fetching students:', err));
            }
        });

        // Check All functionality
        document.getElementById('checkAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.siswa_checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    </script>
@endpush
