@extends('layouts.app')
@section('title', 'Tarik Tabungan')

@section('content')
    @include('partials.page-title', [
        'title' => 'Tarik Tabungan',
        'subTitle' => 'Tabungan / Keuangan'
    ])

    <div class="row g-4">
        {{-- Pilih Siswa --}}
        <div class="col-12">
            <div class="card p-4 shadow-sm rounded-4 border-0">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label for="filter_unit" class="form-label fw-semibold">Filter Unit</label>
                        <select id="filter_unit" class="form-control rounded-pill shadow-sm" data-choices data-choices-sorting-false>
                            <option value="">-- Pilih Unit --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter_kelas" class="form-label fw-semibold">Filter Kelas</label>
                        <select id="filter_kelas" class="form-control rounded-pill shadow-sm"data-choices data-choices-sorting-false>
                            <option value="">-- Pilih Kelas --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="siswa_id" class="form-label fw-semibold">Pilih Siswa</label>
                        <select id="siswa_id" class="form-control rounded-pill shadow-sm " data-choices data-choices-sorting-false>
                            <option value="">-- Pilih Siswa --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Siswa dan Form Penarikan --}}
        <div class="col-12">
            <div class="row g-4">
                {{-- Detail Siswa --}}
                <div class="col-md-4">
                    <div class="card shadow-sm rounded-4 border-0 overflow-hidden">
                        <div class="card-header bg-gradient-danger text-white text-center py-3">
                            <h6 class="mb-0 fw-bold text-white">INFOMASI SISWA</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="position-relative d-inline-block">
                                    <img src="{{ asset('assets/images/videa.png') }}"
                                         alt="Foto Siswa"
                                         id="foto_siswa"
                                         class="img-fluid rounded-circle shadow border border-3 border-danger"
                                         style="width: 120px; height: 120px; object-fit: cover;">
                                    <span class="position-absolute bottom-0 end-0 bg-danger rounded-circle p-2 border border-2 border-white">
                                        <i class="bx bx-wallet-alt text-white"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="student-info">
                                <div class="info-item mb-2 p-2 bg-light rounded">
                                    <small class="text-muted d-block mb-1">Nama Lengkap</small>
                                    <strong id="detail_nama" class="text-dark">-</strong>
                                </div>
                                <div class="info-item mb-2 p-2">
                                    <small class="text-muted d-block mb-1">Nomor Induk</small>
                                    <strong id="detail_nisn" class="text-dark">-</strong>
                                </div>
                                <div class="info-item mb-2 p-2 bg-light rounded">
                                    <small class="text-muted d-block mb-1">Kelas</small>
                                    <strong id="detail_kelas" class="text-dark">-</strong>
                                </div>
                                <div class="info-item mb-2 p-3 bg-success bg-opacity-10 rounded border border-success">
                                    <small class="text-muted d-block mb-1">Saldo Tabungan</small>
                                    <div id="saldo_awal" class="fs-5 fw-bold text-success">Rp 0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Transaksi --}}
                <div class="col-md-8">
                    <form action="{{ route('tabungan.tarik.store') }}" method="POST" id="formTarik">
                        @csrf
                        <input type="hidden" name="kelas_id" id="kelas_hidden">
                        <input type="hidden" name="penerima_id" id="penerima_hidden">

                        <div class="card shadow-sm rounded-4 border-0 overflow-hidden">
                            <div class="card-header bg-gradient-danger text-white py-3">
                                <h5 class="mb-0 fw-bold text-white">
                                    <i class="bx bx-wallet-alt me-2"></i>Transaksi Penarikan Tabungan
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="alert alert-warning border-0 shadow-sm mb-4">
                                    <div class="d-flex align-items-start">
                                        <i class="bx bx-info-circle fs-4 me-3 mt-1"></i>
                                        <div>
                                            <strong class="d-block mb-1">Perhatian!</strong>
                                            <small>Pastikan jumlah penarikan tidak melebihi saldo yang tersedia.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="jumlah" class="form-label fw-semibold">
                                        <i class="bx bx-money text-danger me-1"></i>Jumlah Penarikan
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-danger text-white border-0 rounded-start-pill">
                                            <strong>Rp</strong>
                                        </span>
                                        <input type="text" name="jumlah_display" id="jumlah_display"
                                               class="form-control border-0 shadow-sm rounded-end-pill"
                                               placeholder="0" required>
                                        <input type="hidden" name="jumlah" id="jumlah">
                                    </div>
                                    <small class="text-muted">Maksimal penarikan sesuai saldo yang tersedia</small>
                                </div>

                                <div class="mb-4">
                                    <label for="keterangan" class="form-label fw-semibold">
                                        <i class="bx bx-note text-primary me-1"></i>Keterangan
                                    </label>
                                    <textarea name="keterangan" id="keterangan"
                                              class="form-control rounded-4 shadow-sm border-0"
                                              rows="3"
                                              placeholder="Tambahkan catatan transaksi (opsional)"></textarea>
                                </div>

                                <div class="alert alert-danger border-0 shadow-sm mb-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold">Total Penarikan</span>
                                        <div id="jumlah_transaksi" class="fs-4 fw-bold text-danger">Rp 0</div>
                                    </div>
                                </div>

                                <button type="button" id="btnSubmit" class="btn btn-danger btn-lg w-100 rounded-pill shadow-lg animate-btn">
                                    <i class="bx bx-wallet-alt me-2"></i>Proses Penarikan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .bg-gradient-danger {
            background: linear-gradient(135deg, #f56565 0%, #c53030 100%);
        }
        .animate-btn {
            transition: all 0.3s ease-in-out;
            position: relative;
            overflow: hidden;
        }
        .animate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(245, 101, 101, 0.4);
        }
        .animate-btn:active {
            transform: translateY(0);
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(245, 101, 101, 0.25);
            border-color: #f56565;
        }
        .student-info .info-item {
            transition: all 0.2s ease;
        }
        .student-info .info-item:hover {
            background-color: #fff5f5 !important;
            transform: translateX(5px);
        }
        #foto_siswa {
            transition: all 0.3s ease;
        }
        #foto_siswa:hover {
            transform: scale(1.05);
        }
        .alert {
            animation: slideInDown 0.5s ease-out;
        }
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Get query parameters from URL
        function getQueryParams() {
            const params = new URLSearchParams(window.location.search);
            return {
                siswa_id: params.get('siswa_id'),
                unit_id: params.get('unit_id'),
                kelas_id: params.get('kelas_id')
            };
        }

        // Format number with thousands separator
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Remove all non-digit characters
        function unformatNumber(str) {
            return str.replace(/\D/g, '');
        }

        const jumlahDisplay = document.getElementById('jumlah_display');
        const jumlahHidden = document.getElementById('jumlah');
        const jumlahTransaksi = document.getElementById('jumlah_transaksi');
        const saldoAwalEl = document.getElementById('saldo_awal');
        let saldoAwal = 0;
        const filterUnit = document.getElementById('filter_unit');
        const filterKelas = document.getElementById('filter_kelas');
        const siswaSelect = document.getElementById('siswa_id');
        const kelasHidden = document.getElementById('kelas_hidden');
        const penerimaHidden = document.getElementById('penerima_hidden');

        // Initialize Choices for all dropdowns
        const unitChoices = new Choices(filterUnit, {
            removeItemButton: false,
            shouldSort: false
        });

        const kelasChoices = new Choices(filterKelas, {
            removeItemButton: false,
            shouldSort: false
        });

        const siswaChoices = new Choices(siswaSelect, {
            removeItemButton: false,
            shouldSort: false
        });

        filterUnit.addEventListener('change', function() {
            const unitId = this.value;

            // reset select kelas dan siswa
            kelasChoices.clearStore();
            kelasChoices.setChoices([{ value: '', label: '-- Pilih Kelas --', selected: true }], 'value', 'label', true);
            siswaChoices.clearStore();
            siswaChoices.setChoices([{ value: '', label: '-- Pilih Siswa --', selected: true }], 'value', 'label', true);
            kelasHidden.value = '';

            if (!unitId) return;

            fetch(`/unit/by-unit/${unitId}`)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    if (!data.length) {
                        kelasChoices.setChoices([{ value: '', label: 'Tidak ada kelas', selected: true }], 'value', 'label', true);
                        return;
                    }

                    const options = data.map(kelas => ({
                        value: kelas.id,
                        label: kelas.nama_kelas
                    }));

                    kelasChoices.setChoices(options, 'value', 'label', true);
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    kelasChoices.setChoices([{ value: '', label: 'Gagal memuat kelas', selected: true }], 'value', 'label', true);
                });
        });

        // Load siswa berdasarkan kelas
        filterKelas.addEventListener('change', function() {
            const kelasId = this.value;
            kelasHidden.value = kelasId;
            // reset select
            siswaChoices.clearStore(); // bersihkan semua opsi
            siswaChoices.setChoices([{ value: '', label: '-- Pilih Siswa --', selected: true }], 'value', 'label', true);

            if (!kelasId) return;

            fetch(`/siswa/by-kelas/${kelasId}`)
                .then(res => res.json())
                .then(data => {
                    const options = data.map(siswa => ({
                        value: siswa.id,
                        label: siswa.user.name
                    }));
                    siswaChoices.setChoices(options, 'value', 'label', true);
                });
        });

        // Load detail siswa saat dipilih
        siswaSelect.addEventListener('change', function() {
            const siswaId = this.value;
            penerimaHidden.value = siswaId;

            if (!siswaId) return;

            fetch(`/siswa/siswadetail/${siswaId}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('detail_nama').innerText = data.nama_lengkap || '-';
                    document.getElementById('detail_nisn').innerText = data.nisn || '-';
                    document.getElementById('detail_kelas').innerText = data.kelas || '-';

                    if (data.saldo_akhir) {
                        saldoAwal = parseInt(data.saldo_akhir); // simpan saldo untuk validasi
                        saldoAwalEl.innerText = 'Rp ' + saldoAwal.toLocaleString('id-ID');
                    } else {
                        saldoAwal = 0;
                        saldoAwalEl.innerText = 'Rp 0';
                    }

                    // Update foto siswa - gunakan langsung URL dari API
                    const fotoSiswa = document.getElementById('foto_siswa');
                    if (data.foto) {
                        fotoSiswa.src = data.foto;
                    } else {
                        fotoSiswa.src = `{{ asset('assets/images/videa.png') }}`;
                    }
                });
        });

        // Update jumlah transaksi real-time + validasi saldo
        jumlahDisplay.addEventListener('input', function(e) {
            // Get raw value without formatting
            let rawValue = unformatNumber(this.value);

            // Update hidden input with raw value
            jumlahHidden.value = rawValue;

            let value = parseInt(rawValue) || 0;

            if (value > saldoAwal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Saldo Tidak Cukup!',
                    text: `Jumlah penarikan tidak boleh lebih besar dari saldo (Rp ${saldoAwal.toLocaleString('id-ID')})`,
                    confirmButtonColor: '#f56565'
                });
                // Set ke saldo maksimal
                rawValue = saldoAwal.toString();
                jumlahHidden.value = rawValue;
                this.value = formatNumber(rawValue);
                value = saldoAwal;
            } else {
                // Format display value
                if (rawValue) {
                    this.value = formatNumber(rawValue);
                }
            }

            jumlahTransaksi.innerText = 'Rp ' + value.toLocaleString('id-ID');
        });

        // Handle submit dengan SweetAlert confirmation
        document.getElementById('btnSubmit').addEventListener('click', function(e) {
            e.preventDefault();

            const siswaId = penerimaHidden.value;
            const jumlah = jumlahHidden.value;
            const jumlahDisplayValue = jumlahDisplay.value;
            const namaSiswa = document.getElementById('detail_nama').innerText;

            if (!siswaId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Silakan pilih siswa terlebih dahulu',
                    confirmButtonColor: '#f56565'
                });
                return;
            }

            if (!jumlah || jumlah <= 0 || !jumlahDisplayValue) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Silakan masukkan jumlah penarikan yang valid',
                    confirmButtonColor: '#f56565'
                });
                return;
            }

            if (parseInt(jumlah) > saldoAwal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Saldo Tidak Cukup!',
                    text: `Saldo siswa hanya Rp ${saldoAwal.toLocaleString('id-ID')}`,
                    confirmButtonColor: '#f56565'
                });
                return;
            }

            const saldoSetelah = saldoAwal - parseInt(jumlah);

            Swal.fire({
                title: 'Konfirmasi Penarikan',
                html: `
                    <div class="text-start">
                        <p class="mb-2"><strong>Nama Siswa:</strong> ${namaSiswa}</p>
                        <p class="mb-2"><strong>Saldo Saat Ini:</strong> <span class="text-success fw-bold">Rp ${saldoAwal.toLocaleString('id-ID')}</span></p>
                        <p class="mb-2"><strong>Jumlah Penarikan:</strong> <span class="text-danger fw-bold">Rp ${parseInt(jumlah).toLocaleString('id-ID')}</span></p>
                        <p class="mb-2"><strong>Saldo Setelah Penarikan:</strong> <span class="text-info fw-bold">Rp ${saldoSetelah.toLocaleString('id-ID')}</span></p>
                        <hr>
                        <p class="text-muted mb-0">Apakah Anda yakin ingin memproses penarikan ini?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f56565',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bx bx-check me-1"></i> Ya, Proses!',
                cancelButtonText: '<i class="bx bx-x me-1"></i> Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button untuk prevent double click
                    const btn = document.getElementById('btnSubmit');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

                    // Submit form
                    document.getElementById('formTarik').submit();
                }
            });
        });

        // Auto-fill dari query parameter (ketika dari halaman detail tabungan)
        const params = getQueryParams();

        if (params.unit_id && params.kelas_id && params.siswa_id) {
            console.log('Auto-filling from query params:', params);

            // Tunggu sebentar agar semua Choices instance sudah terinisialisasi
            setTimeout(() => {
                // Set filter unit
                unitChoices.setChoiceByValue(params.unit_id);

                // Trigger change untuk load kelas
                const changeEvent = new Event('change', { bubbles: true });
                filterUnit.dispatchEvent(changeEvent);

                // Setelah kelas dimuat, set kelas dan siswa
                setTimeout(() => {
                    // Set filter kelas
                    kelasChoices.setChoiceByValue(params.kelas_id);

                    // Trigger change untuk load siswa
                    filterKelas.dispatchEvent(changeEvent);

                    // Setelah siswa dimuat, set siswa
                    setTimeout(() => {
                        siswaChoices.setChoiceByValue(params.siswa_id);

                        // Trigger change untuk load detail siswa
                        siswaSelect.dispatchEvent(changeEvent);
                    }, 500);
                }, 500);
            }, 100);
        }
    </script>
@endpush
