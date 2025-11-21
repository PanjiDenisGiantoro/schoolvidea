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
                        <label for="filter_period" class="form-label fw-semibold">Periode Pembayaran</label>
                        <select id="filter_period" class="form-select rounded-pill shadow-sm">
                            <option value="">Pilih Periode</option>
                            <option value="">Semua Periode</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}"
                                    {{ isset($setting) && $setting->start_month == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $i, 1)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    {{-- Tahun Pembayaran --}}
                    <div class="col-md-4">
                        <label for="filter_year" class="form-label fw-semibold">Tahun Periode</label>
                        <select id="filter_year" class="form-select rounded-pill shadow-sm">
                            <option value="">Pilih Tahun</option>
                            <option value="">Semua Tahun</option>
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
                            <th style="width: 200px; text-align: center;">
                                Presensi
                                <div class="custom-presensi-header">
                                    <span class="text-white">JM</span>
                                    <span class="text-white">Hadir</span>
                                    <span class="text-white">T.Hadir</span>
                                    <span class="text-white">H.Staff</span>
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
                            <th style="width: 200px; text-align: center;">
                                Presensi
                                <div class="custom-presensi-header">
                                    <span>JM</span>
                                    <span>Hadir</span>
                                    <span>T.Hadir</span>
                                    <span>H. Staff</span>
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

                    </tbody>
                    </table>
                </div>
            </div>
        </div>

@endsection
@push('scripts')

    <script>
        // Event listener untuk button "Sinkronkan Presensi"
        document.addEventListener('DOMContentLoaded', function() {
            const btnSinkron = document.getElementById('btnSinkron');
            const unitSelect = document.getElementById('filter_unit');
            const officerSelect = document.getElementById('filter_officer');

            if (btnSinkron) {
                btnSinkron.addEventListener('click', async function() {
                    const unitId = unitSelect.value;
                    const officerId = officerSelect.value;

                    if (!unitId) {
                        alert('Mohon pilih Unit terlebih dahulu');
                        return;
                    }

                    // Show loading state
                    const originalText = btnSinkron.innerHTML;
                    btnSinkron.disabled = true;
                    btnSinkron.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Sedang Sinkronisasi...';

                    try {
                        const response = await fetch('/payroll-payment/sync-attendance', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            body: JSON.stringify({
                                unit_id: unitId,
                                officer_id: officerId || null,
                                search: officerId ? null : 'han'
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            alert('Data presensi berhasil disinkronisasi!\n\n' + JSON.stringify(data.data, null, 2));
                            console.log('Sync Success:', data);
                        } else {
                            alert('Gagal sinkronisasi: ' + (data.message || 'Terjadi kesalahan'));
                            console.error('Sync Error:', data);
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                        console.error('Sync Exception:', error);
                    } finally {
                        btnSinkron.disabled = false;
                        btnSinkron.innerHTML = originalText;
                    }
                });
            }
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const unitSelect = document.getElementById('filter_unit');
    const officerSelect = document.getElementById('filter_officer');
    const paymentSelect = document.getElementById('filter_payment');
    const periodSelect = document.getElementById('filter_period');
    const yearSelect = document.getElementById('filter_year');
    const tabelBelumLunas = document.querySelector('#tabelBelumLunas tbody');
    const tabelSudahLunas = document.querySelector('#tabelSudahLunas tbody');



    function resetSelect(selectElement, placeholder = "Pilih") {
        selectElement.innerHTML = `<option value="">${placeholder}</option>`;
    }

    async function getData(url) {
        try {
            let res = await fetch(url);
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return await res.json();
        } catch (err) {
            console.error("Fetch error:", err);
            return null;
        }
    }

    // Load data ketika unit dipilih
    unitSelect.addEventListener('change', async function() {
        const unitId = this.value;
        resetSelect(officerSelect, "Pilih Guru & Staff");
        resetSelect(paymentSelect, "Pilih Pembayaran");

        if (!unitId) return;

        const data = await getData(`/payroll-payment/getByUnit/${unitId}`);
        console.log("Data Unit:", data);

        if (data && data.length) {
            officerSelect.innerHTML =
                `<option value="all">Semua Guru & Staff</option>` +
                data.map(o =>
                    `<option value="${o.id}">${o.user?.name ?? 'Tanpa Nama'}</option>`
                ).join("");
        }
    });

    // Load data ketika officer dipilih
    officerSelect.addEventListener('change', async function() {
        const officerId = this.value;
        resetSelect(paymentSelect, "Pilih Pembayaran");

        if (!officerId || officerId === "all") return;

        // Load detail officer
        const detail = await getData(`/payroll-payment/getOfficerDetail/${officerId}`);
        console.log("Detail Officer:", detail);

        if (detail) {
            document.getElementById('officer_name').innerText = detail.officer_name ?? "-";
            document.getElementById('officer_nip').innerText = detail.officer_nip ?? "-";
            document.getElementById('officer_jabatan').innerText = detail.officer_position ?? "-";
            document.getElementById('officer_no_hp').innerText = detail.officer_no_hp ?? "-";
            document.getElementById('officer_unit').innerText = detail.officer_unit ?? "-";
        }

        // Load komponen pembayaran
        const data = await getData(`/payroll-payment/getByOfficer/${officerId}`);
        console.log("Data Komponen:", data);

        if (data?.components && data.components.length) {
            paymentSelect.innerHTML =
                `<option value="">Pilih Pembayaran</option>` +
                `<option value="all">Semua Pembayaran</option>` +
                data.components.map(p => `
                    <option value="${p.id}">
                        ${p.name ?? 'Komponen Tidak Ditemukan'}
                    </option>`
                ).join("");
        } else {
            paymentSelect.innerHTML = `<option value="">Tidak Ada Pembayaran</option>`;
        }
    });
    let globalAllowance = {
        total_allowance: 0,
        allowances: {}
    };
    // Fungsi untuk load data tabel
    async function loadTableData() {
        const officerId = officerSelect.value;
        const componentId = paymentSelect.value;
        const period = periodSelect.value;
        const year = yearSelect.value;

        if (!officerId || officerId === "all") {
            clearTables();
            return;
        }

        console.log("Loading data dengan parameter:", {
            officerId, componentId, period, year
        });

        const urlBelumLunas = `/payroll-payment/getPayment?officer_id=${officerId}&component_id=${componentId}&period=${period}&year=${year}&status=pending`;
        const urlSudahLunas = `/payroll-payment/getPayment?officer_id=${officerId}&component_id=${componentId}&period=${period}&year=${year}&status=paid`;

        console.log("URL Belum Lunas:", urlBelumLunas);
        console.log("URL Sudah Lunas:", urlSudahLunas);

        try {
            const [dataBelumLunas, dataSudahLunas] = await Promise.all([
                getData(urlBelumLunas),
                getData(urlSudahLunas)
            ]);

            globalAllowance = {
                total_allowance: dataBelumLunas?.total_allowance ||0,
            allowances: dataBelumLunas?.allowances || {}
            }
            console.log('global allowance: ', globalAllowance);

            console.log("Response Belum Lunas:", dataBelumLunas);
            console.log("Response Sudah Lunas:", dataSudahLunas);

            // PERBAIKAN: Gunakan struktur yang sesuai dengan response
            renderBelumLunasTable(dataBelumLunas?.belum_lunas || []);
            renderSudahLunasTable(dataSudahLunas?.sudah_lunas || []);

        } catch (error) {
            console.error("Error loading table data:", error);
        }
    }

    // Event listeners untuk filter
    paymentSelect.addEventListener('change', loadTableData);
    periodSelect.addEventListener('change', loadTableData);
    yearSelect.addEventListener('change', loadTableData);

    function clearTables() {
        tabelBelumLunas.innerHTML = `
            <tr>
                <td colspan="11" class="text-center text-muted">Pilih Guru & Staff terlebih dahulu</td>
            </tr>`;
        tabelSudahLunas.innerHTML = `
            <tr>
                <td colspan="11" class="text-center text-muted">Pilih Guru & Staff terlebih dahulu</td>
            </tr>`;
    }

    function renderBelumLunasTable(data) {
        console.log("Data untuk tabel belum lunas:", data);

        if (!data || !data.length) {
            tabelBelumLunas.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center text-muted">Tidak ada data belum lunas</td>
                </tr>`;
            return;
        }

        tabelBelumLunas.innerHTML = data.map((item, i) => {
            console.log("Item data:", item); // Debug setiap item

            return `
            <tr>
                <td><input type="checkbox" class="row-checkbox-belum"></td>
                <td>${i + 1}</td>
                <td>${item.officer?.name || "-"}</td>
                <td>${formatPaymentMonth(item.payment_month, item.payment_year)}</td>
                <td>${item.component?.name || "-"}</td>
                <td>
                    <div class="custom-presensi-wrapper">
                        <input type="text" class="custom-input-presensi izin" value="${parseInt(item.teaching_hour_month) || 0}"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                        <input type="text" class="custom-input-presensi hadir" value="0"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                        <input type="text" class="custom-input-presensi alpha" value="0"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                        <input type="text" class="custom-input-presensi staff" value="0"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                    </div>
                </td>
                <td>${formatRupiah(item.total_earnings || 0)}</td>
                <td>${formatRupiah(item.total_deductions || 0)}</td>
                <td>${formatRupiah(item.net_payment || 0)}</td>
                <td>
                    <button class="btn btn-primary rounded-pill" data-bs-toggle="modal"
                        data-bs-target="#catatanModal">Catatan</button>
                </td>
                <td>
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-warning rounded-pill">Detail</button>
                        <button class="btn btn-success rounded-pill">Bayar</button>
                    </div>
                </td>
            </tr>
        `}).join("");

        document.querySelectorAll(".custom-input-presensi").forEach(input => {
            input.addEventListener("input", onPresensiChange);
        });


        console.log("HTML yang dihasilkan:", tabelBelumLunas.innerHTML);
    }

    function renderSudahLunasTable(data) {
        console.log("Data untuk tabel sudah lunas:", data);

        if (!data || !data.length) {
            tabelSudahLunas.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center text-muted">Tidak ada data sudah lunas</td>
                </tr>`;
            return;
        }

        tabelSudahLunas.innerHTML = data.map((item, i) => `
            <tr>
                <td><input type="checkbox" class="row-checkbox-sudah"></td>
                <td>${i + 1}</td>
                <td>${item.officer?.name || "-"}</td>
                <td>${formatPaymentMonth(item.payment_month, item.payment_year)}</td>
                <td>${item.component?.name || "-"}</td>
                <td>
                    <div class="custom-presensi-wrapper">
                        <input type="text" class="custom-input-presensi" value="${item.teaching_hour_month || 0}"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                        <input type="text" class="custom-input-presensi" value="0"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                        <input type="text" class="custom-input-presensi" value="0"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                        <input type="text" class="custom-input-presensi alpha" value="0"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                    </div>
                </td>
                <td>${formatRupiah(item.total_earnings || 0)}</td>
                <td>${formatRupiah(item.total_deductions || 0)}</td>
                <td>${formatRupiah(item.net_payment || 0)}</td>
                <td>
                    <button class="btn btn-primary rounded-pill" data-bs-toggle="modal"
                        data-bs-target="#catatanModal">Catatan</button>
                </td>
                <td>
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-warning rounded-pill">Detail</button>
                        <button class="btn btn-success rounded-pill">Edit</button>
                    </div>
                </td>
            </tr>
        `).join("");
    }

    function formatPaymentMonth(month, year) {
        if (!month || !year) return "-";
        const date = new Date(year, month - 1);
        return date.toLocaleDateString("id-ID", { month: "long", year: "numeric" });
    }

    function formatRupiah(amount) {
        if (!amount) return "Rp 0";
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }
    function onPresensiChange() {
        const row = this.closest("tr");

        const izin = parseInt(row.querySelector(".izin")?.value) || 0;
        const hadir = parseInt(row.querySelector(".hadir")?.value) || 0;
        const alpha = parseInt(row.querySelector(".alpha")?.value) || 0;
        const staff = parseInt(row.querySelector(".staff")?.value) || 0;


    }
    // Inisialisasi awal
    clearTables();
});
</script>
@endpush
