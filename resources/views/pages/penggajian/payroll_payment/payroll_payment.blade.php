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
                            <option value="all">Semua Guru & Staff</option>
                        </select>
                    </div>
                    {{-- Pilih Pembayaran --}}
                    <div class="col-md-4">
                        <label for="filter_setting" class="form-label fw-semibold">Pilih Pembayaran</label>
                        <select id="filter_setting" class="form-select rounded-pill shadow-sm">
                            <option value="all">Semua Pembayaran</option>
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
        const settingSelect = document.getElementById('filter_setting');
        const periodeSelect = document.getElementById('filter_periode');
        const yearSelect = document.getElementById('filter_year');
        const tabelBelumLunas = document.getElementById('tabelBelumLunas');
        const tabelSudahLunas = document.getElementById('tabelSudahLunas');
        const tabelBelumLunasBody = document.querySelector('#tabelBelumLunas tbody');



        unitSelect.addEventListener('change', function() {
            const unitId = this.value;
            officerSelect.innerHTML = `<option value="">Memuat...</option>`;
            settingSelect.innerHTML = `<option value="">Pilih Setting</option>`;

            if (!unitId) {
                officerSelect.innerHTML = `<option value="">Pilih Guru & Staff</option>`;
                clearTableData();
                clearOfficerInfo();
                return;
            }

            fetch(`/payroll-setting/officers/by-unit/${unitId}`)
            .then(res => res.json())
            .then(data => {
                officerSelect.innerHTML = `<option value="">Pilih Guru & Staff</option>`;
                if (data.length === 0) {
                    officerSelect.innerHTML = `<option value="">Tidak Ada Data</option>`;
                    return;
                }
                data.forEach(d => {
                    officerSelect.innerHTML += `
                        <option value="${d.id}">${d.user.name} - ${d.position?.name ?? '(Tanpa Jabatan)'}</option>
                    `;
                });
            }).catch(err => {
                console.error('Error loading officers:', err);
                officerSelect.innerHTML = `<option value="">Error memuat data</option>`;
            });
        });

        officerSelect.addEventListener('change', function() {
            const officerId = this.value;

            if (!officerId) {
                clearOfficerInfo();
                clearTableData();
                settingSelect.innerHTML = `<option value="">Pilih Setting</option>`;
                return;
            }

            // Load officer details
            fetch(`/payroll-setting/officers/detail/${officerId}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('officer_name').innerText = data.officer_name ?? "-";
                document.getElementById('officer_nip').innerText = data.officer_nip ?? "-";
                document.getElementById('officer_jabatan').innerText = data.officer_jabatan ?? "-";
                document.getElementById('officer_no_hp').innerText = data.officer_no_hp ?? "-";
                document.getElementById('officer_unit').innerText = data.officer_unit ?? "-";
                if (data.officer_foto) {
                    document.querySelector('img[alt="Foto Guru & Staff"]').src = `${data.officer_foto}`;
                }
            })
            .catch(err => {
                console.error('Error loading officer details:', err);
            });

            // Load payment settings
            fetch(`/payroll-payment/getPaymentList/${officerId}`)
            .then(res => res.json())
            .then(data => {
                settingSelect.innerHTML = `<option value="">Pilih Setting</option>`;
                if (data.components && data.components.length > 0) {
                    data.components.forEach(c => {
                        settingSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                    });
                } else {
                    settingSelect.innerHTML = `<option value="">Tidak Ada Setting</option>`;
                }

                // Isi filter periode dan tahun jika ada data
                if (data.periodes && data.periodes.length > 0) {
                    // Anda bisa mengisi periode select jika perlu
                }
                if (data.years && data.years.length > 0) {
                    // Anda bisa mengisi tahun select jika perlu
                }


tabelBelumLunasBody.innerHTML = "";

function loadPaymentTable(officerId, componentId, periode, year) {
    tabelBelumLunasBody.innerHTML = `<tr><td colspan="11">Memuat...</td></tr>`;

    fetch(`/payroll-payment/getPaymentData?officer_id=${officerId}&setting_id=${componentId}&periode=${periode}&year=${year}`)
        .then(res => res.json())
        .then(result => {

            if (!result.success || result.data.length === 0) {
                tabelBelumLunasBody.innerHTML = `
                    <tr>
                        <td colspan="11" class="text-center">Tidak ada data</td>
                    </tr>
                `;
                return;
            }

            tabelBelumLunasBody.innerHTML = "";

            let index = 1;

            result.data.forEach(item => {

                const officerName = item.officer?.user?.name ?? "-";
                const componentName = item.component?.name ?? "-";
                const componentValue = item.component?.value ?? 0;

                const deductionsTotal = item.total_deductions ?? 0;
                const netPayment = item.net_payment ?? 0;

                // Perbaikan parse bulan
                const [year, month] = item.payment_month.split("-");
                const monthName = convertMonth(parseInt(month)) + " " + year;

                const row = `
                    <tr>
                        <td><input type="checkbox" class="row-checkbox-belum"></td>
                        <td>${index++}</td>
                        <td>${officerName}</td>
                        <td>${monthName}</td>
                        <td>${componentName}</td>
                        <td>
                            <div class="custom-presensi-wrapper">
                                <input type="text" class="custom-input-presensi" style="width:50px;">
                                <input type="text" class="custom-input-presensi" style="width:50px;">
                                <input type="text" class="custom-input-presensi" style="width:50px;">
                            </div>
                        </td>
                        <td>Rp.${componentValue.toLocaleString('id-ID')}</td>
                        <td>Rp.${deductionsTotal.toLocaleString('id-ID')}</td>
                        <td>Rp.${netPayment.toLocaleString('id-ID')}</td>
                        <td>
                            <button class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#catatanModal">
                                Catatan
                            </button>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-warning rounded-pill">Detail</button>
                                <button class="btn btn-success rounded-pill">Bayar</button>
                            </div>
                        </td>
                    </tr>
                `;

                tabelBelumLunasBody.innerHTML += row;
            });
        })
        .catch(err => {
            console.error("Error:", err);
            tabelBelumLunasBody.innerHTML = `<tr><td colspan="11">Error memuat data</td></tr>`;
        });
}

function convertMonth(num) {
    const months = [
        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    ];
    return months[num - 1] ?? "-";
}

settingSelect.addEventListener('change', () => {
    loadPaymentTable(
        officerSelect.value,
        settingSelect.value,
        periodeSelect.value || "all",
        yearSelect.value || ""
    );
});



// data.settings.forEach(setting => {

//     const componentsTotal = setting.components.reduce((sum, c) => sum + (c.amount ?? 0), 0);
//     const deductionsTotal = setting.deductions.reduce((sum, d) => sum + (d.amount ?? 0), 0);
//     const penerimaanBersih = componentsTotal - deductionsTotal;

//     const billingPeriod = setting.billing_period ?? 1;
//     const startYear = setting.start_year ?? new Date().getFullYear();

//     // 👉 hasil: ["Januari 2025", "Februari 2025", "Maret 2025"]
//     const listMonths = generateMonths(billingPeriod, startYear);


// listMonths.forEach(period => {

//     setting.components.forEach(comp => {

//         const componentName = comp.component?.name ?? "-";
//         const componentAmount = comp.amount ?? 0;

//         const deductionTotal = deductionsTotal; // boleh beda, jika per-komponen ubah nanti

//         const bersih = componentAmount - deductionTotal;

//         const row = `
//             <tr>
//                 <td><input type="checkbox" class="row-checkbox-belum"></td>
//                 <td>${index++}</td>
//                 <td>${setting.officer.name ?? '-'}</td>

//                 <!-- Periode -->
//                 <td>${period}</td>

//                 <!-- Nama Komponen -->
//                 <td>${componentName}</td>

//                 <!-- Presensi -->
//                 <td>
//                     <div class="custom-presensi-wrapper">
//                         <input type="text" class="custom-input-presensi izin" style="width: 50px;"
//                             value="${setting.teaching_hours}"
//                             onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
//                         <input type="text" class="custom-input-presensi hadir" style="width: 50px;"
//                             onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
//                         <input type="text" class="custom-input-presensi alpha" style="width: 50px;"
//                             onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
//                     </div>
//                 </td>

//                 <!-- Nilai Komponen -->
//                 <td>Rp.${componentAmount.toLocaleString('id-ID')}</td>

//                 <!-- Total Potongan (global) -->
//                 <td>Rp.${deductionsTotal.toLocaleString('id-ID')}</td>

//                 <!-- Penerimaan Bersih -->
//                 <td>Rp.${bersih.toLocaleString('id-ID')}</td>

//                 <td>
//                     <button type="button" class="btn btn-primary rounded-pill"
//                             data-bs-toggle="modal" data-bs-target="#catatanModal">
//                         Catatan
//                     </button>
//                 </td>

//                 <td>
//                     <div class="d-flex justify-content-center gap-2">
//                         <button class="btn btn-warning rounded-pill">Detail</button>
//                         <button class="btn btn-success rounded-pill">Bayar</button>
//                     </div>
//                 </td>
//             </tr>
//         `;

//         tabelBelumLunasBody.innerHTML += row;

//     });

// });

// });

            })
            .catch(err => {
                console.error('Error loading payment settings:', err);
                settingSelect.innerHTML = `<option value="">Error memuat setting</option>`;
            });
        });
function generateMonths(billingPeriod, startYear) {
    const months = [
        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    ];

    let result = [];
    let startMonth = 0; // mulai Januari (index 0)

    for (let i = 0; i < billingPeriod; i++) {
        const monthIndex = (startMonth + i) % 12;
        const year = startYear + Math.floor((startMonth + i) / 12);
        result.push(`${months[monthIndex]} ${year}`);
    }

    return result;
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
@endpush
