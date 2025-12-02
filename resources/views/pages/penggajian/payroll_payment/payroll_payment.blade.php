@extends("layouts.app")
@section("title", "Pembayaran Penggajian")
@push("styles")
    <link rel="stylesheet" href="{{ asset("assets/css/style.css") }}" />
    <link rel="stylesheet" href="{{ asset("assets/css/tagihan.css") }}" />
@endpush

@section("content")
    @include(
        "partials.page-title",
        [
            "title" => "Tambah Transaksi Pembayaran",
            "subTitle" => "Pembayaran / Keuangan",
        ]
    )
    <div class="row">
        {{-- Form Utama --}}
        <div class="col-md-8">
            <div class="card rounded-4 border-0 p-4 shadow-sm">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <label for="filter_unit" class="form-label fw-semibold">
                            Filter Unit
                        </label>
                        <select
                            id="filter_unit"
                            class="form-select rounded-pill shadow-sm"
                        >
                            <option value="">-- Pilih Unit --</option>
                            @foreach ($units as $u)
                                <option value="{{ $u->id }}">
                                    {{ $u->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Dropdown Guru & Staff --}}
                    <div class="col-md-6">
                        <label
                            for="filter_officer"
                            class="form-label fw-semibold"
                        >
                            Pilih Guru & Staff
                        </label>
                        <select
                            id="filter_officer"
                            class="form-select rounded-pill shadow-sm"
                        >
                            <option value="">Pilih Guru & Staff</option>
                        </select>
                    </div>
                    {{-- Pilih Pembayaran --}}
                    <div class="col-md-4">
                        <label for="filter_type" class="form-label fw-semibold">
                            Pilih Pembayaran
                        </label>
                        <select
                            id="filter_type"
                            class="form-select rounded-pill shadow-sm"
                        >
                            <option value="">Pilih Tipe</option>
                        </select>
                    </div>

                    {{-- Periode Pembayaran --}}
                    <div class="col-md-4">
                        <label
                            for="filter_period"
                            class="form-label fw-semibold"
                        >
                            Periode Pembayaran
                        </label>
                        <select
                            id="filter_period"
                            class="form-select rounded-pill shadow-sm"
                        >
                            <option value="">Pilih Periode</option>
                            <option value="">Semua Periode</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option
                                    value="{{ $i }}"
                                    {{ isset($setting) && $setting->start_month == $i ? "selected" : "" }}
                                >
                                    {{ \Carbon\Carbon::createFromDate(null, $i, 1)->translatedFormat("F") }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    {{-- Tahun Pembayaran --}}
                    <div class="col-md-4">
                        <label for="filter_year" class="form-label fw-semibold">
                            Tahun Periode
                        </label>
                        <select
                            id="filter_year"
                            class="form-select rounded-pill shadow-sm"
                        >
                            <option value="">Pilih Tahun</option>
                            <option value="">Semua Tahun</option>
                            @for ($y = date('Y'); $y <= date('Y') + 5; $y++)
                                <option
                                    value="{{ $y }}"
                                    {{ isset($setting) && $setting->start_year == $y ? "selected" : "" }}
                                >
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
                {{--
                    <div class="mb-3 text-center">
                    <img src="" alt="Foto Guru & Staff" class="img-fluid rounded-circle border shadow-sm"
                    width="120">
                    </div>
                --}}
                <ul class="list-unstyled small">
                    <li class="d-flex justify-content-between">
                        <strong>Nama Lengkap:</strong>
                        <span id="officer_name">-</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <strong>NIP:</strong>
                        <span id="officer_nip">-</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <strong>Unit Pendidikan:</strong>
                        <span id="officer_unit">-</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <strong>Jabatan:</strong>
                        <span id="officer_jabatan">-</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <strong>Nomor Telepon:</strong>
                        <span id="officer_no_hp">-</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <strong>Nama Bank:</strong>
                        <span id="officer_bank">-</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <strong>Nomor Rekening:</strong>
                        <span id="officer_norek">-</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <strong>Nomor Virtual Account:</strong>
                        <span id="officer_va">-</span>
                    </li>
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
                <button
                    id="btnBelumLunas"
                    class="custom-btn-outline-primary custom-active-btn"
                >
                    <i class="ri-money-dollar-circle-line"></i>
                    Belum Lunas
                </button>
                <button id="btnSudahLunas" class="custom-btn-outline-primary">
                    <i class="ri-file-list-3-line"></i>
                    Sudah Lunas
                </button>
            </div>
        </div>

        <!-- Header kartu -->
        <div class="custom-card-header rounded-top-4 p-4 mb-0">
            <h5 class="text-primary fw-bold">
                <i class="fa fa-list"></i>
                Daftar Tagihan Per Bulan
            </h5>
            <div class="d-flex gap-3" id="button-info">
                <div class="">
                    <button id="btnSinkron" class="custom-btn-info">
                        Sinkronkan Presensi
                    </button>
                </div>
                <div class="">
                    <button id="btnProses" class="custom-btn-info">
                        Proses Pembayaran
                    </button>
                </div>
            </div>
        </div>

        <div
            class="modal fade"
            id="catatanModal"
            tabindex="-1"
            aria-labelledby="catatanModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header custom-modal-header">
                        <h5
                            class="modal-title fw-semibold"
                            id="catatanModalLabel"
                        >
                            <i class="ri-sticky-note-line"></i>
                            Tambah Catatan
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <form id="formCatatan">
                            <div class="mb-3">
                                <x-input-field
                                    type="text"
                                    placeholder="Nominal"
                                    id="salary_note"
                                    name="salary_note"
                                    label="Penerimaan Lainnya (Bonus/Sejenisnya)"
                                    oninput="formatCurrencyInput(this)"
                                    onkeypress="
                                        return (
                                            event.charCode >= 48 &&
                                            event.charCode <= 57
                                        );
                                    "
                                />
                            </div>
                            <div class="mb-3">
                                <label
                                    for="isiCatatan"
                                    class="form-label fw-semibold"
                                >
                                    Catatan
                                </label>
                                <textarea
                                    class="form-control"
                                    id="isiCatatan"
                                    rows="4"
                                    placeholder="Tulis keterangan tambahan di sini..."
                                ></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-0">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Batal
                        </button>
                        <button type="button" class="btn custom-btn-purple">
                            Simpan Catatan
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div id="tabelBelumLunas" class="">
            <div class="table-responsive">
                <table
                    class="table-bordered table-hover rounded-3 table overflow-hidden text-center align-middle"
                >
                    <thead
                        class="table-primary text-center text-nowrap align-middle"
                    >
                        <tr>
                            <th>
                                <input
                                    class="custom-checkbox"
                                    type="checkbox"
                                    id="checkAllBelumLunas"
                                />
                            </th>
                            <th>#</th>
                            <th>Nama Guru & Staff</th>
                            <th>Periode Pembayaran</th>
                            <th>Tipe Pembayaran</th>
                            <th style="width: 200px; text-align: center">
                                Presensi
                                <div class="custom-presensi-header">
                                    <span class="text-white">JM</span>
                                    <span class="text-white">JB</span>
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
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="" id="tabelSudahLunas">
            <div class="table-responsive">
                <table
                    class="table-bordered table-hover rounded-3 table overflow-hidden text-center align-middle"
                >
                    <thead
                        class="table-primary text-center text-nowrap align-middle"
                    >
                        <tr>
                            <th>#</th>
                            <th>Nama Guru&Staff</th>
                            <th>Periode Pembayaran</th>
                            <th>Tipe Pembayaran</th>
                            <th style="width: 200px; text-align: center">
                                Presensi
                                <div class="custom-presensi-header">
                                    <span class="text-white">JM</span>
                                    <span class="text-white">JB</span>
                                    <span class="text-white">Hadir</span>
                                    <span class="text-white">T.Hadir</span>
                                    <span class="text-white">H. Staff</span>
                                </div>
                            </th>
                            <th>Penerimaan</th>
                            <th>Total Potongan</th>
                            <th>Penerimaan Bersih</th>
                            <th>Note/Lainnya</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btnBelumLunas = document.getElementById('btnBelumLunas');
            const btnSudahLunas = document.getElementById('btnSudahLunas');
            const tabelBelumLunas = document.getElementById('tabelBelumLunas');
            const tabelSudahLunas = document.getElementById('tabelSudahLunas');
            const cardHeader = document.querySelector('#button-info');
            const btnProsesPembayaran = document.getElementById('btnProses');
            const btnProsesSinkron = document.getElementById('btnSinkron');

            // --- Default tampilan: Belum Lunas aktif ---
            btnBelumLunas.classList.add('custom-active-btn');
            btnSudahLunas.classList.remove('custom-active-btn');
            tabelBelumLunas.style.display = 'block';
            tabelSudahLunas.style.display = 'none';
            if (cardHeader) cardHeader.style.display = 'flex';
            if (btnProsesPembayaran)
                btnProsesPembayaran.style.display = 'inline-flex';
            if (btnProsesSinkron)
                btnProsesSinkron.style.display = 'inline-flex';

            // --- Klik Belum Lunas ---
            btnBelumLunas.addEventListener('click', function () {
                btnBelumLunas.classList.add('custom-active-btn');
                btnSudahLunas.classList.remove('custom-active-btn');
                tabelBelumLunas.style.display = 'block';
                tabelSudahLunas.style.display = 'none';

                if (cardHeader) cardHeader.style.display = 'flex';
                if (btnProsesPembayaran)
                    btnProsesPembayaran.style.display = 'inline-flex';
                if (btnProsesSinkron)
                    btnProsesSinkron.style.display = 'inline-flex';
            });

            // --- Klik Sudah Lunas ---
            btnSudahLunas.addEventListener('click', function () {
                btnSudahLunas.classList.add('custom-active-btn');
                btnBelumLunas.classList.remove('custom-active-btn');
                tabelSudahLunas.style.display = 'block';
                tabelBelumLunas.style.display = 'none';

                if (cardHeader) cardHeader.style.display = 'none';
                if (btnProsesPembayaran)
                    btnProsesPembayaran.style.display = 'none';
                if (btnProsesSinkron) btnProsesSinkron.style.display = 'none';
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropdown = document.getElementById('dropdownKelas');
            const input = dropdown.querySelector('.dropdown-input');
            const list = dropdown.querySelector('.dropdown-list');
            const options = dropdown.querySelectorAll('.dropdown-options li');
            const searchInput = dropdown.querySelector(
                '.dropdown-search-input',
            );

            // Toggle dropdown
            input.addEventListener('click', (e) => {
                e.stopPropagation();
                list.classList.toggle('active');
                searchInput.focus();
            });

            // Klik di luar -> tutup dropdown
            document.addEventListener('click', () =>
                list.classList.remove('active'),
            );

            // Klik item
            options.forEach((option) => {
                option.addEventListener('click', () => {
                    input.value = option.textContent;
                    input.setAttribute('data-value', option.dataset.value);
                    list.classList.remove('active');
                    input.dispatchEvent(new Event('change'));
                });
            });

            // Search filter
            searchInput.addEventListener('keyup', function () {
                const term = this.value.toLowerCase();
                options.forEach((option) => {
                    option.style.display = option.textContent
                        .toLowerCase()
                        .includes(term)
                        ? 'block'
                        : 'none';
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const unitSelect = document.getElementById('filter_unit');
            const officerSelect = document.getElementById('filter_officer');
            const typeSelect = document.getElementById('filter_type');
            const periodSelect = document.getElementById('filter_period');
            const yearSelect = document.getElementById('filter_year');
            const tabelBelumLunas = document.querySelector(
                '#tabelBelumLunas tbody',
            );
            const tabelSudahLunas = document.querySelector(
                '#tabelSudahLunas tbody',
            );

            const btnSinkron = document.getElementById('btnSinkron');

            if (btnSinkron) {
                btnSinkron.addEventListener('click', async function () {
                    const unitId = unitSelect.value;
                    const officerId = officerSelect.value;

                    if (!unitId) {
                        //alert('Mohon pilih Unit terlebih dahulu');
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian !!',
                            text: 'Mohon Pilih Unit Terlebih Dahulu',
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Pilih Rentang Periode',
                        html: `
                        <div class="d-flex justify-content-center gap-4">
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Mulai:</label>
                                <input id="startDate" type="date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Akhir:</label>
                                <input id="endDate" type="date" class="form-control">
                            </div>
                        </div>
                        `,
                        focusConfirm: false,
                        confirmButtonText: 'Sinkron',
                        customClass: {
                            confirmButton: 'btn btn-primary rounded-pill',
                            title: 'text-primary ',
                        },
                        preConfirm: async () => {
                            const startDate =
                                document.getElementById('startDate').value;
                            const endDate =
                                document.getElementById('endDate').value;

                            if (!startDate || !endDate) {
                                Swal.showValidationMessage(
                                    'Tanggal Wajib Diisi',
                                );
                                return false;
                            }

                            return { startDate, endDate };
                        },
                        didOpen: () => {
                            const today = new Date()
                                .toISOString()
                                .split('T')[0];
                            const firstDay = new Date();
                            firstDay.setDate(1);
                            const setFirstDay = firstDay
                                .toISOString()
                                .split('T')[0];

                            document.getElementById('startDate').value =
                                setFirstDay;
                            document.getElementById('endDate').value = today;
                        },
                    }).then(async (res) => {
                        if (!res.isConfirmed) return;

                        const { startDate, endDate } = res.value;

                        const originalText = btnSinkron.innerHTML;
                        btnSinkron.disabled = true;
                        btnSinkron.innerHTML =
                            '<i class="ri-loader-4-line ri-spin"></i> Sedang Sinkronisasi...';

                        try {
                            const response = await fetch(
                                '/payroll-payment/sync-attendance',
                                {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN':
                                            document.querySelector(
                                                'meta[name="csrf-token"]',
                                            )?.content || '',
                                        Accept: 'application/json',
                                    },
                                    body: JSON.stringify({
                                        unit_id: unitId,
                                        officer_id: officerId || null,
                                        search: officerId ? null : 'han',
                                        start_period: startDate,
                                        end_period: endDate,
                                    }),
                                },
                            );

                            // Check if response is actually JSON
                            const contentType =
                                response.headers.get('content-type');
                            if (
                                !contentType ||
                                !contentType.includes('application/json')
                            ) {
                                // Session expired or redirected to login page
                                console.error(
                                    'Non-JSON response received. Session may have expired.',
                                );
                                alert(
                                    'Sesi Anda telah berakhir. Halaman akan dimuat ulang.\n\nSilakan login kembali.',
                                );
                                window.location.reload();
                                return;
                            }

                            const data = await response.json();

                            // Check for authentication error
                            if (response.status === 401 || data.expired) {
                                alert(
                                    'Sesi Anda telah berakhir. Halaman akan dimuat ulang.\n\nSilakan login kembali.',
                                );
                                window.location.reload();
                                return;
                            }

                            if (data.success) {
                                //alert('Data presensi berhasil disinkronisasi!\n\nSynced: ' + data.synced_count + '\nError: ' + data.error_count);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Hore Hore Yes!!!',
                                    confirmButtonText: 'Tutup',
                                    customClass: {
                                        confirmButton: 'bg-green',
                                    },
                                    html: `
                                    Data Presensi Berhasil Disinkronisasi<br><br>
                                    Synced: <b>${data.synced_count}</b> <br>
                                    Error: <b>${data.error_count}</b>
                                    `,
                                });
                                console.log('Sync Success:', data);

                                // Refresh the attendance data - reload tabel untuk menampilkan data terbaru
                                if (officerId && officerId !== 'all') {
                                    console.log('Synced data:', data.data);
                                    // Reload tabel untuk menampilkan data presensi yang baru disinkronisasi
                                    loadTableData();
                                }
                            } else {
                                Swal.fire(
                                    'Gagal sinkronisasi: ' +
                                        (data.message || 'Terjadi kesalahan'),
                                );
                                console.error('Sync Error:', data);
                            }
                        } catch (error) {
                            console.error('Sync Exception:', error);

                            // Check if it's a JSON parse error (likely session expired)
                            if (
                                error instanceof SyntaxError &&
                                error.message.includes('JSON')
                            ) {
                                Swal.fire(
                                    'Sesi Anda telah berakhir atau terjadi kesalahan.\n\nHalaman akan dimuat ulang. Silakan login kembali.',
                                );
                                window.location.reload();
                            } else {
                                Swal.fire(
                                    'Error: ' +
                                        error.message +
                                        '\n\nSilakan coba lagi atau hubungi administrator.',
                                );
                            }
                        } finally {
                            btnSinkron.disabled = false;
                            btnSinkron.innerHTML = originalText;
                        }
                    });

                    // Show loading state
                });
            }

            function setupSelectAll(checkAllId, rowCheckboxClass) {
                const checkAll = document.getElementById(checkAllId);
                const rowCheckboxes = document.querySelectorAll(
                    `.${rowCheckboxClass}`,
                );

                if (!checkAll) return;

                checkAll.addEventListener('change', function () {
                    rowCheckboxes.forEach(
                        (cb) => (cb.checked = checkAll.checked),
                    );
                });

                rowCheckboxes.forEach((cb) => {
                    cb.addEventListener('change', function () {
                        const allChecked = [...rowCheckboxes].every(
                            (x) => x.checked,
                        );
                        checkAll.checked = allChecked;
                    });
                });
            }
            function resetSelect(selectElement, placeholder = 'Pilih') {
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
                    console.error('Fetch error:', err);
                    return null;
                }
            }

            // Load data ketika unit dipilih
            unitSelect.addEventListener('change', async function () {
                const unitId = this.value;
                resetSelect(officerSelect, 'Pilih Guru & Staff');
                resetSelect(typeSelect, 'Pilih Pembayaran');

                if (!unitId) return;

                const data = await getData(
                    `/payroll-payment/getByUnit/${unitId}`,
                );
                console.log('Data Unit:', data);

                if (data && data.length) {
                    officerSelect.innerHTML =
                        `<option value="all">Semua Guru & Staff</option>` +
                        data
                            .map(
                                (o) =>
                                    `<option value="${o.id}">${o.user?.name ?? 'Tanpa Nama'}</option>`,
                            )
                            .join('');
                }
            });

            // Load data ketika officer dipilih
            officerSelect.addEventListener('change', async function () {
                const officerId = this.value;
                resetSelect(typeSelect, 'Pilih Pembayaran');

                if (!officerId) return;

                // Load detail officer
                const detail = await getData(
                    `/payroll-payment/getOfficerDetail/${officerId}`,
                );
                console.log('Detail Officer:', detail);

                if (detail) {
                    document.getElementById('officer_name').innerText =
                        detail.officer_name ?? '-';
                    document.getElementById('officer_nip').innerText =
                        detail.officer_nip ?? '-';
                    document.getElementById('officer_jabatan').innerText =
                        detail.officer_position ?? '-';
                    document.getElementById('officer_no_hp').innerText =
                        detail.officer_no_hp ?? '-';
                    document.getElementById('officer_unit').innerText =
                        detail.officer_unit ?? '-';
                    document.getElementById('officer_bank').innerText =
                        detail.officer_bank ?? '-';
                    document.getElementById('officer_norek').innerText =
                        detail.officer_norek ?? '-';
                    document.getElementById('officer_va').innerText =
                        detail.officer_va ?? '-';
                }

                // Load komponen pembayaran
                const data = await getData(
                    `/payroll-payment/getByOfficer/${officerId}`,
                );
                console.log('Data Type:', data);

                if (data && data.length) {
                    typeSelect.innerHTML =
                        `<option value="">Pilih Tipe</option>` +
                        data
                            .map(
                                (p) => `
                    <option value="${p}">
                        ${p ?? 'Tipe Tidak Ditemukan'}
                    </option>`,
                            )
                            .join('');
                } else {
                    typeSelect.innerHTML = `<option value="">Tidak Ada Pembayaran</option>`;
                }
            });
            let globalAllowance = {
                total_allowance: 0,
                allowances: {},
                belum_lunas: {},
                staff: 0,
            };
            const rowDataMap = new Map();
            let globalAttendance = null;
            console.log('globalAttendance: ', globalAttendance);

            async function loadTableData() {
                const officerId = officerSelect.value;
                const type = typeSelect.value;
                const period = periodSelect.value;
                const year = yearSelect.value;

                if (!officerId) {
                    clearTables();
                    return;
                }

                console.log('Loading data dengan parameter:', {
                    officerId,
                    type,
                    period,
                    year,
                });

                const urlBelumLunas = `/payroll-payment/getPayment?officer_id=${officerId}&type=${type}&period=${period}&year=${year}&status=pending`;
                const urlSudahLunas = `/payroll-payment/getPayment?officer_id=${officerId}&type=${type}&period=${period}&year=${year}&status=paid`;

                console.log('URL Belum Lunas:', urlBelumLunas);
                console.log('URL Sudah Lunas:', urlSudahLunas);

                try {
                    const [dataBelumLunas, dataSudahLunas] = await Promise.all([
                        getData(urlBelumLunas),
                        getData(urlSudahLunas),
                    ]);

                    globalAllowance = {
                        total_allowance: dataBelumLunas?.total_allowance || 0,
                        allowances: dataBelumLunas?.allowances || {},
                        belum_lunas: dataBelumLunas?.belum_lunas || {},
                        staff: dataBelumLunas?.staff || 0,
                    };

                    // Simpan data attendance untuk digunakan di render table
                    //globalAttendance = dataBelumLunas?.attendance || null;
                    if (dataBelumLunas?.attendanceMap) {
                        globalAttendance = {
                            attendanceMap: dataBelumLunas.attendanceMap,
                        };
                    } else if (dataBelumLunas?.attendance) {
                        globalAttendance = {
                            attendanceMap: {
                                [officerId]: dataBelumLunas.attendance,
                            },
                        };
                    } else {
                        globalAttendance = { attendanceMap: {} };
                    }
                    console.log('global attendance: ', globalAttendance);

                    // PERBAIKAN: Gunakan struktur yang sesuai dengan response
                    renderBelumLunasTable(dataBelumLunas?.belum_lunas || []);
                    renderSudahLunasTable(dataSudahLunas?.sudah_lunas || []);
                } catch (error) {
                    console.error('Error loading table data:', error);
                }
            }

            // Event listeners untuk filter
            typeSelect.addEventListener('change', loadTableData);
            periodSelect.addEventListener('change', loadTableData);
            yearSelect.addEventListener('change', loadTableData);

            function clearTables() {
                tabelBelumLunas.innerHTML = `
            <tr>
                <td colspan="11" class="text-center text-muted">Pilih Guru & Staff dan Pembayaran Terlebih Dahulu</td>
            </tr>`;
                tabelSudahLunas.innerHTML = `
            <tr>
                <td colspan="11" class="text-center text-muted">Pilih Guru & Staff dan Pembayaran Terlebih Dahulu</td>
            </tr>`;
            }

            let selectedRow = null;
            function renderBelumLunasTable(data) {
                rowDataMap.clear();

                if (!data || !data.length) {
                    tabelBelumLunas.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center text-muted">Tidak ada data belum lunas</td>
                </tr>`;
                    return;
                }

                tabelBelumLunas.innerHTML = data
                    .map((item, i) => {
                        const officerId = item.officer?.id;
                        let presenceCount = 0;
                        let absenceCount = 0;
                        let staffCount = 0;

                        if (globalAttendance?.attendanceMap && officerId) {
                            const attendance =
                                globalAttendance.attendanceMap[officerId];
                            if (
                                attendance &&
                                attendance.month === item.payment_month &&
                                attendance.year === item.payment_year
                            ) {
                                presenceCount = attendance.presence_count || 0;
                                absenceCount = attendance.absence_count || 0;
                                staffCount = attendance.presence || 0;
                            }
                        }
                        return `
            <tr data-item='${JSON.stringify(item)}'
                data-base-earnings="${item.total_earnings || 0}"
                data-base-deductions="${item.total_deductions || 0}"
                data-st-hadir="${item.st_hadir || 0}"
                data-status="pending"
                >
                <td><input type="checkbox" class="row-checkbox-belum"></td>
                <td>${i + 1}</td>
                <td>${item.officer?.name || '-'}</td>
                <td>${formatPaymentMonth(item.payment_month, item.payment_year)}</td>
                <td>${item.type || '-'}</td>
                <td>
                    <div class="custom-presensi-wrapper">
                        <input type="text" class="custom-input-presensi hadir_week" value="${parseInt(item.teaching_hour_week) || 0}"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                        <input type="text" class="custom-input-presensi hadir_month" value="${parseInt(item.teaching_hour_month) || 0}"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                        <input type="text" class="custom-input-presensi hadir" value="${presenceCount}"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                        <input type="text" class="custom-input-presensi alpha" value="${absenceCount}"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                        <input type="text" class="custom-input-presensi staff" value="${staffCount}"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                    </div>
                </td>
                <td>${formatRupiah(item.total_earnings || 0)}</td>
                <td>${formatRupiah(item.total_deductions || 0)}</td>
                <td>${formatRupiah(item.net_payment || 0)}</td>
                <td>
                    <button class="btn btn-primary rounded-pill btn-catatan" data-bs-toggle="modal"
                        data-bs-target="#catatanModal">Catatan</button>
                </td>
                <td>
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-warning rounded-pill">Detail</button>
                        <button class="btn btn-success rounded-pill btn-bayar">Bayar</button>

                    </div>
                </td>
            </tr>
        `;
                    })
                    .join('');

                document
                    .querySelectorAll('.custom-input-presensi')
                    .forEach((input) => {
                        input.addEventListener('input', onPresensiChange);
                    });
                document.querySelectorAll('.btn-bayar').forEach((btn) => {
                    btn.addEventListener('click', onClickBayar);
                });
                document.querySelectorAll('tr[data-item]').forEach((row) => {
                    const input = row.querySelector('.hadir');
                    if (input) {
                        onPresensiChange.call(input);
                    }
                });

                setupSelectAll('checkAllBelumLunas', 'row-checkbox-belum');
                console.log(
                    'row-checkbox-belum:',
                    document.querySelectorAll('.row-checkbox-belum').length,
                );
            }
            function renderSudahLunasTable(data) {
                if (!data || !data.length) {
                    tabelSudahLunas.innerHTML = `<tr><td colspan="11" class="text-center text-muted">Tidak ada data sudah lunas</td></tr>`;
                    return;
                }

                tabelSudahLunas.innerHTML = data
                    .map((item, i) => {
                        const officerId = item.officer?.id;
                        let presenceCount = 0;
                        let absenceCount = 0;
                        let staffCount = 0;

                        if (globalAttendance?.attendanceMap && officerId) {
                            const attendance =
                                globalAttendance.attendanceMap[officerId];
                            if (
                                attendance &&
                                attendance.month === item.payment_month &&
                                attendance.year === item.payment_year
                            ) {
                                presenceCount = attendance.presence_count || 0;
                                absenceCount = attendance.absence_count || 0;
                                staffCount = attendance.presence || 0;
                            }
                        }

                        return `
            <tr data-status="paid"
            data-item='${JSON.stringify(item)}'>
                <td>${i + 1}</td>
                <td>${item.officer?.name || '-'}</td>
                <td>${formatPaymentMonth(item.payment_month, item.payment_year)}</td>
                <td>${item.type || '-'}</td>
                <td>
                    <div class="custom-presensi-wrapper">
                        <input type="text" class="custom-input-presensi hadir_week" value="${parseInt(item.teaching_hour_week || 0)}" onkeypress="return event.charCode >=48 && event.charCode <=57" maxLength="3">
                        <input type="text" class="custom-input-presensi hadir_month" value="${parseInt(item.teaching_hour_month) || 0}"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3">
                        <input type="text" class="custom-input-presensi hadir" value="${presenceCount}" onkeypress="return event.charCode >=48 && event.charCode <=57" maxLength="3">
                        <input type="text" class="custom-input-presensi alpha" value="${absenceCount}" onkeypress="return event.charCode >=48 && event.charCode <=57" maxLength="3">
                        <input type="text" class="custom-input-presensi staff" value="${staffCount}" onkeypress="return event.charCode >=48 && event.charCode <=57" maxLength="3">
                    </div>
                </td>
                <td>${formatRupiah(item.total_earnings || 0)}</td>
                <td>${formatRupiah(item.total_deductions || 0)}</td>
                <td>${formatRupiah(item.net_payment || 0)}</td>
                <td><button class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#catatanModal">Catatan</button></td>
                <td>
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-info rounded-pill"><i class="ri-eye-line"></i></button>
                        <button class="btn btn-success rounded-pill"><i class="ri-pencil-line"></i></button>
                        <a href="{{ url('payroll-payment/slip/${item.id}') }}" target="_blank" class="btn btn-warning rounded-pill shadow-sm">
                            <i class="ri-printer-line"></i>
                        </a>
                    </div>
                </td>
            </tr>`;
                    })
                    .join('');
            }

            //proses modal ctatan

            document.addEventListener('click', function (e) {
                if (e.target.closest('.btn-catatan')) {
                    selectedRow = e.target.closest('tr');
                    let item = {};
                    try {
                        item = JSON.parse(selectedRow.dataset.item || '{}');
                    } catch {}

                    const status = selectedRow.dataset.status;

                    // ⚡ Perbaikan: pakai .value
                    document.getElementById('salary_note').value =
                        item.salary_note
                            ? formatCurrencyInput(item.salary_note)
                            : '';
                    document.getElementById('isiCatatan').value =
                        item.text_note ?? '';

                    document.getElementById('salary_note').disabled =
                        status === 'paid';
                    document.getElementById('isiCatatan').disabled =
                        status === 'paid';
                }
            });

            document
                .querySelector('#catatanModal .btn.custom-btn-purple')
                .addEventListener('click', function () {
                    if (!selectedRow) return;

                    const status = selectedRow.dataset.status;
                    if (status === 'paid') {
                        bootstrap.Modal.getInstance(
                            document.getElementById('catatanModal'),
                        ).hide();
                        document.getElementById('salary_note').disabled = true;
                        document.getElementById('isiCatatan').disabled = true;
                    } else {
                        document.getElementById('salary_note').disabled = false;
                        document.getElementById('isiCatatan').disabled = false;
                    }

                    const noteValue =
                        unformatCurrency(
                            document.getElementById('salary_note').value,
                        ) || 0;
                    const textNote = String(
                        document.getElementById('isiCatatan').value || ' ',
                    ).trim();

                    let item = {};
                    try {
                        item = JSON.parse(selectedRow.dataset.item || '{}');
                    } catch {}

                    // simpan salary_note pada item
                    item.salary_note = noteValue;
                    item.text_note = textNote;
                    selectedRow.dataset.item = JSON.stringify(item);

                    // panggil ulang kalkulasi (agar total earning berubah)
                    const oneInput = selectedRow.querySelector('.hadir');
                    if (oneInput) {
                        onPresensiChange.call(oneInput);
                    }

                    bootstrap.Modal.getInstance(
                        document.getElementById('catatanModal'),
                    ).hide();
                });
            //proses pembayaran masal
            document
                .getElementById('btnProses')
                .addEventListener('click', async () => {
                    const rows = Array.from(
                        document.querySelectorAll(
                            '.row-checkbox-belum:checked',
                        ),
                    ).map((chk) => chk.closest('tr'));

                    if (!rows.length) {
                        Swal.fire(
                            'Tidak ada data',
                            'Pilih minimal satu data untuk bayar',
                            'warning',
                        );
                        return;
                    }

                    // Ambil item dari rowDataMap
                    const items = rows.map((row) => {
                        try {
                            let item =
                                rowDataMap.get(row) ||
                                JSON.parse(row.dataset.item || '{}');

                            item.text_note = item.text_note || '';
                            item.earning = item.total_earnings || 0;
                            item.deduction = item.total_deductions || 0;
                            item.net_payment = item.net_payment || 0;

                            return item;
                        } catch (e) {
                            return {};
                        }
                    });
                    console.log('data items: ', items);

                    // Hitung total
                    const totalTagihan = items.reduce(
                        (sum, it) => sum + (it.net_payment || 0),
                        0,
                    );
                    const totalItems = items.length;

                    Swal.fire({
                        icon: 'warning',
                        title: 'Proses Pembayaran Masal',
                        html: `
            <p>Jumlah Data: <strong>${totalItems}</strong></p>
            <p>Total Pembayaran Gaji: <strong class="text-success">${formatRupiah(totalTagihan)}</strong></p>
        `,
                        confirmButtonText: 'Bayar Semua',
                        showCancelButton: true,
                        cancelButtonText: 'Batal',
                        customClass: {
                            confirmButton: 'bg-green rounded-pill',
                            cancelButton: 'bg-red rounded-pill',
                        },
                    }).then(async (result) => {
                        if (!result.isConfirmed) return;

                        const csrfToken = document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content;

                        try {
                            const response = await fetch(
                                `/payroll-payment/paymentAll`,
                                {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                    },
                                    body: JSON.stringify({
                                        totalTagihan,
                                        items,
                                    }),
                                },
                            );

                            const resultJson = await response.json();
                            console.log('response:', resultJson);

                            if (!resultJson.status) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: resultJson.message || 'Terjadi Error',
                                });
                                return;
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Pembayaran Berhasil!',
                                timer: 2000,
                                showConfirmButton: false,
                            });

                            loadTableData();
                        } catch (err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error !!!',
                                text: 'Gagal Terhubung Ke Server',
                            });
                            console.error(err);
                        }
                    });
                });

            function unformatCurrency(str) {
                return parseInt(str.replace(/[^\d]/g, '')) || 0;
            }

            function formatPaymentMonth(month, year) {
                if (!month || !year) return '-';
                const date = new Date(year, month - 1);
                return date.toLocaleDateString('id-ID', {
                    month: 'long',
                    year: 'numeric',
                });
            }

            function formatRupiah(amount) {
                if (!amount) return 'Rp 0';
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                }).format(amount);
            }
            //fungsi untuk menghitung total pembayaran
            function onPresensiChange() {
                const row = this.closest('tr');

                let item = {};
                try {
                    item = JSON.parse(row.dataset.item || '{}');
                } catch (err) {
                    console.error('JSON parse error: ', err);
                }

                if (!item || Object.keys(item).length === 0) {
                    console.warn('Fallback: Asigning data directly');
                }

                const hadir_week =
                    parseInt(row.querySelector('.hadir_week')?.value) || 0;
                const hadir_month =
                    parseInt(row.querySelector('.hadir_month')?.value) || 0;
                const hadir = parseInt(row.querySelector('.hadir')?.value) || 0;
                const alpha = parseInt(row.querySelector('.alpha')?.value) || 0;
                const staff = parseInt(row.querySelector('.staff')?.value) || 0;
                const salaryNote = parseFloat(item.salary_note) || 0;
                console.log(salaryNote);
                let totalEarnings = parseFloat(item.total_earnings) || 0;
                let totalDeductions = parseFloat(item.total_deductions) || 0;
                let staffAllowance = parseFloat(globalAllowance.staff) || 0;

                const staffTotal = staff * staffAllowance;
                const allowanceRate = globalAllowance.total_allowance || 0;
                totalEarnings +=
                    hadir * allowanceRate + salaryNote + staffTotal;
                result = hadir * allowanceRate;

                //const absenceDeductionRate = 10000;
                //totalDeductions += alpha * absenceDeductionRate;

                const netPayment = totalEarnings - totalDeductions;
                row.children[6].innerHTML = formatRupiah(totalEarnings);
                row.children[7].innerHTML = formatRupiah(totalDeductions);
                row.children[8].innerHTML = formatRupiah(netPayment);

                item.total_earnings = totalEarnings;
                item.total_deductions = totalDeductions;
                item.net_payment = netPayment;
                item.hadir_week = hadir_week;
                item.hadir_month = hadir_month;
                item.hadir = hadir;
                item.alpha = alpha;
                item.staff = staff;

                rowDataMap.set(row, item);
            }
            //fungsi untuk button proses pembayaran
            function onClickBayar() {
                const row = this.closest('tr');
                let item = {};
                try {
                    item =
                        rowDataMap.get(row) ||
                        JSON.parse(row.dataset.item || '{}');
                } catch (err) {
                    console.error('JSON parse error: ', err);
                }

                if (!item || Object.keys(item).length === 0) {
                    console.warn('Fallback: Asigning data directly');
                }
                const salaryNote = item.salary_note || '0';
                const textNote = item.text_note || ' ';

                Swal.fire({
                    icon: 'warning',
                    title: `Pembayaran ${item.officer?.name}`,
                    html: `
            <p>Nominal Gaji: <strong class="text-success">${formatRupiah(item.net_payment)}</strong></p>
        `,
                    confirmButtonText: 'Bayar',
                    showCancelButton: true,
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'bg-green rounded-pill',
                        cancelButton: 'bg-red rounded-pill',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        processPayment(
                            item.id,
                            item.net_payment,
                            item.total_earnings,
                            item.total_deductions,
                            textNote,
                            salaryNote,
                        );
                    }
                });
            }

            async function processPayment(
                id,
                amount,
                earning,
                deduction,
                notes,
                salarynote,
            ) {
                const csrfToken = document.querySelector(
                    'meta[name="csrf-token"]',
                ).content;

                try {
                    const response = await fetch(
                        `/payroll-payment/payment/${id}`,
                        {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                amount,
                                earning,
                                deduction,
                                notes,
                                salarynote,
                            }),
                        },
                    );

                    const result = await response.json();
                    console.log('response:', result);

                    // ❗ CEK STATUS JSON DARI BACKEND
                    if (!result.status) {
                        // alert(result.message || "Terjadi error");
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: result.message || 'Terjadi Error',
                        });
                        return;
                    }

                    // ✔ hanya kalau status = true
                    // alert("Pembayaran Berhasil");
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Pembayaran Berhasil!',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    loadTableData();
                } catch (err) {
                    // alert("Gagal terhubung ke server");
                    Swal.fire({
                        icon: 'error',
                        title: 'Error !!!',
                        text: 'Gagal Terhubung Ke Server',
                    });
                    console.error(err);
                }
            }

            // Inisialisasi awal
            clearTables();
        });
    </script>
    <script>
        function formatCurrencyInput(input) {
            let value = input.value.replace(/[^\d]/g, '');
            if (value === '') {
                input.value = '';
                return;
            }
            input.value = 'Rp. ' + new Intl.NumberFormat('id-ID').format(value);
        }

        // Sebelum submit → hapus semua titik agar dikirim sebagai angka murni
        document.addEventListener('submit', function (e) {
            const inputs = document.querySelectorAll(
                '.component-value, .deduction-value, [id$="_allowance"], [name="note_salary"], [name="nilai"]',
            );
            inputs.forEach((input) => {
                input.value = parseInt(input.value.replace(/\./g, ''));
                console.log('parseint : ', input.value);
            });
        });
    </script>
@endpush
