@extends('layouts.app')
@section('title', 'Pembayaran Penggajian')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tagihan.css') }}">
@endpush
@section('content')
    @include('partials.page-title', [
        'title' => 'Tambah Transaksi Pembayaran',
        'subTitle' => 'Pembayaran / Keuangan',
    ])
    <div class="row">
        {{-- Form Utama --}}
        <div class="col-md-8">
            <div class="card rounded-4 border-0 p-4 shadow-sm">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <label for="filter_unit" class="form-label fw-semibold">Filter Unit</label>
                        <select id="filter_unit" class="form-select rounded-pill shadow-sm">
                            <option value="">-- Pilih Unit --</option>
                             @foreach ($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Dropdown Guru & Staff --}}
                    <div class="col-md-6">
                        <label for="filter_officer" class="form-label fw-semibold">Pilih Guru & Staff</label>
                        <select id="filter_officer" class="form-select rounded-pill shadow-sm">
                            <option value="">Pilih Guru & Staff</option>
                        </select>
                    </div>
                    {{-- Pilih Pembayaran --}}
                    <div class="col-md-4">
                        <label for="filter_payment" class="form-label fw-semibold">Pilih Pembayaran</label>
                        <select id="filter_payment" class="form-select rounded-pill shadow-sm">
                            <option value="">Pilih Pembayaran</option>
                        </select>
                    </div>

                    {{-- Periode Pembayaran --}}
                    <div class="col-md-4">
                        <label for="filter_periode" class="form-label fw-semibold">Periode Pembayaran</label>
                        <select id="filter_periode" class="form-select rounded-pill shadow-sm">
                            <option value="all">Semua Periode</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}"
                                    {{ isset($setting) && $setting->start_month == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $i, 1)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter_year" class="form-label fw-semibold">Tahun Periode</label>
                        <select id="filter_year" class="form-select rounded-pill shadow-sm">
                            @for ($y = date('Y'); $y <= date('Y') + 5; $y++)
                                <option value="{{ $y }}"
                                    {{ isset($setting) && $setting->start_year == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
        </div>
        {{-- Sidebar Info Guru --}}
        <div class="col-md-4">
            <div class="card row rounded-4 border-0 p-4 shadow-sm">
                    {{--<div class="mb-3 text-center">
                    <img src="" alt="Foto Guru & Staff" class="img-fluid rounded-circle border shadow-sm"
                        width="120">
                </div>--}}
                <ul class="list-unstyled small">
                    <li><strong>Nama Lengkap:</strong> <span id="officer_name">-</span></li>
                    <li><strong>NIP:</strong> <span id="officer_nip">-</span></li>
                    <li><strong>Unit Pendidikan:</strong> <span id="officer_unit">-</span></li>
                    <li><strong>Jabatan:</strong> <span id="officer_jabatan">-</span></li>
                    <li><strong>Nomor Telepon:</strong> <span id="officer_no_hp">-</span></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Daftar Tagihan --}}
        {{-- Tabel Belum Lunas --}}
        <div class="card rounded-4 mt-3 p-4 border-0 shadow-sm">
            <!-- Header tombol toggle -->
            <div>
                <div class="custom-toggle-header">
                    <button id="btnBelumLunas" class="custom-btn-outline-primary custom-active-btn">
                        <i class="ri-money-dollar-circle-line"></i> Belum Lunas
                    </button>
                    <button id="btnSudahLunas" class="custom-btn-outline-primary">
                        <i class="ri-file-list-3-line"></i> Sudah Lunas
                    </button>
                </div>
            </div>


            <!-- Header kartu -->
            <div class="custom-card-header rounded-top-4 p-4 mb-0">
                <h5 class="text-primary fw-bold"><i class="fa fa-list"></i> Daftar Tagihan Per Bulan</h5>
            <div class="d-flex gap-3" id="button-info">
                <div class="">
                    <button id="btnSinkron" class="custom-btn-info">Sinkronkan Presensi</button>
                </div>
                <div class="">
                    <button id="btnProses" class="custom-btn-info">Proses Pembayaran</button>
                </div>
            </div>
            </div>

<div class="modal fade" id="catatanModal" tabindex="-1" aria-labelledby="catatanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header custom-modal-header">
                    <h5 class="modal-title fw-semibold" id="catatanModalLabel">
                        <i class="ri-sticky-note-line"></i> Tambah Catatan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCatatan">
                        <div class="mb-3">

                            <x-input-field type="number" placeholder="Nominal" name="salary"
                                label="Penerimaan Lainnya (Bonus/Sejenisnya)" />
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
            <div id="tabelBelumLunas" class="">
                <div class="table-responsive">
                    <table class="table-bordered table-hover rounded-3 table overflow-hidden text-center align-middle">
                        <thead class="table-primary text-center text-nowrap align-middle">
                        <tr>
                            <th><input class="custom-checkbox" type="checkbox" id="checkAllBelumLunas"></th>
                            <th>#</th>
                            <th>Nama Guru & Staff</th>
                            <th>Periode Pembayaran</th>
                            <th>Tipe Pembayaran</th>
                            <th style="width: 180px; text-align: center;">
                                Presensi
                                <div class="custom-presensi-header gap-4">
                                    <span class="text-white">JM</span>
                                    <span class="text-white">Hadir</span>
                                    <span class="text-white">T.Hadir</span>
                                </div>
                            </th>
                            <th>Penerimaan</th>
                            <th>Total Potongan</th>
                            <th>Penerimaan Bersih</th>
                            <th>Note/Lainnya</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="checkbox" class="row-checkbox-belum "></td>
                            <td>1</td>
                            <td>Ibal Kemed</td>
                            <td>Oktober 2025</td>
                            <td>Gaji</td>
                            <td>
                                <div class="custom-presensi-wrapper">
                                    <input type="text" class="custom-input-presensi izin" style="width: 50px;"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3" >
                                    <input type="text" class="custom-input-presensi hadir" style="width: 50px;"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3" >
                                    <input type="text" class="custom-input-presensi alpha" style="width: 50px;"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3" >
                                </div>
                            </td>
                            <td>Rp.10.000.000</td>
                            <td>Rp.2.000.000</td>
                            <td>Rp.8.000.000</td>
                            <td class="">
                                <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal"
                                    data-bs-target="#catatanModal">Catatan</button>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-warning rounded-pill">Detail</button>
                                    <button class="btn btn-success rounded-pill">Bayar</button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="" id="tabelSudahLunas">
                <div class="table-responsive">
                    <table class="table-bordered table-hover rounded-3 table overflow-hidden text-center align-middle">
                        <thead class="table-primary text-center text-nowrap align-middle">
                             <tr>
                            <th><input class="custom-checkbox" type="checkbox" id="checkAllSudahLunas"></th>
                            <th>#</th>
                            <th>Nama Guru&Staff</th>
                            <th>Periode Pembayaran</th>
                            <th>Tipe Pembayaran</th>
                            <th style="width: 180px; text-align: center;">
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
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="checkbox" class="row-checkbox-sudah "></td>
                            <td>1</td>
                            <td>Ibal Kemed</td>
                            <td>Oktober 2025</td>
                            <td>Gaji</td>
                            <td>
                                <div class="custom-presensi-wrapper">
                                    <input type="text" class="custom-input-presensi izin" style="width: 50px;"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="20" >
                                    <input type="text" class="custom-input-presensi hadir" style="width: 50px;"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="20" >
                                    <input type="text" class="custom-input-presensi alpha" style="width: 50px;"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="20" >
                                </div>
                            </td>
                            <td>Rp.10.000.000</td>
                            <td>Rp.2.000.000</td>
                            <td>Rp.8.000.000</td>
                            <td class="justify-content-center">
                                <button type="button" class="btn custom-btn-purple" data-bs-toggle="modal"
                                    data-bs-target="#catatanModal"> + </button>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-warning">Detail</button>
                                    <button class="btn btn-success">Edit</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>

@endsection
@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {

    const unitSelect = document.getElementById('filter_unit');
    const officerSelect = document.getElementById('filter_officer');
    const paymentSelect = document.getElementById('filter_payment');
    const tabelBelumLunasBody = document.querySelector('#tabelBelumLunas tbody');

    function resetSelect(selectElement, placeholder = "Pilih") {
        selectElement.innerHTML = `<option value="">${placeholder}</option>`;
    }

    async function getData(url) {
        try {
            let res = await fetch(url);
            return await res.json();
        } catch (err) {
            console.error("fetch error:", err);
            return null;
        }
    }

    unitSelect.addEventListener('change', async function() {
        const unitId = this.value;

        resetSelect(officerSelect, "Pilih Guru & Staff");
        resetSelect(paymentSelect, "Pilih Pembayaran");

        if (!unitId) return;

        const data = await getData(`/payroll-payment/getByUnit/${unitId}`);

        if (data && data.length) {
            officerSelect.innerHTML =
                `<option value="all">Semua Guru & Staff</option>` +
                data.map(o =>
                    `<option value="${o.id}">${o.user?.name ?? 'Tanpa Nama'}</option>`
                ).join("");
        }
    });

    officerSelect.addEventListener('change', async function() {
        const officerId = this.value;

        resetSelect(paymentSelect, "Pilih Pembayaran");

        if (!officerId || officerId === "all") return;

        const data = await getData(`/payroll-payment/getByOfficer/${officerId}`);
        console.log(data);
        console.log(data>.components);

        if (data?.components && data?.components?.length) {
            paymentSelect.innerHTML =
                `<option value="all">Semua Pembayaran</option>` +
                data.payment.map(p => `
                    <option value="${p.id}">
                        ${p.name ?? 'Komponen Tidak Ditemukan'}
                    </option>`
                ).join("");
        } else {
            paymentSelect.innerHTML  = `<option value="">Tidak Ada Pembayaran</option>`;
        }

        const detail = await getData(`/payroll-payment/getOfficerDetail/${officerId}`);

        if (detail) {
            document.getElementById('officer_name').innerText = detail.officer_name ?? "-";
            document.getElementById('officer_nip').innerText = detail.officer_nip ?? "-";
            document.getElementById('officer_jabatan').innerText = detail.officer_position ?? "-";
            document.getElementById('officer_no_hp').innerText = detail.officer_no_hp ?? "-";
            document.getElementById('officer_unit').innerText = detail.officer_unit ?? "-";

            if (detail.officer_foto) {
                document.querySelector('img[alt="Foto Guru & Staff"]').src = detail.officer_foto;
            }
        }

    });

});
</script>

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
                    option.style.display = option.textContent.toLowerCase().includes(term) ?
                        'block' : 'none';
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
@endpush
