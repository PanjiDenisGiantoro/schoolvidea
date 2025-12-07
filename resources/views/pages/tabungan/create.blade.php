@extends('layouts.app')
@section('title', 'Tambah Transaksi Tabungan')

@section('content')
    @include('partials.page-title', [
        'title' => 'Tambah Transaksi',
        'subTitle' => 'Tabungan / Keuangan',
    ])

    <div class="row g-4">
        {{-- Pilih Siswa --}}
        <div class="col-12">
            <div class="card rounded-4 border-0 p-4 shadow-sm">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label for="filter_unit" class="form-label fw-semibold">Filter Unit</label>
                        <select id="filter_unit" class="form-control rounded-pill shadow-sm">
                            <option value="">-- Pilih Unit --</option>
                            @foreach ($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter_kelas" class="form-label fw-semibold">Filter Kelas</label>
                        <select id="filter_kelas" class="form-control rounded-pill shadow-sm">
                            <option value="">-- Pilih Kelas --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="siswa_id" class="form-label fw-semibold">Pilih Siswa</label>
                        <select id="siswa_id" class="form-control rounded-pill shadow-sm">
                            <option value="">-- Pilih Siswa --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Siswa dan Form Tabungan --}}
        <div class="col-12">
            <div class="row g-4">
                {{-- Detail Siswa --}}
                <div class="col-md-4">
                    <div class="card rounded-4 overflow-hidden border-0 shadow-sm">
                        <div class="card-header bg-gradient-success py-3 text-center text-white ">
                            <h6 class="fw-bold mb-0 text-white">INFORMASI SISWA</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4 text-center">
                                <div class="position-relative d-inline-block">
                                    <img src="{{ asset('assets/images/videa.png') }}" alt="Foto Siswa" id="foto_siswa"
                                        class="img-fluid rounded-circle border-3 border-primary border shadow"
                                        style="width: 120px; height: 120px; object-fit: cover;">
                                    <span
                                        class="position-absolute bg-success rounded-circle bottom-0 end-0 border border-2 border-white p-2">
                                        <i class="bx bx-check text-white"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="student-info">
                                <div class="info-item bg-light mb-2 rounded p-2">
                                    <small class="text-muted d-block mb-1">Nama Lengkap</small>
                                    <strong id="detail_nama" class="text-dark">-</strong>
                                </div>
                                <div class="info-item mb-2 p-2">
                                    <small class="text-muted d-block mb-1">Nomor Induk</small>
                                    <strong id="detail_nisn" class="text-dark">-</strong>
                                </div>
                                <div class="info-item bg-light mb-2 rounded p-2">
                                    <small class="text-muted d-block mb-1">Kelas</small>
                                    <strong id="detail_kelas" class="text-dark">-</strong>
                                </div>
                                <div class="info-item mb-2 p-2">
                                    <small class="text-muted d-block mb-1">Telepon</small>
                                    <strong id="detail_telp" class="text-dark">-</strong>
                                </div>
                                <div class="info-item mb-2 p-2">
                                    <small class="text-muted d-block mb-1">No Virtual Account</small>
                                    <strong id="detail_va" class="text-dark">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Transaksi --}}
                <div class="col-md-8">
                    <form action="{{ route('tabungan.store') }}" method="POST" id="formTabungan">
                        @csrf
                        <input type="hidden" name="kelas_id" id="kelas_hidden">
                        <input type="hidden" name="penerima_id" id="penerima_hidden">

                        <div class="card rounded-4 overflow-hidden border-0 shadow-sm">
                            <div class="card-header bg-gradient-success py-3 text-white">
                                <h5 class="fw-bold mb-0  text-white">
                                    <i class="bx bx-wallet me-2 "></i>Transaksi Setoran Tabungan
                                </h5>
                            </div>
                            <div class="card-body gap-4 p-4">
                                <div class="alert alert-info mb-4 border-0 shadow-sm">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-info-circle fs-4 me-3"></i>
                                        <div>
                                            <strong>Saldo Saat Ini</strong>
                                            <div id="saldo_awal" class="fs-5 fw-bold text-success mt-1">Rp 0</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="jumlah" class="form-label fw-semibold">
                                        <i class="bx bx-money text-success me-1"></i>Jumlah Setoran
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-success rounded-start-pill border-0 text-white">
                                            <strong>Rp</strong>
                                        </span>
                                        <input type="text" name="jumlah_display" id="jumlah_display"
                                            class="form-control rounded-end-pill border-0 shadow-sm" placeholder="0"
                                            required>
                                        <input type="hidden" name="jumlah" id="jumlah">
                                    </div>
                                    <small class="text-muted" id="jumlah_hint">Minimal setoran Rp 1.000</small>
                                    <small class="text-danger d-none" id="jumlah_warning">
                                        <i class="bx bx-error-circle me-1"></i>Jumlah setoran tidak boleh kurang dari Rp 1.000!
                                    </small>
                                </div>

                                <div class="mb-4">
                                    <label for="keterangan" class="form-label fw-semibold">
                                        <i class="bx bx-note text-primary me-1"></i>Keterangan
                                    </label>
                                    <textarea name="keterangan" id="keterangan" class="form-control rounded-4 border-0 shadow-sm" rows="3"
                                        placeholder="Tambahkan catatan transaksi (opsional)"></textarea>
                                </div>

                                <div class="alert alert-success mb-4 mt-4 border-0 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold">Total Setoran</span>
                                        <div id="jumlah_transaksi" class="fs-4 fw-bold text-success">Rp 0</div>
                                    </div>
                                </div>

                                <button type="button" id="btnSubmit"
                                    class="btn btn-success btn-lg w-100 rounded-pill animate-btn shadow-lg">
                                    <i class="bx bx-check-circle me-2"></i>Proses Setoran
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

        .bg-gradient-success {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        }

        .animate-btn {
            transition: all 0.3s ease-in-out;
            position: relative;
            overflow: hidden;
        }

        .animate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(72, 187, 120, 0.4);
        }

        .animate-btn:active {
            transform: translateY(0);
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(72, 187, 120, 0.25);
            border-color: #48bb78;
        }

        .form-control.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
        }

        #jumlah_warning {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .student-info .info-item {
            transition: all 0.2s ease;
        }

        .student-info .info-item:hover {
            background-color: #f0f9ff !important;
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

        // Update jumlah transaksi real-time
        const jumlahDisplay = document.getElementById('jumlah_display');
        const jumlahHidden = document.getElementById('jumlah');
        const jumlahTransaksi = document.getElementById('jumlah_transaksi');
        const jumlahWarning = document.getElementById('jumlah_warning');
        const jumlahHint = document.getElementById('jumlah_hint');

        jumlahDisplay.addEventListener('input', function(e) {
            // Get raw value without formatting
            let rawValue = unformatNumber(this.value);

            // Update hidden input with raw value
            jumlahHidden.value = rawValue;

            // Format display value
            if (rawValue) {
                this.value = formatNumber(rawValue);
            }

            // Update jumlah transaksi display
            const value = parseInt(rawValue) || 0;
            jumlahTransaksi.innerText = 'Rp ' + value.toLocaleString('id-ID');

            // Show/hide warning based on value
            if (value > 0 && value < 1000) {
                jumlahWarning.classList.remove('d-none');
                jumlahHint.classList.add('d-none');
                this.classList.add('is-invalid');
            } else {
                jumlahWarning.classList.add('d-none');
                jumlahHint.classList.remove('d-none');
                this.classList.remove('is-invalid');
            }
        });

        // Handle submit dengan SweetAlert confirmation
        document.getElementById('btnSubmit').addEventListener('click', function(e) {
            e.preventDefault();

            const siswaId = document.getElementById('penerima_hidden').value;
            const jumlah = document.getElementById('jumlah').value;
            const jumlahDisplay = document.getElementById('jumlah_display').value;
            const namaSiswa = document.getElementById('detail_nama').innerText;

            if (!siswaId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Silakan pilih siswa terlebih dahulu',
                    confirmButtonColor: '#48bb78'
                });
                return;
            }

            if (!jumlah || jumlah <= 0 || !jumlahDisplay) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Silakan masukkan jumlah setoran yang valid',
                    confirmButtonColor: '#48bb78'
                });
                return;
            }

            if (parseInt(jumlah) < 1000) {
                Swal.fire({
                    icon: 'error',
                    title: 'Setoran Tidak Valid!',
                    text: 'Jumlah setoran minimal Rp 1.000',
                    confirmButtonColor: '#48bb78'
                });
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Setoran',
                html: `
                    <div class="text-start">
                        <p class="mb-2"><strong>Nama Siswa:</strong> ${namaSiswa}</p>
                        <p class="mb-2"><strong>Jumlah Setoran:</strong> <span class="text-success fw-bold">Rp ${parseInt(jumlah).toLocaleString('id-ID')}</span></p>
                        <hr>
                        <p class="text-muted mb-0">Apakah Anda yakin ingin memproses setoran ini?</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#48bb78',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="bx bx-check me-1"></i> Ya, Proses!',
                cancelButtonText: '<i class="bx bx-x me-1"></i> Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button untuk prevent double click
                    const btn = document.getElementById('btnSubmit');
                    btn.disabled = true;
                    btn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

                    // Submit form
                    document.getElementById('formTabungan').submit();
                }
            });
        });

        // Element DOM
        const filterUnit = document.getElementById('filter_unit');
        const filterKelas = document.getElementById('filter_kelas');
        const siswaSelect = document.getElementById('siswa_id');
        const kelasHidden = document.getElementById('kelas_hidden');
        const penerimaHidden = document.getElementById('penerima_hidden');

        // Initialize Choices for dependent dropdowns
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

        // Load kelas berdasarkan unit
        filterUnit.addEventListener('change', function() {
            const unitId = this.value;
            console.log('Unit changed, loading kelas for unitId:', unitId);

            // reset select kelas dan siswa
            kelasChoices.clearStore();
            kelasChoices.setChoices([{
                value: '',
                label: '-- Pilih Kelas --',
                selected: true
            }], 'value', 'label', true);
            siswaChoices.clearStore();
            siswaChoices.setChoices([{
                value: '',
                label: '-- Pilih Siswa --',
                selected: true
            }], 'value', 'label', true);
            kelasHidden.value = '';

            if (!unitId) {
                console.log('No unitId, skipping fetch');
                return;
            }

            console.log('Fetching kelas from:', `/unit/by-unit/${unitId}`);
            fetch(`/unit/by-unit/${unitId}`)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    console.log('Kelas data loaded:', data);
                    if (!data.length) {
                        kelasChoices.setChoices([{
                            value: '',
                            label: 'Tidak ada kelas',
                            selected: true
                        }], 'value', 'label', true);
                        return;
                    }

                    const options = data.map(kelas => ({
                        value: kelas.id,
                        label: kelas.nama_kelas
                    }));

                    kelasChoices.setChoices(options, 'value', 'label', true);
                    console.log('Kelas choices updated, checking for callback...');

                    // Panggil callback auto-fill kelas jika ada
                    if (window.autoFillKelasCallback) {
                        console.log('Auto-fill kelas callback found, executing...');
                        setTimeout(() => {
                            window.autoFillKelasCallback();
                            delete window.autoFillKelasCallback;
                        }, 100);
                    } else {
                        console.log('No auto-fill kelas callback');
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    kelasChoices.setChoices([{
                        value: '',
                        label: 'Gagal memuat kelas',
                        selected: true
                    }], 'value', 'label', true);
                });
        });

        // Load siswa berdasarkan kelas
        filterKelas.addEventListener('change', function() {
            const kelasId = this.value;
            console.log('Kelas changed, loading siswa for kelasId:', kelasId);
            kelasHidden.value = kelasId;

            // reset select siswa
            siswaChoices.clearStore();
            siswaChoices.setChoices([{
                value: '',
                label: '-- Pilih Siswa --',
                selected: true
            }], 'value', 'label', true);

            if (!kelasId) {
                console.log('No kelasId, skipping fetch');
                return;
            }

            console.log('Fetching siswa from:', `/siswa/by-kelas/${kelasId}`);
            fetch(`/siswa/by-kelas/${kelasId}`)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    console.log('Siswa data loaded:', data);
                    if (!data.length) {
                        siswaChoices.setChoices([{
                            value: '',
                            label: 'Tidak ada siswa',
                            selected: true
                        }], 'value', 'label', true);
                        return;
                    }

                    const options = data.map(siswa => ({
                        value: siswa.id,
                        label: siswa.user && siswa.user.name ? siswa.user.name :
                            'Nama tidak tersedia'
                    }));

                    siswaChoices.setChoices(options, 'value', 'label', true);
                    console.log('Siswa choices updated, checking for callback...');

                    // Panggil callback auto-fill siswa jika ada
                    if (window.autoFillSiswaCallback) {
                        console.log('Auto-fill siswa callback found, executing...');
                        setTimeout(() => {
                            window.autoFillSiswaCallback();
                            delete window.autoFillSiswaCallback;
                        }, 100);
                    } else {
                        console.log('No auto-fill siswa callback');
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    siswaChoices.setChoices([{
                        value: '',
                        label: 'Gagal memuat siswa',
                        selected: true
                    }], 'value', 'label', true);
                });
        });

        // Load detail siswa saat dipilih
        siswaSelect.addEventListener('change', function() {
            const siswaId = this.value;
            penerimaHidden.value = siswaId;

            if (!siswaId) return;

            fetch(`/siswa/siswadetail/${siswaId}`)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    document.getElementById('detail_nama').innerText = data.nama_lengkap || '-';
                    document.getElementById('detail_nisn').innerText = data.nisn || '-';
                    document.getElementById('detail_kelas').innerText = data.kelas || '-';
                    document.getElementById('detail_telp').innerText = data.no_hp || '-';
                    document.getElementById('detail_va').innerText = data.va || '-';

                    // Update foto siswa - gunakan langsung URL dari API
                    const fotoSiswa = document.getElementById('foto_siswa');
                    if (data.foto) {
                        fotoSiswa.src = data.foto;
                    } else {
                        fotoSiswa.src = `{{ asset('assets/images/videa.png') }}`;
                    }

                    // Update saldo awal
                    document.getElementById('saldo_awal').innerText = data.saldo_akhir ?
                        'Rp ' + parseInt(data.saldo_akhir).toLocaleString('id-ID') :
                        'Rp 0';
                })
                .catch(err => console.error('Fetch detail siswa error:', err));
        });

        // Auto-fill dari query parameter (ketika dari halaman detail tabungan)
        const params = getQueryParams();

        if (params.unit_id && params.kelas_id && params.siswa_id) {
            console.log('Auto-filling from query params:', params);

            // Fungsi helper untuk auto-fill kelas setelah data dimuat
            function autoFillKelas() {
                console.log('Step 2: Auto-filling kelas...', params.kelas_id);

                // Set nilai di Choices.js untuk menampilkan pilihan yang dipilih
                setTimeout(() => {
                    filterKelas.value = params.kelas_id;
                    kelasChoices.setChoiceByValue(params.kelas_id);
                    kelasHidden.value = params.kelas_id;
                    console.log('Kelas value set in UI:', params.kelas_id);

                    // Set flag untuk auto-fill siswa setelah data siswa dimuat
                    window.autoFillSiswaId = params.siswa_id;

                    // Trigger change untuk load siswa
                    setTimeout(() => {
                        console.log('Triggering kelas change to load siswa...');
                        const changeEvent = new Event('change', { bubbles: true });
                        filterKelas.dispatchEvent(changeEvent);
                    }, 150);
                }, 150);
            }

            // Fungsi helper untuk auto-fill siswa setelah data dimuat
            function autoFillSiswa() {
                if (window.autoFillSiswaId) {
                    console.log('Step 3: Auto-filling siswa...', window.autoFillSiswaId);

                    // Set nilai di Choices.js untuk menampilkan pilihan yang dipilih
                    setTimeout(() => {
                        siswaSelect.value = window.autoFillSiswaId;
                        siswaChoices.setChoiceByValue(window.autoFillSiswaId);
                        penerimaHidden.value = window.autoFillSiswaId;
                        console.log('Siswa value set in UI:', window.autoFillSiswaId);

                        // Trigger change untuk load detail siswa
                        setTimeout(() => {
                            console.log('Triggering siswa change to load detail...');
                            const changeEvent = new Event('change', { bubbles: true });
                            siswaSelect.dispatchEvent(changeEvent);
                        }, 150);

                        delete window.autoFillSiswaId;
                    }, 150);
                }
            }

            // Tunggu sebentar agar semua Choices instance sudah terinisialisasi
            setTimeout(() => {
                console.log('Step 1: Setting unit...', params.unit_id);

                // Set flag untuk auto-fill kelas setelah data kelas dimuat
                window.autoFillKelasCallback = autoFillKelas;
                window.autoFillSiswaCallback = autoFillSiswa;

                // Set filter unit - set nilai manual dulu baru Choices.js
                filterUnit.value = params.unit_id;
                unitChoices.setChoiceByValue(params.unit_id);

                // Trigger change untuk load kelas
                const changeEvent = new Event('change', { bubbles: true });
                filterUnit.dispatchEvent(changeEvent);
            }, 200);
        }
    </script>
@endpush
