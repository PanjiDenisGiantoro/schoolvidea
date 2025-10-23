@extends('layouts.app')
@section('title', 'Pembayaran Penggajian')

@push('styles')
<<<<<<< HEAD
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
<style>
    .table thead th {
        font-weight: 600;
        font-size: 0.9rem;
    }
    .table tbody td {
        font-size: 0.9rem;
    }
    .card-header {
        font-size: 1rem;
    }
    .btn {
        transition: all 0.25s ease-in-out;
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .animate-btn {
        transition: all 0.3s ease-in-out;
    }
    .animate-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.2);
    }
    .form-control:focus, .form-select:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
    }
</style>
@endpush

@section('content')
@include('partials.page-title', [
    'title' => 'Tambah Transaksi Pembayaran',
    'subTitle' => 'Pembayaran / Keuangan'
])

<div class="row">
    {{-- Form Utama --}}
    <div class="col-md-8">
        <div class="card p-4 shadow-sm rounded-4 border-0">
            <div class="row g-3 align-items-center">
                {{-- Dropdown Guru & Staff --}}
                <div class="col-md-6 mb-4">
                    <label for="filter_kelas" class="form-label fw-semibold">Pilih Guru & Staff</label>
                    <div class="custom-dropdown" id="dropdownKelas">
                        <input type="text" class="dropdown-input" placeholder="Cari atau Pilih Guru&Staff..." readonly>
                        <div class="dropdown-list">
                            <div class="dropdown-search">
                                <input type="text" placeholder="Cari..." class="dropdown-search-input">
                            </div>
                            <ul class="dropdown-options">
                                <li data-value="1">Guru 1</li>
                                <li data-value="2">Guru 2</li>
                                <li data-value="3">Guru 3</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Jenis Pembayaran --}}
                <div class="col-md-6 mb-4">
                    <label for="jenis_pembayaran" class="form-label fw-semibold">Jenis Pembayaran</label>
                    <select id="jenis_pembayaran" class="form-select shadow-sm">
                        <option value="">-- Pilih Jenis Pembayaran --</option>
                        <option value="perbulan">Per Bulan</option>
                        <option value="bebas">Bebas</option>
                    </select>
                </div>

                {{-- Pilih Pembayaran --}}
                <div class="col-md-6 mb-4">
                    <label for="pembayaran" class="form-label fw-semibold">Pilih Pembayaran</label>
                    <select id="pembayaran" class="form-select shadow-sm">
                        <option value="">-- Pilih Pembayaran --</option>
                        <option value="">Gaji</option>
                        <option value="">Lembur</option>
                    </select>
                </div>

                {{-- Periode Pembayaran --}}
                <div class="col-md-6 mb-4">
                    <label for="periode_pembayaran" class="form-label fw-semibold">Periode Pembayaran</label>
                    <select id="periode_pembayaran" class="form-select shadow-sm">
                        <option value="">-- Pilih Periode Pembayaran --</option>
                        <option value="">Oktober</option>
                        <option value="">November</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar Info Guru --}}
    <div class="col-md-4">
        <div class="card p-4 shadow-sm rounded-4 border-0">
            <div class="text-center mb-3">
                <img src="" alt="Foto Guru & Staff" class="img-fluid rounded-circle shadow-sm border" width="120">
            </div>
            <ul class="list-unstyled small">
                <li><strong>Nama Lengkap:</strong> <span>-</span></li>
                <li><strong>NIP:</strong> <span>-</span></li>
                <li><strong>Unit Pendidikan:</strong> <span>-</span></li>
                <li><strong>Jabatan:</strong> <span>-</span></li>
                <li><strong>Nomor Telepon:</strong> <span>-</span></li>
            </ul>
        </div>
    </div>
</div>

{{-- Daftar Tagihan --}}
<div class="crd shadow-sm rounded-4 border-0 mt-3">
    <div class="custom-toggle-header">
        <button id="btnBelumLunas" class="custom-btn-outline-primary custom-active-btn">
            <i class="ri-money-dollar-circle-line"></i> Belum Lunas
        </button>
        <button id="btnSudahLunas" class="custom-btn-outline-primary custom-active-btn">
            <i class="ri-money-dollar-circle-line"></i> Sudah Lunas
        </button>
    </div>

    <div class="custom-card-header">
        <span><i class="fa fa-list"></i> Daftar Tagihan Per Bulan</span>
        <div class="row" id="button-info">
            <div class="col-md-5">
                <button id="btnSinkron" class="custom-btn-info">Sinkron Pembayaran</button>
            </div>
            <div class="col-md-5">
                <button id="btnProses" class="custom-btn-info">Proses Pembayaran</button>
            </div>
        </div>
    </div>

    {{-- Tabel Belum Lunas --}}
    <div id="tabelBelumLunas" class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light text-center">
=======
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        .table thead th {
            font-weight: 600;
            font-size: 0.9rem;
        }
        .table tbody td {
            font-size: 0.9rem;
        }
        .card-header {
            font-size: 1rem;
        }
        .btn {
            transition: all 0.25s ease-in-out;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .animate-btn {
            transition: all 0.3s ease-in-out;
        }
        .animate-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.2);
        }
        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
    </style>
@endpush

@section('content')
    @include('partials.page-title', [
        'title' => 'Tambah Transaksi Pembayaran',
        'subTitle' => 'Pembayaran / Keuangan'
    ])

    <div class="row">
        {{-- Form Utama --}}
        <div class="col-md-8">
            <div class="card p-4 shadow-sm rounded-4 border-0">
                <div class="row g-3 align-items-center">
                    {{-- Dropdown Guru & Staff --}}
                    <div class="col-md-6 mb-4">
                        <label for="filter_kelas" class="form-label fw-semibold">Pilih Guru & Staff</label>
                        <div class="custom-dropdown" id="dropdownKelas">
                            <input type="text" class="dropdown-input" placeholder="Cari atau Pilih Guru&Staff..." readonly>
                            <div class="dropdown-list">
                                <div class="dropdown-search">
                                    <input type="text" placeholder="Cari..." class="dropdown-search-input">
                                </div>
                                <ul class="dropdown-options">
                                    <li data-value="1">Guru 1</li>
                                    <li data-value="2">Guru 2</li>
                                    <li data-value="3">Guru 3</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Jenis Pembayaran --}}
                    <div class="col-md-6 mb-4">
                        <label for="jenis_pembayaran" class="form-label fw-semibold">Jenis Pembayaran</label>
                        <select id="jenis_pembayaran" class="form-select shadow-sm">
                            <option value="">-- Pilih Jenis Pembayaran --</option>
                            <option value="perbulan">Per Bulan</option>
                            <option value="bebas">Bebas</option>
                        </select>
                    </div>

                    {{-- Pilih Pembayaran --}}
                    <div class="col-md-6 mb-4">
                        <label for="pembayaran" class="form-label fw-semibold">Pilih Pembayaran</label>
                        <select id="pembayaran" class="form-select shadow-sm">
                            <option value="">-- Pilih Pembayaran --</option>
                            <option value="">Gaji</option>
                            <option value="">Lembur</option>
                        </select>
                    </div>

                    {{-- Periode Pembayaran --}}
                    <div class="col-md-6 mb-4">
                        <label for="periode_pembayaran" class="form-label fw-semibold">Periode Pembayaran</label>
                        <select id="periode_pembayaran" class="form-select shadow-sm">
                            <option value="">-- Pilih Periode Pembayaran --</option>
                            <option value="">Oktober</option>
                            <option value="">November</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Info Guru --}}
        <div class="col-md-4">
            <div class="card p-4 shadow-sm rounded-4 border-0">
                <div class="text-center mb-3">
                    <img src="" alt="Foto Guru & Staff" class="img-fluid rounded-circle shadow-sm border" width="120">
                </div>
                <ul class="list-unstyled small">
                    <li><strong>Nama Lengkap:</strong> <span>-</span></li>
                    <li><strong>NIP:</strong> <span>-</span></li>
                    <li><strong>Unit Pendidikan:</strong> <span>-</span></li>
                    <li><strong>Jabatan:</strong> <span>-</span></li>
                    <li><strong>Nomor Telepon:</strong> <span>-</span></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Daftar Tagihan --}}
    <div class="crd shadow-sm rounded-4 border-0 mt-3">
        <div class="custom-toggle-header">
            <button id="btnBelumLunas" class="custom-btn-outline-primary custom-active-btn">
                <i class="ri-money-dollar-circle-line"></i> Belum Lunas
            </button>
            <button id="btnSudahLunas" class="custom-btn-outline-primary custom-active-btn">
                <i class="ri-money-dollar-circle-line"></i> Sudah Lunas
            </button>
        </div>

        <div class="custom-card-header">
            <span><i class="fa fa-list"></i> Daftar Tagihan Per Bulan</span>
            <div class="row" id="button-info">
                <div class="col-md-5">
                    <button id="btnSinkron" class="custom-btn-info">Sinkron Pembayaran</button>
                </div>
                <div class="col-md-5">
                    <button id="btnProses" class="custom-btn-info">Proses Pembayaran</button>
                </div>
            </div>
        </div>

        {{-- Tabel Belum Lunas --}}
        <div id="tabelBelumLunas" class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light text-center">
>>>>>>> 035ca4fe5cec0facf9625bb51946e19ed53787c7
                    <tr>
                        <th><input class="custom-checkbox " type="checkbox" id="checkAllBelumLunas"></th>
                        <th>#</th>
                        <th>Nama Guru&Staff</th>
                        <th>Periode Pembayaran</th>
                        <th>Tipe Pembayaran</th>
<<<<<<< HEAD
                        <th style="width: 180px; text-align: center;"> 
=======
                        <th style="width: 180px; text-align: center;">
>>>>>>> 035ca4fe5cec0facf9625bb51946e19ed53787c7
                            Presensi
                            <div class="custom-presensi-header gap-4">
                                <span>JM</span>
                                <span>Hadir</span>
                                <span>T.Hadir</span>
                            </div>
                        </th>
                        <th>Penerimaan</th>
                        <th>Total Potongan</th>
                        <th>Penerimaan Bersih</th>
                        <th>Note/Lainnya</th>
                        <th>Aksi</th>
                    </tr>
<<<<<<< HEAD
                </thead>
                <tbody>
=======
                    </thead>
                    <tbody>
>>>>>>> 035ca4fe5cec0facf9625bb51946e19ed53787c7
                    <tr>
                        <td><input type="checkbox" class="row-checkbox-belum custom-checkbox"></td>
                        <td>1</td>
                        <td>Ibal Kemed</td>
                        <td>Oktober 2025</td>
                        <td>Gaji</td>
                        <td>
                            <div class="custom-presensi-wrapper">
                                <input type="number" class="custom-input-presensi izin" style="width: 50px;">
                                <input type="number" class="custom-input-presensi hadir" style="width: 50px;">
                                <input type="number" class="custom-input-presensi alpha" style="width: 50px;">
                            </div>
                        </td>
                        <td>Rp.10.000.000</td>
                        <td>Rp.2.000.000</td>
                        <td>Rp.8.000.000</td>
                        <td class="justify-content-center">
                            <button type="button" class="btn custom-btn-purple" data-bs-toggle="modal" data-bs-target="#catatanModal"> + </button>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-warning">Detail</button>
                                <button class="btn btn-success">Bayar</button>
                            </div>
                        </td>
                    </tr>
<<<<<<< HEAD
                </tbody>
            </table>
        </div>
    </div>
        {{-- Tabel Sudah Lunas --}} 
    <div id="tabelSudahLunas" class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light text-center">
=======
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Tabel Sudah Lunas --}}
        <div id="tabelSudahLunas" class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light text-center">
>>>>>>> 035ca4fe5cec0facf9625bb51946e19ed53787c7
                    <tr>
                        <th><input class="custom-checkbox " type="checkbox" id="checkAllSudahLunas"></th>
                        <th>#</th>
                        <th>Nama Guru&Staff</th>
                        <th>Periode Pembayaran</th>
                        <th>Tipe Pembayaran</th>
<<<<<<< HEAD
                        <th style="width: 180px; text-align: center;"> 
=======
                        <th style="width: 180px; text-align: center;">
>>>>>>> 035ca4fe5cec0facf9625bb51946e19ed53787c7
                            Presensi
                            <div class="custom-presensi-header gap-4">
                                <span>JM</span>
                                <span>Hadir</span>
                                <span>T.Hadir</span>
                            </div>
                        </th>
                        <th>Penerimaan</th>
                        <th>Total Potongan</th>
                        <th>Penerimaan Bersih</th>
                        <th>Note/Lainnya</th>
                        <th>Aksi</th>
                    </tr>
<<<<<<< HEAD
                </thead>
                <tbody>
=======
                    </thead>
                    <tbody>
>>>>>>> 035ca4fe5cec0facf9625bb51946e19ed53787c7
                    <tr>
                        <td><input type="checkbox" class="row-checkbox-sudah custom-checkbox"></td>
                        <td>1</td>
                        <td>Ibal Kemed</td>
                        <td>Oktober 2025</td>
                        <td>Gaji</td>
                        <td>
                            <div class="custom-presensi-wrapper">
                                <input type="number" class="custom-input-presensi izin" style="width: 50px;">
                                <input type="number" class="custom-input-presensi hadir" style="width: 50px;">
                                <input type="number" class="custom-input-presensi alpha" style="width: 50px;">
                            </div>
                        </td>
                        <td>Rp.10.000.000</td>
                        <td>Rp.2.000.000</td>
                        <td>Rp.8.000.000</td>
                        <td class="justify-content-center">
                            <button type="button" class="btn custom-btn-purple" data-bs-toggle="modal" data-bs-target="#catatanModal"> + </button>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-warning">Detail</button>
                                <button class="btn btn-success">Edit</button>
                            </div>
                        </td>
                    </tr>
<<<<<<< HEAD
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Catatan --}}
<div class="modal fade" id="catatanModal" tabindex="-1" aria-labelledby="catatanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header custom-modal-header">
                <h5 class="modal-title fw-semibold" id="catatanModalLabel">
                    <i class="ri-sticky-note-line"></i> Tambah Catatan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCatatan">
                    <div class="mb-3">
                        
                        <x-input-field type="number" placeholder="Nominal" name="salary" label="Penerimaan Lainnya (Bonus/Sejenisnya)" />
                    </div>
                    <div class="mb-3">
                        <label for="isiCatatan" class="form-label fw-semibold">Catatan</label>
                        <textarea class="form-control" id="isiCatatan" rows="4" placeholder="Tulis keterangan tambahan di sini..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn custom-btn-purple">Simpan Catatan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnBelumLunas = document.getElementById('btnBelumLunas');
    const btnSudahLunas = document.getElementById('btnSudahLunas');
    const tabelBelumLunas = document.getElementById('tabelBelumLunas');
    const tabelSudahLunas = document.getElementById('tabelSudahLunas');
    const cardHeader = document.querySelector('#button-info');
    const btnProsesPembayaran = document.getElementById('btnProsesPembayaran');

    // --- Default tampilan: Belum Lunas aktif ---
    btnBelumLunas.classList.add('custom-active-btn');
    btnSudahLunas.classList.remove('custom-active-btn');
    tabelBelumLunas.style.display = 'block';
    tabelSudahLunas.style.display = 'none';
    if (cardHeader) cardHeader.style.display = 'flex';
    if (btnProsesPembayaran) btnProsesPembayaran.style.display = 'inline-flex';

    // --- Klik Belum Lunas ---
    btnBelumLunas.addEventListener('click', function() {
        btnBelumLunas.classList.add('custom-active-btn');
        btnSudahLunas.classList.remove('custom-active-btn');
        tabelBelumLunas.style.display = 'block';
        tabelSudahLunas.style.display = 'none';

        if (cardHeader) cardHeader.style.display = 'flex';
        if (btnProsesPembayaran) btnProsesPembayaran.style.display = 'inline-flex';
    });

    // --- Klik Sudah Lunas ---
    btnSudahLunas.addEventListener('click', function() {
        btnSudahLunas.classList.add('custom-active-btn');
        btnBelumLunas.classList.remove('custom-active-btn');
        tabelSudahLunas.style.display = 'block';
        tabelBelumLunas.style.display = 'none';

        if (cardHeader) cardHeader.style.display = 'none';
        if (btnProsesPembayaran) btnProsesPembayaran.style.display = 'none';
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const dropdown = document.getElementById('dropdownKelas');
    const input = dropdown.querySelector('.dropdown-input');
    const list = dropdown.querySelector('.dropdown-list');
    const options = dropdown.querySelectorAll('.dropdown-options li');
    const searchInput = dropdown.querySelector('.dropdown-search-input');

    // Toggle dropdown
    input.addEventListener('click', (e) => {
        e.stopPropagation();
        list.classList.toggle('active');
        searchInput.focus();
    });

    // Klik di luar -> tutup dropdown
    document.addEventListener('click', () => list.classList.remove('active'));

    // Klik item
    options.forEach(option => {
        option.addEventListener('click', () => {
            input.value = option.textContent;
            input.setAttribute('data-value', option.dataset.value);
            list.classList.remove('active');
            input.dispatchEvent(new Event('change'));
        });
    });

    // Search filter
    searchInput.addEventListener('keyup', function() {
        const term = this.value.toLowerCase();
        options.forEach(option => {
            option.style.display = option.textContent.toLowerCase().includes(term) ? 'block' : 'none';
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function setupSelectAll(checkAllId, rowCheckboxClass) {
        const checkAll = document.getElementById(checkAllId);
        const rowCheckboxes = document.querySelectorAll(`.${rowCheckboxClass}`);

        if (!checkAll) return;

        checkAll.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => cb.checked = checkAll.checked);
        });

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = Array.from(rowCheckboxes).every(x => x.checked);
                checkAll.checked = allChecked;
            });
        });
    }

    // Inisialisasi untuk masing-masing tabel
    setupSelectAll('checkAllBelumLunas', 'row-checkbox-belum');
    setupSelectAll('checkAllSudahLunas', 'row-checkbox-sudah');
});
</script>


{{-- Skrip cadangan yang dikomentari --}}
<!--
=======
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Catatan --}}
    <div class="modal fade" id="catatanModal" tabindex="-1" aria-labelledby="catatanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header custom-modal-header">
                    <h5 class="modal-title fw-semibold" id="catatanModalLabel">
                        <i class="ri-sticky-note-line"></i> Tambah Catatan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCatatan">
                        <div class="mb-3">

                            <x-input-field type="number" placeholder="Nominal" name="salary" label="Penerimaan Lainnya (Bonus/Sejenisnya)" />
                        </div>
                        <div class="mb-3">
                            <label for="isiCatatan" class="form-label fw-semibold">Catatan</label>
                            <textarea class="form-control" id="isiCatatan" rows="4" placeholder="Tulis keterangan tambahan di sini..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn custom-btn-purple">Simpan Catatan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnBelumLunas = document.getElementById('btnBelumLunas');
            const btnSudahLunas = document.getElementById('btnSudahLunas');
            const tabelBelumLunas = document.getElementById('tabelBelumLunas');
            const tabelSudahLunas = document.getElementById('tabelSudahLunas');
            const cardHeader = document.querySelector('#button-info');
            const btnProsesPembayaran = document.getElementById('btnProsesPembayaran');

            // --- Default tampilan: Belum Lunas aktif ---
            btnBelumLunas.classList.add('custom-active-btn');
            btnSudahLunas.classList.remove('custom-active-btn');
            tabelBelumLunas.style.display = 'block';
            tabelSudahLunas.style.display = 'none';
            if (cardHeader) cardHeader.style.display = 'flex';
            if (btnProsesPembayaran) btnProsesPembayaran.style.display = 'inline-flex';

            // --- Klik Belum Lunas ---
            btnBelumLunas.addEventListener('click', function() {
                btnBelumLunas.classList.add('custom-active-btn');
                btnSudahLunas.classList.remove('custom-active-btn');
                tabelBelumLunas.style.display = 'block';
                tabelSudahLunas.style.display = 'none';

                if (cardHeader) cardHeader.style.display = 'flex';
                if (btnProsesPembayaran) btnProsesPembayaran.style.display = 'inline-flex';
            });

            // --- Klik Sudah Lunas ---
            btnSudahLunas.addEventListener('click', function() {
                btnSudahLunas.classList.add('custom-active-btn');
                btnBelumLunas.classList.remove('custom-active-btn');
                tabelSudahLunas.style.display = 'block';
                tabelBelumLunas.style.display = 'none';

                if (cardHeader) cardHeader.style.display = 'none';
                if (btnProsesPembayaran) btnProsesPembayaran.style.display = 'none';
            });
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropdown = document.getElementById('dropdownKelas');
            const input = dropdown.querySelector('.dropdown-input');
            const list = dropdown.querySelector('.dropdown-list');
            const options = dropdown.querySelectorAll('.dropdown-options li');
            const searchInput = dropdown.querySelector('.dropdown-search-input');

            // Toggle dropdown
            input.addEventListener('click', (e) => {
                e.stopPropagation();
                list.classList.toggle('active');
                searchInput.focus();
            });

            // Klik di luar -> tutup dropdown
            document.addEventListener('click', () => list.classList.remove('active'));

            // Klik item
            options.forEach(option => {
                option.addEventListener('click', () => {
                    input.value = option.textContent;
                    input.setAttribute('data-value', option.dataset.value);
                    list.classList.remove('active');
                    input.dispatchEvent(new Event('change'));
                });
            });

            // Search filter
            searchInput.addEventListener('keyup', function() {
                const term = this.value.toLowerCase();
                options.forEach(option => {
                    option.style.display = option.textContent.toLowerCase().includes(term) ? 'block' : 'none';
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function setupSelectAll(checkAllId, rowCheckboxClass) {
                const checkAll = document.getElementById(checkAllId);
                const rowCheckboxes = document.querySelectorAll(`.${rowCheckboxClass}`);

                if (!checkAll) return;

                checkAll.addEventListener('change', function() {
                    rowCheckboxes.forEach(cb => cb.checked = checkAll.checked);
                });

                rowCheckboxes.forEach(cb => {
                    cb.addEventListener('change', function() {
                        const allChecked = Array.from(rowCheckboxes).every(x => x.checked);
                        checkAll.checked = allChecked;
                    });
                });
            }

            // Inisialisasi untuk masing-masing tabel
            setupSelectAll('checkAllBelumLunas', 'row-checkbox-belum');
            setupSelectAll('checkAllSudahLunas', 'row-checkbox-sudah');
        });
    </script>


    {{-- Skrip cadangan yang dikomentari --}}
    <!--
>>>>>>> 035ca4fe5cec0facf9625bb51946e19ed53787c7
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi dropdown umum
    document.querySelectorAll('.custom-dropdown').forEach(dropdown => {
        const input = dropdown.querySelector('.dropdown-input');
        const list = dropdown.querySelector('.dropdown-list');
        const options = dropdown.querySelectorAll('.dropdown-options li');
        const searchInput = dropdown.querySelector('.dropdown-search-input');

        // toggle dropdown
        input.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.dropdown-list').forEach(dl => {
                if (dl !== list) dl.classList.remove('active');
            });
            list.classList.toggle('active');
            searchInput.focus();
        });

        // klik di luar -> tutup
        document.addEventListener('click', () => list.classList.remove('active'));

        // klik item
        options.forEach(option => {
            option.addEventListener('click', () => {
                if (!option.classList.contains('disabled')) {
                    input.value = option.textContent;
                    input.setAttribute('data-value', option.dataset.value);
                    list.classList.remove('active');
                    input.dispatchEvent(new Event('change'));
                }
            });
        });

        // search filter
        searchInput.addEventListener('keyup', function() {
            const term = this.value.toLowerCase();
            options.forEach(option => {
                option.style.display = option.textContent.toLowerCase().includes(term)
                    ? 'block' : 'none';
            });
        });
    });
});
</script>
-->
@endpush
