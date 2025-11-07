@extends('layouts.app')
@section('title', 'Tambah Transaksi Tabungan')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@endpush
@section('content')
    @include('partials.page-title', [
        'title' => 'Tambah Transaksi Pembayaran',
        'subTitle' => 'Pembayaran / Keuangan',
    ])
    @push('styles')
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
        </style>
    @endpush

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card rounded-4 border-0 p-4 shadow-sm">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label for="filter_unit" class="form-label fw-semibold">Filter Unit</label>
                        <select id="filter_unit" class="form-select rounded-pill shadow-sm">
                            <option value="">-- Pilih Unit --</option>
                            @foreach ($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama }}</option>
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

                <div class="row g-3 mt-2">
                    <div class="col-md-12" id="nama_tagihan_wrapper" style="display: none;">
                        <label for="nama_tagihan" class="form-label fw-semibold">Pilih Nama Tagihan (Bulanan)</label>
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
        <div class="col-md-4">
            <div class="card rounded-4 border-0 p-4 shadow-sm">
                <div class="mb-3 text-center">
                    <img src="{{ asset('images/default-user.png') }}" alt="Foto Siswa"
                        class="img-fluid rounded-circle border shadow-sm" width="120">
                </div>

        {{-- Ringkasan Tagihan --}}

        {{-- Daftar Tagihan --}}
        <div class="card rounded-4 mt-3 border-0 shadow-sm">
            <!-- Header tombol toggle -->
            <div class="custom-toggle-header">
                <button id="btnBelumLunas" class="custom-btn-outline-primary custom-active-btn">
                    <i class="ri-money-dollar-circle-line"></i> Belum Lunas
                </button>
                <button id="btnSudahLunas" class="custom-btn-outline-primary">
                    <i class="ri-file-list-3-line"></i> Sudah Lunas
                </button>
            </div>

            <!-- Header kartu -->
            <div class="custom-card-header">
                <span><i class="fa fa-list"></i> Daftar Tagihan Per Bulan</span>
                <button id="btnProsesPembayaran" class="custom-btn-info">
                    <i class="ri-checkbox-multiple-line"></i> Proses Pembayaran
                </button>
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
                                    <label for="isiCatatan" class="form-label fw-semibold">Isi Catatan</label>
                                    <textarea class="form-control" id="isiCatatan" rows="4" placeholder="Tulis keterangan tambahan di sini..."></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn custom-btn-purple" onclick="simpanCatatan()">Simpan Catatan</button>

                        </div>
                    </div>
                </div>
            </div>

            <div id="tabelBelumLunas" class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-hover table-striped mb-0 table align-middle">
                        <thead class="table-light text-center align-middle">
                            <tr>
                                <th><input class="custom-checkbox" type="checkbox" id="checkAll"></th>
                                <th>#</th>
                                <th>Periode Tagihan</th>
                                <th>Tagihan Kelas</th>
                                <th>Rincian Tagihan</th>
                                <th>Jumlah Potongan</th>
                                <th>Jumlah Tagihan</th>
                                <th>Total Dibayar</th>
                                <th>Total Tunggakan</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list_tagihan">
                            <tr>
                                <td colspan="11" class="text-muted py-4 text-center">
                                    <i class="fa fa-info-circle"></i> Silakan pilih kelas & siswa
                                </td>
                            </tr>
                        </tbody>
                        <tfoot id="foot_belum_lunas" class="fw-bold table-light text-center" style="font-size: 14px">
                            <tr>
                                <td colspan="11"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-body p-0" id="tabelSudahLunas">
                <div class="table-responsive">
                    <table class="table-hover table-striped mb-0 table align-middle">
                        <thead class="table-light items-center text-center">
                            <tr>
                                <th>#</th>
                                <th>Periode Tagihan</th>
                                <th>Tagihan Kelas</th>
                                <th>Rincian Tagihan</th>
                                <th>Jml.Potongan</th>
                                <th>Jml.Tagihan</th>
                                <th>Jml.Bayar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list_tagihan">
                            <tr>
                                <td colspan="9" class="text-muted py-4 text-center">
                                    <i class="fa fa-info-circle"></i> Silakan pilih kelas & siswa
                                </td>
                            </tr>
                        </tbody>
                        <tfoot id="foot_sudah_lunas" class="fw-bold table-light text-center" style="font-size: 14px">
                            <tr>
                                <td colspan="9"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Detail Siswa dan Form Tabungan --}}
        <div class="col-12">
            <div class="row g-4">
                {{-- Detail Siswa --}}
                {{-- Form Transaksi --}}
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
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
    </style>
@endpush
@push('scripts')
    <script>
        const jumlahInput = document.getElementById('jumlah');
        const jumlahTransaksi = document.getElementById('jumlah_transaksi');
        const filterUnit = document.getElementById('filter_unit');
        const filterKelas = document.getElementById('filter_kelas');
        const siswaSelect = document.getElementById('siswa_id');
        const kelasHidden = document.getElementById('kelas_hidden');
        const penerimaHidden = document.getElementById('penerima_hidden');

        const listTagihanContainer = document.getElementById('list_tagihan_container');
        const listTagihan = document.getElementById('list_tagihan');
        const footBelumLunas = document.getElementById('foot_belum_lunas');
        const footSudahLunas = document.getElementById('foot_sudah_lunas');

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
            listTagihan.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">
                        <i class="fa fa-exclamation-circle text-warning"></i> Tidak ada data tagihan
                    </td>
                </tr>`;
            footBelumLunas.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">


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
                    document.getElementById('detail_nama').innerText = ': ' + (data.nama_lengkap || '-');
                    document.getElementById('detail_nisn').innerText = ': ' + (data.nisn || '-');
                    document.getElementById('detail_unit').innerText = ': ' + (data.unit || '-');
                    document.getElementById('detail_kelas').innerText = ': ' + (data.kelas || '-');
                    document.getElementById('detail_VA').innerText = ': ' + (data.VA || '-');
                    document.getElementById('detail_bank').innerText = ': ' + (data.bank || '-');
                    document.getElementById('detail_rfid_no').innerText = ': ' + (data.rfid_no || '-');
                    document.getElementById('detail_norek').innerText = ': ' + (data.norek || '-');
                    document.getElementById('detail_telp').innerText = ': ' + data.no_hp || '-';


                    if (data.foto) {
                        document.querySelector('img[alt="Foto Siswa"]').src = `${data.foto}`;
                    }
                })
                .catch(err => console.error(err));
            fetch(`/tagihan/daftarTagihan/${siswaId}`)
                .then(res => res.json())
                .then(data => {
                    const tagihanSelect = document.getElementById('nama_tagihan');
                    const wrapper = document.getElementById('nama_tagihan_wrapper');
                    const listTagihan = document.getElementById('list_tagihan');
                    // listTagihan.innerHTML = '';


                    if (!data.detail || !data.detail.length) {
                        listTagihan.innerHTML = `
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="fa fa-exclamation-circle text-warning"></i> Tidak ada tagihan
                        </td>
                    </tr>`;
                        wrapper.style.display = 'block';
                        return;
                    }

                    // Isi dropdown nama tagihan
                    const tagihanSelect = document.getElementById('nama_tagihan');
                    tagihanSelect.innerHTML = '<option value="">-- Pilih Tagihan --</option>';
                    data.detail.forEach(tagihan => {
                        const opt = document.createElement('option');
                        opt.value = tagihan.id;
                        const kategoriNama = tagihan.kategori?.[0]?.nama_kategori ?? 'Tanpa Kategori';
                        opt.text =
                            `${kategoriNama} - Rp ${parseInt(tagihan.nominal).toLocaleString('id-ID')}`;
                        tagihanSelect.appendChild(opt);
                    });
                    wrapper.style.display = 'block';
                    window.tagihanData = data.detail;
                }).catch(err => console.error('Fetch tagihan error: ', err))
        });
        document.getElementById('nama_tagihan').addEventListener('change', function() {
            const tagihanId = this.value;
            const siswaId = siswaSelect.value;

            if (!tagihanId) return;

            fetch(`/tagihan/perbulan/${siswaId}/${tagihanId}`)
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
                    if (!data.belum_lunas.length) {
                        listTagihan.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">
                        <i class="fa fa-exclamation-circle text-warning"></i> Tidak ada data tagihan
                    </td>
                </tr>`;
                        footBelumLunas.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">
                    </td>
                </tr>`;
                    } else {
                        // Render Belum Lunas
                        const tabelBelum = document.querySelector('#tabelBelumLunas tbody');
                        tabelBelum.innerHTML = data.belum_lunas.map(tagihan => `

            <tr>
                <td class="text-center"><input type="checkbox" value="${tagihan.id}"></td>
                <td class="text-center">${tagihan.no}</td>
                <td class="text-center">${tagihan.periode}</td>
                <td class="text-center">${tagihan.tagihan_kelas}</td>
                <td class="text-end">Rp ${parseInt(tagihan.rincian_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-end text-danger">Rp ${parseInt(tagihan.jumlah_potongan).toLocaleString('id-ID')}</td>
                <td class="text-end fw-bold">Rp ${parseInt(tagihan.jumlah_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-end text-success">Rp ${parseInt(tagihan.jumlah_dibayar).toLocaleString('id-ID')}</td>
                <td class="text-end">Rp ${parseInt(tagihan.nominal_pembayaran).toLocaleString('id-ID')}</td>
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

                        let totalRincian = 0;
                        let totalPotongan = 0;
                        let totalTagihan = 0;
                        let totalDibayar = 0;
                        let totalTunggakan = 0;
                        let totalPeriode = new Set();
                        let tagihanKelas = '';

                        data.belum_lunas.forEach(item => {
                            totalPeriode.add(item.periode);
                            totalRincian += parseInt(item.rincian_tagihan || 0);
                            totalPotongan += parseInt(item.jumlah_potongan || 0);
                            totalTagihan += parseInt(item.jumlah_tagihan || 0);
                            totalDibayar += parseInt(item.nominal_pembayaran || 0);
                            totalTunggakan += parseInt(item.jumlah_dibayar || 0);
                            tagihanKelas = item.tagihan_kelas;
                        });

                        const tabelBelumFoot = document.querySelector('#tabelBelumLunas tfoot');
                        tabelBelumFoot.innerHTML = `
    <tr>
        <td class="text-center"><input type="checkbox" disabled></td>
        <td class="text-center fw-bold" style="font-size: 14px">Total</td>
        <td class="text-center" style="font-size: 14px">${[totalPeriode.size].join(', ')}</td>
        <td class="text-center" style="font-size: 14px">${tagihanKelas}</td>
        <td class="text-center" style="font-size: 14px">Rp ${totalRincian.toLocaleString('id-ID')}</td>
        <td class="text-center text-danger" style="font-size: 14px">Rp ${totalPotongan.toLocaleString('id-ID')}</td>
        <td class="text-center fw-bold" style="font-size: 14px">Rp ${totalTagihan.toLocaleString('id-ID')}</td>
        <td class="text-center" style="font-size: 14px">Rp ${totalDibayar.toLocaleString('id-ID')}</td>
        <td class="text-center text-success" style="font-size: 14px">Rp ${totalTunggakan.toLocaleString('id-ID')}</td>
        <td class="text-center" colspan="2">—</td>
    </tr>
`;
                    }
                    if (!data.sudah_lunas.length) {
                        listTagihan.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="fa fa-exclamation-circle text-warning"></i> Tidak ada data tagihan
                    </td>
                </tr>`;
                        footSudahLunas.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                    </td>
                </tr>`;
                    } else {
                        // Render Sudah Lunas
                        const tabelLunas = document.querySelector('#tabelSudahLunas tbody');
                        tabelLunas
                            .innerHTML = data.sudah_lunas.map(tagihan => `
            <tr>
                <td class="text-center">${tagihan.no}</td>
                <td class="text-center">${tagihan.periode}</td>
                <td class="text-center">${tagihan.tagihan_kelas}</td>
                <td class="text-end">Rp ${parseInt(tagihan.rincian_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-end text-danger">Rp ${parseInt(tagihan.jumlah_potongan).toLocaleString('id-ID')}</td>
                <td class="text-end fw-bold">Rp ${parseInt(tagihan.jumlah_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-end text-success">Rp ${parseInt(tagihan.jumlah_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-center"><span class="badge bg-success">LUNAS</span></td>
            </tr>
        `).join('');
                        let tagihKelas = '';
                        let rincianTagihan = 0;
                        let jumlahPotongan = 0;
                        let jumlahTagihan = 0;
                        let jumlahBayar = 0;
                        let ttlPeriode = new Set();


                        data.sudah_lunas.forEach(item => {
                            ttlPeriode.add(item.periode);
                            rincianTagihan += parseInt(item.rincian_tagihan || 0);
                            jumlahPotongan += parseInt(item.jumlah_potongan || 0);
                            jumlahTagihan += parseInt(item.jumlah_tagihan || 0);
                            jumlahBayar += parseInt(item.jumlah_tagihan || 0);
                            tagihKelas = item.tagihan_kelas;
                        });

                        const tabelSudahLunasFoot = document.querySelector('#tabelSudahLunas tfoot');
                        tabelSudahLunasFoot.innerHTML = `
    <tr>
        <td class="text-center fw-bold" style="font-size: 14px">Total</td>
        <td class="text-center" style="font-size: 14px">${[ttlPeriode.size].join(', ')}</td>
        <td class="text-center" style="font-size: 14px">${tagihKelas}</td>
        <td class="text-center" style="font-size: 14px">Rp ${rincianTagihan.toLocaleString('id-ID')}</td>
        <td class="text-center text-danger" style="font-size: 14px">Rp ${jumlahPotongan.toLocaleString('id-ID')}</td>
        <td class="text-center fw-bold" style="font-size: 14px">Rp ${jumlahTagihan.toLocaleString('id-ID')}</td>
        <td class="text-center text-success" style="font-size: 14px">Rp ${jumlahBayar.toLocaleString('id-ID')}</td>
        <td class="text-center fw-bold" colspan="2" style="font-size: 14px">-</td>
    </tr>
`;
                    }

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
                title: `Bayar Tagihan ${displayBulan} ${displayTahun}`,
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
                    // 🔹 Input Nominal untuk Bayar Sebagian
                    Swal.fire({
                        title: "Masukan Nominal Bayar",
                        input: "number",
                        inputAttributes: {
                            placeholder: "Contoh:Rp. 50.000",
                            inputmode: "numeric",
                            style: "text-align:center"
                        },
                        inputLabel: `Maksimal Rp ${validNominal.toLocaleString('id-ID')}`,
                        inputPlaceholder: "Contoh: 500000",
                        showCancelButton: true,
                        confirmButtonText: "Bayar",
                        cancelButtonText: "Batal",
                        preConfirm: (val) => {
                            if (!val || val <= 0) {
                                Swal.showValidationMessage("Nominal harus lebih dari 0");
                                return false;
                            }
                            if (parseInt(val) > validNominal) {
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
                        metode: 'manual',
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

        function tambahCatatan(tagihanId, isicatatan) {
            // 1️⃣ Simpan ID ke hidden input
            document.getElementById('catatan_tagihan_id').value = tagihanId;
            // 2️⃣ Ambil data tagihan dari window.tagihanData (hasil fetch sebelumnya)
            const tagihan = window.tagihanData?.find(t => t.id === tagihanId);
            // 3️⃣ Isi catatan kalau ada, kosong kalau tidak
            document.getElementById('isiCatatan').value = isicatatan || '';
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
                    const selected = Array.from(
                        document.querySelectorAll(
                            '#tabelBelumLunas tbody input[type="checkbox"]:checked')
                    ).map(cb => cb.value);

                    if (selected.length === 0) {
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

        // Fungsi untuk proses pembayaran multiple
        function prosesMultiplePembayaran(selectedTagihan) {
            let successCount = 0;
            let failedCount = 0;
            const totalTagihan = selectedTagihan.length;

            // Tampilkan loading
            Swal.fire({
                title: 'Memproses Pembayaran...',
                html: `<p>Sedang memproses ${totalTagihan} tagihan</p>`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Proses pembayaran satu per satu secara sequential
            processNextPayment(0);

            function processNextPayment(index) {
                if (index >= selectedTagihan.length) {
                    // Semua pembayaran selesai diproses
                    Swal.fire({
                        title: 'Proses Selesai',
                        html: `
                            <p><strong>Berhasil:</strong> ${successCount} tagihan</p>
                            <p><strong>Gagal:</strong> ${failedCount} tagihan</p>
                        `,
                        icon: successCount > 0 ? 'success' : 'error',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload list tagihan
                        document.getElementById('nama_tagihan').dispatchEvent(new Event('change'));
                    });
                    return;
                }

                const tagihan = selectedTagihan[index];

                // Dapatkan data dari window global jika ada
                const tagihanData = window.tagihanDataMap ? window.tagihanDataMap.get(tagihan.id) : null;

                fetch('/pembayaran/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        tagihan_siswa_id: tagihan.id,
                        bulan: tagihanData?.bulan || tagihan.periode,
                        tahun: tagihanData?.tahun || new Date().getFullYear(),
                        nominal: tagihan.nominal,
                        jumlah_bayar: tagihan.nominal,
                        kategori_id: tagihanData?.kategori_id || 1,
                        metode: 'manual',
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status == 1 || data.status == true) {
                        successCount++;
                    } else {
                        failedCount++;
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    failedCount++;
                })
                .finally(() => {
                    // Update progress
                    const processed = index + 1;
                    Swal.update({
                        html: `<p>Memproses ${processed} dari ${totalTagihan} tagihan...</p>`
                    });

                    // Proses tagihan berikutnya
                    processNextPayment(index + 1);
                });
            }
        }
    </script>
@endpush
