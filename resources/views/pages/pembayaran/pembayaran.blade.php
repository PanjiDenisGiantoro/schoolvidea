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
                    <div class="col-md-6">
                        <label for="filter_kelas" class="form-label fw-semibold">Filter Kelas</label>
                        <select id="filter_kelas" class="form-select rounded-pill shadow-sm">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="siswa_id" class="form-label fw-semibold">Pilih Siswa</label>
                        <select id="siswa_id" class="form-select rounded-pill shadow-sm">
                            <option value="">-- Pilih Siswa --</option>
                        </select>
                    </div>

                    <div class="mb-3" id="nama_tagihan_wrapper" style="display: block;">
                        <label for="nama_tagihan" class="form-label fw-semibold">Pilih Nama Tagihan</label>
                        <select id="nama_tagihan" class="form-select rounded-pill shadow-sm">
                            <option value="">-- Pilih Tagihan --</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card rounded-4 border-0 p-4 shadow-sm">
                <div class="mb-3 text-center">
                    <img src="{{ asset('images/default-user.png') }}" alt="Foto Siswa"
                        class="img-fluid rounded-circle border shadow-sm" width="120">
                </div>
                <ul class="list-unstyled small">
                    <li><strong>Nama Lengkap:</strong> <span id="detail_nama">-</span></li>
                    <li><strong>Nomor Induk:</strong> <span id="detail_nisn">-</span></li>
                    <li><strong>Unit Pendidikan:</strong> <span id="detail_unit">-</span></li>
                    <li><strong>Kelas Sekarang:</strong> <span id="detail_kelas">-</span></li>
                    <li><strong>Nama Jurusan:</strong> <span id="detail_jurusan">-</span></li>
                    <li><strong>Tahun Ajaran:</strong> <span id="detail_tahun">-</span></li>
                    <li><strong>Jenis Kelamin:</strong> <span id="detail_gender">-</span></li>
                    <li><strong>TTL:</strong> <span id="detail_lahir">-</span></li>
                    <li><strong>Telepon:</strong> <span id="detail_telp">-</span></li>
                </ul>
            </div>
        </div>

        {{-- Ringkasan Tagihan --}}

        {{-- Daftar Tagihan --}}
        <div class="card rounded-4 mb-3 mt-3 border-0 shadow-sm">
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
                                    <textarea class="form-control" id="isiCatatan" name="isiCatatan" rows="4"
                                        placeholder="Tulis keterangan tambahan di sini..."></textarea>
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

            <div id="tabelBelumLunas" class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-hover table-striped mb-0 table align-middle">
                        <thead class="table-light text-center">
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
                        <tfoot class="fw-bold table-light text-center" style="font-size: 14px">
                            <tr>
                                <td></td>
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
        const filterKelas = document.getElementById('filter_kelas');
        const siswaSelect = document.getElementById('siswa_id');
        const kelasHidden = document.getElementById('kelas_hidden');
        const penerimaHidden = document.getElementById('penerima_hidden');

        const listTagihanContainer = document.getElementById('list_tagihan_container');
        const listTagihan = document.getElementById('list_tagihan');



        // Load siswa berdasarkan kelas
        filterKelas.addEventListener('change', function() {
            var kelasId = this.value;
            kelasHidden.value = kelasId;

            siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';
            document.getElementById('nama_tagihan_wrapper').style.display = 'block';
            document.getElementById('list_tagihan').innerHTML = `
        <tr>
            <td colspan="5" class="text-center text-muted py-4">
                <i class="fa fa-info-circle"></i> Silakan pilih siswa & jenis tagihan
            </td>
        </tr>`;
            document.getElementById('nama_tagihan').innerHTML =
                '<option value="">-- Pilih Tagihan --</option>'; // Reset nama tagihan


            siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';
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
            if (!siswaId) return;
            fetch(`/siswa/siswadetail/${siswaId}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('detail_nama').innerText = data.nama_lengkap;
                    document.getElementById('detail_nisn').innerText = data.nisn;
                    document.getElementById('detail_unit').innerText = data.unit;
                    document.getElementById('detail_kelas').innerText = data.kelas;
                    document.getElementById('detail_jurusan').innerText = data.jurusan;
                    document.getElementById('detail_tahun').innerText = data.tahun_ajaran;
                    document.getElementById('detail_gender').innerText = data.gender;
                    document.getElementById('detail_lahir').innerText =
                        `${data.tempat_lahir}, ${data.tanggal_lahir}`;
                    document.getElementById('detail_telp').innerText = data.no_hp;
                    if (data.foto) {
                        document.querySelector('img[alt="Foto Siswa"]').src = `/storage/${data.foto}`;
                    }
                    // update saldo awal
                })
                .catch(err => console.error(err));
            fetch(`/tagihan/daftarTagihan/${siswaId}`)
                .then(res => res.json())
                .then(data => {
                    const tagihanSelect = document.getElementById('nama_tagihan');
                    const wrapper = document.getElementById('nama_tagihan_wrapper');
                    const listTagihan = document.getElementById('list_tagihan');
                    listTagihan.innerHTML = '';


                    if (!data.detail || !data.detail.length) {
                        listTagihan.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fa fa-exclamation-circle text-warning"></i> Tidak ada tagihan
                        </td>
                    </tr>`;
                        wrapper.style.display = 'block';
                        return;
                    }

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

                    // Render Belum Lunas
                    const tabelBelum = document.querySelector('#tabelBelumLunas tbody');
                    tabelBelum.innerHTML = data.belum_lunas.map(tagihan => `
            <tr>
                <td class="text-center"><input type="checkbox" value="${tagihan.id}"></td>
                <td class="text-center" style="font-size: 14px">${tagihan.no}</td>
                <td class="text-center" style="font-size: 14px">${tagihan.periode}</td>
                <td class="text-center" style="font-size: 14px">${tagihan.tagihan_kelas}</td>
                <td class="text-center" style="font-size: 14px">Rp ${parseInt(tagihan.rincian_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-center text-danger" style="font-size: 14px">Rp ${parseInt(tagihan.jumlah_potongan).toLocaleString('id-ID')}</td>
                <td class="text-center fw-bold" style="font-size: 14px">Rp ${parseInt(tagihan.jumlah_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-center" style="font-size: 14px">Rp ${parseInt(tagihan.nominal_pembayaran).toLocaleString('id-ID')}</td>
                <td class="text-center text-success" style="font-size: 14px">Rp ${parseInt(tagihan.jumlah_dibayar).toLocaleString('id-ID')}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-warning btn-sm rounded-pill"
                                onclick="tambahCatatan(${tagihan.id}, '${tagihan.catatan}')">
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
        <td class="text-center" style="font-size: 14px">${[...totalPeriode].join(', ')}</td>
        <td class="text-center" style="font-size: 14px">${tagihanKelas}</td>
        <td class="text-center" style="font-size: 14px">Rp ${totalRincian.toLocaleString('id-ID')}</td>
        <td class="text-center text-danger" style="font-size: 14px">Rp ${totalPotongan.toLocaleString('id-ID')}</td>
        <td class="text-center fw-bold" style="font-size: 14px">Rp ${totalTagihan.toLocaleString('id-ID')}</td>
        <td class="text-center" style="font-size: 14px">Rp ${totalDibayar.toLocaleString('id-ID')}</td>
        <td class="text-center text-success" style="font-size: 14px">Rp ${totalTunggakan.toLocaleString('id-ID')}</td>
        <td class="text-center" colspan="2">—</td>
    </tr>
`;
                    // Render Sudah Lunas
                    const tabelLunas = document.querySelector('#tabelSudahLunas tbody');
                    tabelLunas
                        .innerHTML = data.sudah_lunas.map(tagihan => `
            <tr>
                <td class="text-center">${tagihan.no}</td>
                <td class="text-center">${tagihan.periode}</td>
                <td class="text-center">${tagihan.tagihan_kelas}</td>
                <td class="text-center">Rp ${parseInt(tagihan.rincian_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-center text-danger">Rp ${parseInt(tagihan.jumlah_potongan).toLocaleString('id-ID')}</td>
                <td class="text-center fw-bold">Rp ${parseInt(tagihan.jumlah_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-center text-success">Rp ${parseInt(tagihan.jumlah_tagihan).toLocaleString('id-ID')}</td>
                <td class="text-center"><span class="badge bg-success">LUNAS</span></td>
            </tr>
        `).join('');
                });

        });
    </script>
    <script>
        function formatCurrencyInput(input) {
            let value = input.value.replace(/[^\d]/g, '');
            if (value === '') {
                input.value = '';
                return;
            }
            input.value = new Intl.NumberFormat('id-ID').format(value);
        }

        // Sebelum submit → hapus semua titik agar dikirim sebagai angka murni
        document.addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll(
                '.component-value, .deduction-value, [id$="_allowance"], [name="salary"]'
            );
            inputs.forEach(input => {
                input.value = input.value.replace(/\./g, '');
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
                        input: "text",
                        inputAttributes: {
                            placeholder: "Contoh: 50.000",
                            inputmode: "numeric",
                            style: "text-align:center"
                        },
                        inputLabel: `Maksimal Rp ${validNominal.toLocaleString('id-ID')}`,

                        showCancelButton: true,
                        confirmButtonText: "Bayar",
                        cancelButtonText: "Batal",
                        didOpen: () => {
                            const input = Swal.getInput();
                            input.addEventListener('input', () => formatCurrencyInput(input));
                        },
                        preConfirm: (val) => {
                            const numericValue = parseInt(val.replace(/\./g, '')) || 0;
                            if (numericValue <= 0) {
                                Swal.showValidationMessage("Nominal harus lebih dari 0");
                                return false;
                            }
                            if (numericValue > validNominal) {
                                Swal.showValidationMessage(
                                    "Nominal tidak boleh lebih besar dari total tagihan!");
                                return false;
                            }
                            return numericValue;
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

                    // isi ke input hidden
                    hiddenInput.value = selected.join(',');

                    // optional: tampilkan konfirmasi
                    Swal.fire({
                        title: "Konfirmasi Pembayaran",
                        text: `Anda akan memproses ${selected.length} tagihan.`,
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonText: "Lanjutkan",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // kirim form (pastikan form punya id, misal "formTagihan")
                            document.getElementById('formTagihan').submit();
                        }
                    });
                });
            }
        });
    </script>
@endpush
