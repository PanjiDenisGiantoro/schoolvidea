@extends('layouts.app')
@section('title', 'Tambah Transaksi Tabungan')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tabungan.css') }}">
@endpush
@section('content')
    @include('partials.page-title', [
        'title' => 'Tambah Transaksi Pembayaran',
        'subTitle' => 'Pembayaran / Keuangan',
    ])


    <div class="row g-4">
        {{-- Summary Cards --}}
        <div class="row mt-4">
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-red shadow-sm h-100 transition-all">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Total Tunggakan</p>
                                <h3 class="fw-bold text-danger mb-0 text-absolute">Rp {{ number_format($summary['total_tunggakan'], 0, ',', '.') }}</h3>
                            </div>
                            <div class="stat-icon bg-danger bg-opacity-10 rounded-3 p-3">
                                <i class="bx bx-time text-danger" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2"><i class="bx bx-time text-danger"></i> Periode Bulan Ini</small>
                        <small class="text-muted d-block mt-2"><i class="bx bx-time text-danger"></i> November 2025</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-green shadow-sm h-100 transition-all">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Total Pembayaran</p>
                                <h3 class="fw-bold text-success mb-0 text-absolute">   Rp {{ number_format($summary['total_pembayaran'], 0, ',', '.') }}</h3>
                            </div>
                            <div class="stat-icon bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="bx bx-wallet-alt text-success" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2"><i class="bx bx-wallet-alt text-success"></i> Bulan Ini</small>
                        <small class="text-muted d-block mt-2"><i class="bx bx-wallet-alt text-success"></i> November 2025</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-yellow shadow-sm h-100 transition-all">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Total Pembayaran</p>
                                <h3 class="fw-bold text-warning mb-0 text-absolute">  Rp {{ number_format($summary['total_nontunai'], 0, ',', '.') }}</h3>
                            </div>
                            <div class="stat-icon bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="bx bx-credit-card text-warning" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2"><i class="bx bx-credit-card text-warning"></i> Non-Tunai</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-blue shadow-sm h-100 transition-all">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Total Pembayaran</p>
                                <h3 class="fw-bold text-info mb-0 text-absolute"> Rp {{ number_format($summary['total_tunai'], 0, ',', '.') }}</h3>
                            </div>
                            <div class="stat-icon bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="bx bx-money text-info" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2"><i class="bx bx-money text-info"></i> Tunai</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 mt-4 p-3">
            <div class="card rounded-4 border-0 shadow-sm">
                <div class="row g-3 p-4 align-items-center">
                    <div class="col-md-4">
                        <label for="filter_unit" class="form-label fw-semibold">Filter Unit</label>
                        <select id="filter_unit" class="form-select rounded-pill shadow-sm">
                            <option value="">-- Pilih Unit --</option>
                            @foreach ($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter_kelas" class="form-label fw-semibold">Filter Kelas</label>
                        <select id="filter_kelas" class="form-select rounded-pill shadow-sm">
                            <option value="">-- Pilih Kelas --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="siswa_id" class="form-label fw-semibold">Pilih Siswa</label>
                        <select id="siswa_id" class="form-select rounded-pill shadow-sm">
                            <option value="">-- Pilih Siswa --</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-4 px-3">
                    <div class="col-md-12 px-4 mb-4" id="nama_tagihan_wrapper" style="display: none;">
                        <label for="nama_tagihan" class="form-label fw-semibold">Pilih Nama Tagihan </label>
                        <select id="nama_tagihan" class="form-select rounded-pill shadow-sm">
                            <option value="">-- Pilih Tagihan --</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Hidden input untuk jenis tagihan (otomatis perbulan) -->
                    <input type="hidden" id="kategori_tagihan" value="perbulan">

                </div>
            </div>
        </div>
        <div class="col-md-12" id="profile-card-wrapper" style="display: none">
            <div class="card rounded-4 overflow-hidden border-0 pr-4 shadow-lg">
                <div class="row align-items-center g-4">

                    {{-- Kolom kiri: Foto + Nama --}}
                    <div class="col-md-3 profile-card text-center">
                        <div class="profile-photo-wrapper mx-auto mb-3">
                            <img src="{{ asset('images/default-user.png') }}" alt="Foto Siswa" id="foto_siswa"
                                 class="profile-photo">
                        </div>
                        <h5 class="fw-bold mb-1 text-white fw-bold" style="font-size: 18px" id="detail_nama">-</h5>
                        <p class="mb-0 opacity-75" id="detail_nisn">-</p>
                    </div>

                    {{-- Kolom kanan: Detail Siswa --}}
                    <div class="col-md-8 p-4">
                        <ul class="list-unstyled small mb-0 px-4" style="font-size: 14px">
                            <li class="mb-2"><i class="ri-building-line text-primary me-2"></i>
                                <strong>Unit :</strong> <span id="detail_unit" class="float-end">-</span>
                            </li>
                            <li class="mb-2"><i class="ri-book-line text-success me-2"></i>
                                <strong>Kelas :</strong> <span id="detail_kelas" class="float-end">-</span>
                            </li>
                            <li class="mb-2"><i class="ri-calendar-line text-warning me-2"></i>
                                <strong>No VA :</strong> <span id="detail_va" class="float-end">-</span>
                            </li>
                            <li class="mb-2"><i class="ri-user-line text-secondary me-2"></i>
                                <strong>Bank :</strong> <span id="detail_bank" class="float-end">-</span>
                            </li>
                            <li class="mb-2"><i class="ri-map-pin-line text-danger me-2"></i>
                                <strong>No Rekening :</strong> <span id="detail_norek" class="float-end">-</span>
                            </li>
                            <li><i class="ri-phone-line text-success me-2"></i>
                                <strong>Telepon :</strong> <span id="detail_telp" class="float-end">-</span>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>

        {{-- Ringkasan Tagihan --}}

        {{-- Daftar Tagihan --}}
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
                {{-- <div class="d-flex align-items-center gap-2">
                    <label for="per_page" class="mb-0">Tampilkan:</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm" style="width: auto;"
                        onchange="changePerPage(this.value)">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                    </select>
                    <span class="text-muted">data per halaman</span>
                </div> --}}
            </div>


            <!-- Header kartu -->
            <div class="custom-card-header px-4 mb-0">
                <h5 class="text-primary fw-bold"><i class="fa fa-list"></i> Daftar Tagihan Per Bulan</h5>
                <button id="btnProsesPembayaran" class="custom-btn-info">
                    <i class="ri-checkbox-multiple-line me-2"></i> Proses Pembayaran
                </button>
            </div>

            <div class="modal fade" id="catatanModal" tabindex="-1" aria-labelledby="catatanModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content rounded-4 border-0 shadow-lg">
                        <div class="modal-header custom-modal-header">
                            <h5 class="modal-title fw-semibold" id="catatanModalLabel">
                                <i class="ri-sticky-note-line"></i> Tambah Catatan
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <form id="formCatatan">
                                <div class="mb-3">
                                    <label for="isiCatatan" class="form-label fw-semibold">Isi Catatan</label>
                                    <textarea class="form-control" id="isiCatatan" rows="4" placeholder="Tulis keterangan tambahan di sini..."></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn custom-btn-purple" onclick="simpanCatatan()">Simpan
                                Catatan</button>

                        </div>
                    </div>
                </div>
            </div>

            <div id="tabelBelumLunas" class="mb-3">
                <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch; max-height: 600px; overflow-y: auto;">
                    <table class="table-bordered table-hover rounded-3 table overflow-hidden text-center align-middle" style="min-width: 1200px;">
                        <thead class="table-primary text-center text-nowrap align-middle">
                        <tr>
                            <th><input class="custom-checkbox " type="checkbox" id="checkAll"></th>
                            <th>Periode Tagihan</th>
                            <th>Tagihan Ke</th>
                            <th>Jenis Tagihan </th>
                            <th>Rincian</th>
                            <th>Potongan</th>
                            <th>Jumlah Tagihan</th>
                            <th>Nominal Pembayaran</th>
                            <th>Total Tunggakan</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody id="list_tagihan">
                        <tr>
                            <td colspan="11" class="text-muted py-4 text-center">
                                <i class="fa fa-info-circle"></i> Silakan pilih siswa & nama tagihan
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                {{-- <div class=" d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $perbulan->firstItem() ?? 0 }} sampai {{ $perbulan->lastItem() ?? 0 }} dari {{ $perbulan->total() }} data
                </div>
                <div>
                    {{ $perbulan->appends(request()->query())->links() }}
                </div>
            </div> --}}
            </div>
            <div class="mb-3" id="tabelSudahLunas">
                <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch; max-height: 600px; overflow-y: auto;">
                    <table class="table-bordered table-hover rounded-3 table overflow-hidden text-center align-middle" style="min-width: 1100px;">
                        <thead class="table-primary text-center text-nowrap align-middle">
                        <tr>
                            <th>Periode Tagihan</th>
                            <th>Tagihan Ke</th>
                            <th>Jenis Tagihan</th>
                            <th>Rincian</th>
                            <th>Potongan</th>
                            <th>Jumlah Tagihan</th>
                            <th>Jumlah Bayar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody id="list_tagihan">
                        <tr>
                            <td colspan="11" class="text-muted py-4 text-center">
                                <i class="fa fa-info-circle"></i> Silakan pilih siswa & nama tagihan
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                {{-- <div class=" d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Menampilkan {{ $perbulan->firstItem() ?? 0 }} sampai {{ $perbulan->lastItem() ?? 0 }} dari {{ $perbulan->total() }} data
                    </div>
                    <div>
                        {{ $perbulan->appends(request()->query())->links() }}
                    </div>
                </div> --}}
            </div>
        </div>

        {{-- Detail Siswa dan Form Tabungan --}}
        <div class="col-12">
            <div class="row g-4">
                <div class="col-md-8">
                    <form action="" method="POST" id="formTagihan">
                        @csrf
                        <input type="hidden" name="checkbox" id="checkbox">
                        <input type="hidden" name="tagihan_siswa_id" id="tagihan_hidden">
                        <input type="hidden" name="kategori_id" id="kategori_hidden">
                        <input type="hidden" name="bulan" id="bulan_hidden">
                        <input type="hidden" name="tahun" id="tahun_hidden">
                        <input type="hidden" name="kelas_id" id="kelas_hidden">
                        <input type="hidden" name="penerima_id" id="penerima_hidden">
                        <input type="hidden" id="catatan_tagihan_id">

                    </form>

                </div>
            </div>
        </div>
        <!-- Modal Catatan -->
    </div>
@endsection

@push('styles')
    <style>
        .animate-btn {
            transition: all 0.3s ease-in-out;
        }

        .animate-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.2);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2596be;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }

        /* Horizontal Scroll Styling */
        .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 0, 0.3) transparent;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: transparent;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.5);
        }

        /* Vertical Scroll Styling */
        .table-responsive {
            position: relative;
        }

        /* Fix Header untuk Horizontal Scroll */
        .table thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #f8f9fa;
        }

        /* Smooth scroll behavior */
        .table-responsive {
            scroll-behavior: smooth;
        }

        /* Optional: Add shadow indicator for horizontal scroll */
        .table-responsive::after {
            content: '';
            position: sticky;
            right: 0;
            top: 0;
            width: 30px;
            height: 100%;
            background: linear-gradient(to left, rgba(0,0,0,0.1), transparent);
            pointer-events: none;
            display: none;
        }

        /* Mobile responsive - adjust heights */
        @media (max-width: 768px) {
            .table-responsive {
                max-height: 500px;
            }

            table {
                min-width: 900px;
            }
        }

        /* Scroll indicator styling */
        .table-responsive.is-scrollable {
            position: relative;
        }

        /* Left scroll shadow indicator */
        .table-responsive.scrolling-left::before {
            content: '';
            position: sticky;
            left: 0;
            top: 0;
            width: 20px;
            height: 100%;
            background: linear-gradient(to right, rgba(0, 0, 0, 0.15), transparent);
            pointer-events: none;
            z-index: 5;
        }

        /* Right scroll shadow indicator */
        .table-responsive.scrolling-right::after {
            content: '';
            position: sticky;
            right: 0;
            top: 0;
            width: 20px;
            height: 100%;
            background: linear-gradient(to left, rgba(0, 0, 0, 0.15), transparent);
            pointer-events: none;
            z-index: 5;
        }

        /* Hint untuk pengguna bahwa table bisa di-scroll */
        .table-responsive.is-scrollable {
            border-bottom: 3px solid #e9ecef;
        }

        .table-responsive.is-scrollable::after {
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            color: #6c757d;
            content: '← Scroll untuk melihat lebih banyak kolom →';
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .table-responsive.is-scrollable:hover::after {
            opacity: 1;
        }
    </style>
@endpush
@push('scripts')
    <script>
        /**
         * Initialize horizontal scroll listeners for tables
         * Menambahkan scroll indicator ketika table bisa di-scroll ke kanan
         */
        document.addEventListener('DOMContentLoaded', function() {
            const tableContainers = document.querySelectorAll('.table-responsive');

            tableContainers.forEach(container => {
                // Check scrollability dan add class jika bisa di-scroll
                function updateScrollIndicator() {
                    if (container.scrollWidth > container.clientWidth) {
                        container.classList.add('is-scrollable');
                    } else {
                        container.classList.remove('is-scrollable');
                    }
                }

                // Initial check
                updateScrollIndicator();

                // Check setiap kali scroll
                container.addEventListener('scroll', function() {
                    if (this.scrollLeft > 0) {
                        this.classList.add('scrolling-left');
                    } else {
                        this.classList.remove('scrolling-left');
                    }

                    if (this.scrollLeft + this.clientWidth < this.scrollWidth) {
                        this.classList.add('scrolling-right');
                    } else {
                        this.classList.remove('scrolling-right');
                    }
                });

                // Re-check ketika window di-resize
                window.addEventListener('resize', updateScrollIndicator);
            });
        });

        // function changePerPage(perPage) {
        //     const url = new URL(window.location.href);
        //     url.searchParams.set('per_page', perPage);
        //     url.searchParams.delete('page'); // Reset ke halaman 1
        //     window.location.href = url.toString();
        // }

        const jumlahInput = document.getElementById('jumlah');
        const jumlahTransaksi = document.getElementById('jumlah_transaksi');
        const filterUnit = document.getElementById('filter_unit');
        const filterKelas = document.getElementById('filter_kelas');
        const siswaSelect = document.getElementById('siswa_id');
        const kelasHidden = document.getElementById('kelas_hidden');
        const penerimaHidden = document.getElementById('penerima_hidden');
        const listTagihanContainer = document.getElementById('list_tagihan_container');
        const listTagihan = document.getElementById('list_tagihan');

        // Load kelas berdasarkan unit
        filterUnit.addEventListener('change', function() {
            var unitId = this.value;

            filterKelas.innerHTML = '<option value="">-- Pilih Kelas --</option>';
            siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';
            document.getElementById('nama_tagihan_wrapper').style.display = 'none';
            document.getElementById('list_tagihan').innerHTML = `
        <tr>
            <td colspan="11" class="text-center text-muted py-4">
                <i class="fa fa-info-circle"></i> Silakan pilih unit dan kelas
            </td>
        </tr>`;
            document.getElementById('nama_tagihan').innerHTML =
                '<option value="">-- Pilih Tagihan --</option>';

            if (!unitId) return;

            fetch(`/siswa/get-kelas/${unitId}`)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    console.log('Data kelas:', data);
                    if (data.length === 0) {
                        const option = document.createElement('option');
                        option.text = 'Tidak ada kelas';
                        option.value = '';
                        filterKelas.appendChild(option);
                        return;
                    }
                    data.forEach(kelas => {
                        const option = document.createElement('option');
                        option.value = kelas.id;
                        option.text = kelas.nama_kelas;
                        filterKelas.appendChild(option);
                    });
                })
                .catch(err => console.error('Fetch error:', err));
        });

        // Load siswa berdasarkan kelas
        filterKelas.addEventListener('change', function() {
            var kelasId = this.value;
            kelasHidden.value = kelasId;

            siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';
            document.getElementById('nama_tagihan_wrapper').style.display = 'none';
            document.getElementById('list_tagihan').innerHTML = `
        <tr>
            <td colspan="11" class="text-center text-muted py-4">
                <i class="fa fa-info-circle"></i> Silakan pilih siswa
            </td>
        </tr>`;
            document.getElementById('nama_tagihan').innerHTML =
                '<option value="">-- Pilih Tagihan --</option>';
            if (!kelasId) return;
            fetch(`/siswa/by-kelas/${kelasId}`)
                .then(res => {
                    console.log(res)
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    console.log('Data siswa:', data); // debug
                    if (data.length === 0) {
                        const option = document.createElement('option');
                        option.text = 'Tidak ada siswa';
                        option.value = '';
                        siswaSelect.appendChild(option);
                        return;
                    }
                    data.forEach(siswa => {
                        const option = document.createElement('option');
                        option.value = siswa.id;
                        // pastikan user ada
                        option.text = siswa.user ? siswa.user.name : 'Nama tidak tersedia';
                        siswaSelect.appendChild(option);
                    });
                })
                .catch(err => console.error('Fetch error:', err));
        });

        // Load detail siswa saat dipilih
        siswaSelect.addEventListener('change', function() {
            const siswaId = this.value;
            penerimaHidden.value = siswaId;

            // Reset dropdown & table
            document.getElementById('nama_tagihan').innerHTML = '<option value="">-- Pilih Tagihan --</option>';
            document.getElementById('nama_tagihan_wrapper').style.display = 'none';
            document.getElementById('list_tagihan').innerHTML = `
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">
                        <i class="fa fa-info-circle"></i> Silakan pilih nama tagihan
                    </td>
                </tr>`;

            if (!siswaId) return;

            // Load detail siswa
            fetch(`/siswa/siswadetail/${siswaId}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('detail_nama').innerText = data.nama_lengkap;
                    document.getElementById('detail_nisn').innerText = data.nisn;
                    document.getElementById('detail_unit').innerText = data.unit;
                    document.getElementById('detail_kelas').innerText = data.kelas;
                    document.getElementById('detail_va').innerText = data.va;
                    document.getElementById('detail_bank').innerText = data.bank;
                    document.getElementById('detail_norek').innerText = data.norek;
                    document.getElementById('detail_telp').innerText = data.no_hp;
                    if (data.foto) {
                        document.querySelector('img[alt="Foto Siswa"]').src = `${data.foto}`;
                    }
                })
                .catch(err => console.error(err));

            // Load daftar tagihan bulanan otomatis
            fetch(`/tagihan/daftarTagihan/${siswaId}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.detail || !data.detail.length) {
                        document.getElementById('nama_tagihan_wrapper').style.display = 'none';
                        return;
                    }

                    // Isi dropdown nama tagihan
                    const tagihanSelect = document.getElementById('nama_tagihan');
                    tagihanSelect.innerHTML = `
            <option value="">-- Pilih Tagihan --</option>
            <option value="all">Semua Tagihan</option>
        `;
                    data.detail.forEach((tagihan) => {
                        const opt = document.createElement('option');
                        opt.value = tagihan.id;
                        const kategoriNama = tagihan.kategori?.[0]?.nama_kategori ?? 'Tanpa Kategori';
                        // Format tanggal
                        const date = new Date(tagihan.created_at);
                        const formattedDate = date.toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric'
                        });
                        opt.text = `${kategoriNama} - Rp ${parseInt(tagihan.nominal).toLocaleString('id-ID')} - ${formattedDate}`;
                        tagihanSelect.appendChild(opt);
                    });

                    // Tampilkan wrapper
                    document.getElementById('nama_tagihan_wrapper').style.display = 'block';
                    window.tagihanData = data.detail;
                })
                .catch(err => console.error('Fetch tagihan error:', err));
        });
        document.getElementById('nama_tagihan').addEventListener('change', function() {
            const tagihanId = this.value;
            const siswaId = siswaSelect.value;

            const card = document.getElementById("profile-card-wrapper");

            if (this.value !== "") {
                card.style.display = "block"; // tampil
            } else {
                card.style.display = "none"; // sembunyi
            }

            if (!tagihanId) return;

            // Jika "Semua Tagihan" dipilih, gunakan endpoint perbulanAll
            const endpoint = tagihanId === 'all'
                ? `/tagihan/perbulanAll/${siswaId}`
                : `/tagihan/perbulan/${siswaId}/${tagihanId}`;

            fetch(endpoint)
                .then(res => res.json())
                .then(data => {
                    if (!data.belum_lunas.length && !data.sudah_lunas.length) {
                        listTagihan.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="fa fa-exclamation-circle text-warning"></i> Tidak ada data tagihan
                    </td>
                </tr>`;
                        return;
                    }

                    // Render Belum Lunas
                    const tabelBelum = document.querySelector('#tabelBelumLunas tbody');

                    // Simpan data tagihan ke window untuk keperluan pembayaran multiple
                    if (!window.tagihanDataMap) {
                        window.tagihanDataMap = new Map();
                    }

                    data.belum_lunas.forEach(tagihan => {
                        window.tagihanDataMap.set(tagihan.id, {
                            bulan: tagihan.periode,
                            tahun: tagihan.tahun || new Date().getFullYear(),
                            kategori_id: tagihan.kategori_id || 1,
                            nominal: parseInt(tagihan.nominal_pembayaran)
                        });
                    });

                    tabelBelum.innerHTML = data.belum_lunas.map(tagihan => `
            <tr>
                <td class="text-center"><input type="checkbox" value="${tagihan.id}"></td>
                <td class="text-center">${tagihan.periode}</td>
                <td class="text-center">${tagihan.no}</td>
                <td class="text-center">${tagihan.tagihan_kelas}</td>
                <td class="text-center">Rp ${parseInt(tagihan.rincian_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-center text-danger">Rp ${parseInt(tagihan.jumlah_potongan).toLocaleString('id-ID')}</td>
                <td class="text-center fw-bold">Rp ${parseInt(tagihan.jumlah_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-center">Rp ${parseInt(tagihan.nominal_pembayaran).toLocaleString('id-ID')}</td>
                <td class="text-center text-success">Rp ${parseInt(tagihan.jumlah_dibayar).toLocaleString('id-ID')}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-warning btn-sm rounded-pill"
                                onclick="tambahCatatan(${tagihan.id})">
                                <i class="fa fa-sticky-note"></i> Catatan
                            </button></td>
                <td class="text-center">

                    <button type="button" class="btn btn-success btn-sm rounded-pill"
                        onclick="bayarTagihan(${tagihan.id}, '${tagihan.periode}', '${tagihan.tahun || new Date().getFullYear()}', ${tagihan.jumlah_dibayar}, ${tagihan.kategori_id || 1})">
                        <i class="fa fa-credit-card"></i> Bayar
                    </button>
                </td>
            </tr>
        `).join('');

                    // Render Sudah Lunas
                    const tabelLunas = document.querySelector('#tabelSudahLunas tbody');
                    tabelLunas.innerHTML = data.sudah_lunas.map(tagihan => `
            <tr>
                <td class="text-center">${tagihan.periode}</td>
                <td class="text-center">${tagihan.no}</td>
                <td class="text-center">${tagihan.tagihan_kelas}</td>
                <td class="text-end">Rp ${parseInt(tagihan.rincian_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-end text-danger">Rp ${parseInt(tagihan.jumlah_potongan).toLocaleString('id-ID')}</td>
                <td class="text-end fw-bold">Rp ${parseInt(tagihan.jumlah_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-end text-success">Rp ${parseInt(tagihan.jumlah_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-center"><span class="badge bg-success">LUNAS</span></td>
                                <td>
                                    <a href="{{ url('keuangan-transaksi/show') }}/${tagihan.keuangan_transaksi_id}" class="btn btn-success btn-sm rounded-pill" title="Lihat Detail">
                                        <i class='ri-eye-line' style="font-size: 16px"></i> Detail
                                    </a>
                                    <a href="{{ url('keuangan-transaksi/cetak-struk') }}/${tagihan.keuangan_transaksi_id}" class="btn btn-warning btn-sm rounded-pill" title="Cetak Struk" target="_blank">
                                        <i class="ri-printer-line" style="font-size: 16px"></i> Struk
                                    </a>
                                </td>
            </tr>
        `).join('');
                });

        });
    </script>
    <script>
        function bayarTagihan(tagihanId, bulan, tahun, nominal, kategoriId) {
            // Handle fallback values for bulan and tahun
            const displayBulan = bulan && bulan !== 'N/A' ? bulan : 'Tagihan';
            const displayTahun = tahun && tahun !== 'undefined' ? tahun : new Date().getFullYear();

            // Ensure nominal is a valid number
            const validNominal = parseInt(nominal) || 0;

            if (validNominal <= 0) {
                Swal.fire("Error!", "Nominal tagihan tidak valid.", "error");
                return;
            }

            Swal.fire({
                title: `Bayar Tagihan ${displayBulan} `,
                text: `Total: Rp ${validNominal.toLocaleString('id-ID')}`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Bayar Full",
                cancelButtonText: "Batal",
                showDenyButton: true,
                denyButtonText: "Bayar Sebagian",
                confirmButtonColor: "#3085d6",
                denyButtonColor: "#f59e0b",
                cancelButtonColor: "#d33"
            }).then((result) => {
                if (result.isConfirmed) {
                    // 🔹 Bayar Full
                    kirimPembayaran(tagihanId, bulan, tahun, validNominal, kategoriId, validNominal);
                } else if (result.isDenied) {
                    // 🔹 Input Nominal untuk Bayar Sebagian dengan format digit
                    Swal.fire({
                        title: "Masukan Nominal Bayar",
                        html: `
                            <div class="mb-3">
                                <label class="form-label">Maksimal: Rp ${validNominal.toLocaleString('id-ID')}</label>
                                <input type="text" id="swal-input-nominal" class="form-control"
                                    placeholder="Contoh: 500.000" style="font-size: 1.1rem;">
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: "Bayar",
                        cancelButtonText: "Batal",
                        didOpen: () => {
                            const input = document.getElementById('swal-input-nominal');

                            // Format number with thousands separator
                            function formatNumber(num) {
                                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            }

                            // Remove all non-digit characters
                            function unformatNumber(str) {
                                return str.replace(/\D/g, '');
                            }

                            input.addEventListener('input', function(e) {
                                let rawValue = unformatNumber(this.value);
                                if (rawValue) {
                                    this.value = formatNumber(rawValue);
                                }
                            });

                            input.focus();
                        },
                        preConfirm: () => {
                            const input = document.getElementById('swal-input-nominal');
                            const rawValue = input.value.replace(/\D/g, '');
                            const val = parseInt(rawValue);

                            if (!rawValue || val <= 0) {
                                Swal.showValidationMessage("Nominal harus lebih dari 0");
                                return false;
                            }
                            if (val > validNominal) {
                                Swal.showValidationMessage(
                                    "Nominal tidak boleh lebih besar dari total tagihan!");
                                return false;
                            }
                            return val;
                        }
                    }).then((res) => {
                        if (res.isConfirmed) {
                            kirimPembayaran(tagihanId, bulan, tahun, validNominal, kategoriId, res.value);
                        }
                    });
                }
            });
        }

        function kirimPembayaran(tagihanId, bulan, tahun, nominal, kategoriId, jumlahBayar) {
            fetch('/pembayaran/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    tagihan_siswa_id: tagihanId,
                    bulan: bulan,
                    tahun: tahun,
                    nominal: nominal, // total tagihan
                    jumlah_bayar: jumlahBayar, // jumlah yang dibayar (bisa full / sebagian)
                    kategori_id: kategoriId,
                    metode: 'TUNAI',
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status == 1) {
                        Swal.fire({
                            title: "Berhasil!",
                            text: "Pembayaran berhasil dilakukan.",
                            icon: "success",
                            confirmButtonText: "OK"
                        }).then(() => {
                            // reload list tagihan supaya status berubah lunas / sebagian
                            document.getElementById('nama_tagihan').dispatchEvent(new Event('change'));
                        });
                    } else {
                        Swal.fire("Gagal!", data.message, "error");
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    Swal.fire("Error!", "Terjadi kesalahan saat membayar.", "error");
                });
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnBelumLunas = document.getElementById('btnBelumLunas');
            const btnSudahLunas = document.getElementById('btnSudahLunas');
            const tabelBelumLunas = document.getElementById('tabelBelumLunas');
            const tabelSudahLunas = document.getElementById('tabelSudahLunas');
            const btnProsesPembayaran = document.getElementById('btnProsesPembayaran');

            btnBelumLunas.addEventListener('click', function() {
                btnProsesPembayaran.style.display = 'inline-flex'; // tampil lagi
            });

            btnSudahLunas.addEventListener('click', function() {
                btnProsesPembayaran.style.display = 'none'; // sembunyikan
            });
            btnBelumLunas.addEventListener('click', function() {
                btnBelumLunas.classList.add('custom-active-btn');
                btnSudahLunas.classList.remove('custom-active-btn');
                tabelBelumLunas.style.display = 'block';
                tabelSudahLunas.style.display = 'none';
            });

            btnSudahLunas.addEventListener('click', function() {
                btnSudahLunas.classList.add('custom-active-btn');
                btnBelumLunas.classList.remove('custom-active-btn');
                tabelSudahLunas.style.display = 'block';
                tabelBelumLunas.style.display = 'none';
            });
        });

        function tambahCatatan(tagihanId) {
            // 1️⃣ Simpan ID ke hidden input
            document.getElementById('catatan_tagihan_id').value = tagihanId;

            // 2️⃣ Ambil data tagihan dari window.tagihanData (hasil fetch sebelumnya)
            const tagihan = window.tagihanData?.find(t => t.id === tagihanId);

            // 3️⃣ Isi catatan kalau ada, kosong kalau tidak
            document.getElementById('isiCatatan').value = tagihan?.catatan || '';

            // 4️⃣ Tampilkan modal
            const modal = new bootstrap.Modal(document.getElementById('catatanModal'));
            modal.show();
        }


        function simpanCatatan() {
            const tagihanId = document.getElementById('catatan_tagihan_id').value;
            const isiCatatan = document.getElementById('isiCatatan').value.trim();

            if (!isiCatatan) {
                Swal.fire("Peringatan!", "Catatan tidak boleh kosong.", "warning");
                return;
            }

            fetch('/pembayaran/catatan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    tagihan_id: tagihanId,
                    catatan: isiCatatan
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 1) {
                        Swal.fire("Berhasil!", "Catatan berhasil disimpan.", "success");
                        const modal = bootstrap.Modal.getInstance(document.getElementById('catatanModal'));
                        modal.hide();

                        // reload tabel supaya catatan muncul
                        document.getElementById('nama_tagihan').dispatchEvent(new Event('change'));
                    } else {
                        Swal.fire("Gagal!", data.message || "Tidak dapat menyimpan catatan.", "error");
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire("Error!", "Terjadi kesalahan saat menyimpan catatan.", "error");

                });
        }
    </script>
    <script>
        function bukaCatatanModal(catatan) {
            const textarea = document.getElementById('isiCatatan');
            textarea.value = catatan || ''; // jika null/undefined → kosongkan

            const modal = new bootstrap.Modal(document.getElementById('catatanModal'));
            modal.show();
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('checkAll');
            const btnProsesPembayaran = document.getElementById('btnProsesPembayaran');
            const hiddenInput = document.getElementById('checkbox');

            // 🔹 Fitur "Centang Semua"
            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll(
                        '#tabelBelumLunas tbody input[type="checkbox"]');
                    checkboxes.forEach(cb => {
                        cb.checked = checkAll.checked;
                    });
                });
            }

            // 🔹 Saat klik "Proses Pembayaran"
            if (btnProsesPembayaran) {
                btnProsesPembayaran.addEventListener('click', function() {
                    const checkboxes = Array.from(
                        document.querySelectorAll(
                            '#tabelBelumLunas tbody input[type="checkbox"]:checked')
                    );

                    if (checkboxes.length === 0) {
                        Swal.fire("Peringatan!", "Silakan pilih minimal satu tagihan terlebih dahulu.",
                            "warning");
                        return;
                    }

                    // Kumpulkan data tagihan yang dipilih
                    const selectedTagihan = checkboxes.map(cb => {
                        const row = cb.closest('tr');
                        const periode = row.querySelector('td:nth-child(3)').textContent.trim();
                        const nominalText = row.querySelector('td:nth-child(9)').textContent.trim();
                        const nominal = parseInt(nominalText.replace(/[^\d]/g, ''));

                        return {
                            id: cb.value,
                            periode: periode,
                            nominal: nominal
                        };
                    });

                    const totalNominal = selectedTagihan.reduce((sum, t) => sum + t.nominal, 0);

                    // Tampilkan konfirmasi
                    Swal.fire({
                        title: "Konfirmasi Pembayaran",
                        html: `
                            <p>Anda akan memproses ${selectedTagihan.length} tagihan</p>
                            <p><strong>Total: Rp ${totalNominal.toLocaleString('id-ID')}</strong></p>
                        `,
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonText: "Bayar Semua",
                        cancelButtonText: "Batal",
                        confirmButtonColor: "#3085d6"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Proses pembayaran multiple
                            prosesMultiplePembayaran(selectedTagihan);
                        }
                    });
                });
            }
        });

        // Fungsi untuk proses pembayaran multiple dengan detail tracking
        function prosesMultiplePembayaran(selectedTagihan) {
            const totalTagihan = selectedTagihan.length;
            const totalNominal = selectedTagihan.reduce((sum, t) => sum + t.nominal, 0);

            // Input nominal pembayaran (bisa full atau sebagian)
            Swal.fire({
                title: 'Masukan Nominal Pembayaran',
                html: `
                    <div class="mb-3">
                        <p class="text-muted mb-3">Total ${totalTagihan} tagihan</p>
                        <label class="form-label">Maksimal: Rp ${totalNominal.toLocaleString('id-ID')}</label>
                        <input type="text" id="swal-input-nominal-pembayaran" class="form-control"
                            placeholder="Contoh: 1.500.000" style="font-size: 1.1rem;" value="${totalNominal.toLocaleString('id-ID')}">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: "Proses Pembayaran",
                cancelButtonText: "Batal",
                didOpen: () => {
                    const input = document.getElementById('swal-input-nominal-pembayaran');

                    // Format number with thousands separator
                    function formatNumber(num) {
                        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    }

                    // Remove all non-digit characters
                    function unformatNumber(str) {
                        return str.replace(/\D/g, '');
                    }

                    input.addEventListener('input', function(e) {
                        let rawValue = unformatNumber(this.value);
                        if (rawValue) {
                            this.value = formatNumber(rawValue);
                        }
                    });

                    input.focus();
                    input.select();
                },
                preConfirm: () => {
                    const input = document.getElementById('swal-input-nominal-pembayaran');
                    const rawValue = input.value.replace(/\D/g, '');
                    const val = parseInt(rawValue);

                    if (!rawValue || val <= 0) {
                        Swal.showValidationMessage("Nominal harus lebih dari 0");
                        return false;
                    }
                    if (val > totalNominal) {
                        Swal.showValidationMessage(
                            `Nominal tidak boleh lebih besar dari total tagihan (Rp ${totalNominal.toLocaleString('id-ID')})`);
                        return false;
                    }
                    return val;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const jumlahBayar = result.value;

                    // Kumpulkan ID tagihan
                    const tagihanIds = selectedTagihan.map(t => parseInt(t.id));

                    // Tampilkan loading
                    Swal.fire({
                        title: 'Memproses Pembayaran...',
                        html: `<p>Sedang memproses ${totalTagihan} tagihan sebesar Rp ${jumlahBayar.toLocaleString('id-ID')}</p>`,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Call endpoint baru: proses-multiple-with-detail
                    fetch('/pembayaran/proses-multiple-with-detail', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            tagihan_siswa_ids: tagihanIds,
                            jumlah_bayar: jumlahBayar,
                            metode: 'TUNAI',
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status) {
                                // Sukses
                                Swal.fire({
                                    title: "Berhasil!",
                                    html: `
                                        <p>Pembayaran gabungan berhasil diproses</p>
                                        <p class="mt-3"><strong>Head Tagihan:</strong></p>
                                        <p class="font-monospace bg-light p-2 rounded">${data.data.head_tagihan}</p>
                                        <p class="mt-3">
                                            <strong>Summary:</strong><br>
                                            Jumlah Tagihan: ${data.data.summary.jumlah_tagihan}<br>
                                            Total Bayar: Rp ${data.data.summary.jumlah_bayar.toLocaleString('id-ID')}
                                        </p>
                                    `,
                                    icon: "success",
                                    confirmButtonText: "OK",
                                    allowOutsideClick: false
                                }).then(() => {
                                    // Reload list tagihan
                                    document.getElementById('nama_tagihan').dispatchEvent(new Event('change'));
                                });
                            } else {
                                // Error
                                Swal.fire("Gagal!", data.message || "Pembayaran gagal diproses", "error");
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            Swal.fire("Error!", "Terjadi kesalahan saat memproses pembayaran: " + err.message, "error");
                        });
                }
            });
        }
    </script>

@endpush
