@extends('layouts.app')
@section('title', 'Tambah Transaksi Tabungan')

@section('content')
    @include('partials.page-title', [
        'title' => 'Tambah Transaksi',
        'subTitle' => 'Tabungan / Keuangan'
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
                box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            }
        </style>
    @endpush


    <div class="row g-4">
        <div class="col-md-8">
            <div class="card p-4 shadow-sm rounded-4 border-0">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <label for="filter_kelas" class="form-label fw-semibold">Filter Kelas</label>
                        <select id="filter_kelas" class="form-select rounded-pill shadow-sm">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
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
                    <div class="mb-3">
                        <label for="kategori_tagihan" class="form-label fw-semibold">Jenis Tagihan</label>
                        <select id="kategori_tagihan" class="form-select rounded-pill shadow-sm">
                            <option value="">-- Pilih Jenis Tagihan --</option>
                            <option value="perbulan">Per Bulan</option>
                            <option value="bebas">Bebas</option>
                        </select>
                    </div>

                    <div class="mb-3" id="nama_tagihan_wrapper" style="display: none;">
                        <label for="nama_tagihan" class="form-label fw-semibold">Pilih Nama Tagihan</label>
                        <select id="nama_tagihan" class="form-select rounded-pill shadow-sm">
                            <option value="">-- Pilih Tagihan --</option>
                        </select>
                    </div>




                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 shadow-sm rounded-4 border-0">
                <div class="text-center mb-3">
                    <img src="{{ asset('images/default-user.png') }}"
                         alt="Foto Siswa"
                         class="img-fluid rounded-circle shadow-sm border"
                         width="120">
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
        <div class="card shadow-sm rounded-4 border-0 mt-3">
            <div class="card-body">
                <h6 class="fw-bold text-primary mb-3">📌 Detail Tagihan</h6>
                <ul class="list-group list-group-flush small" id="detail_tagihan">

                    <li class="list-group-item d-flex justify-content-between">
                        <span>Total Tagihan</span>
                        <strong id="total_tagihan">Rp 0</strong>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Daftar Tagihan --}}
        <div class="card shadow-sm rounded-4 border-0 mt-3">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fa fa-list"></i> Daftar Tagihan Per Bulan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light text-center">
                        <tr>
                            <th>Bulan</th>
                            <th>Kategori</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody id="list_tagihan">
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fa fa-info-circle"></i> Silakan pilih siswa & jenis tagihan
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
                        <input type="hidden" name="tagihan_siswa_id" id="tagihan_hidden">
                        <input type="hidden" name="kategori_id" id="kategori_hidden">
                        <input type="hidden" name="bulan" id="bulan_hidden">
                        <input type="hidden" name="tahun" id="tahun_hidden">
                        <input type="hidden" name="kelas_id" id="kelas_hidden">
                        <input type="hidden" name="penerima_id" id="penerima_hidden">

                    </form>

                </div>
            </div>
        </div>
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
        .form-control:focus, .form-select:focus {
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
        const kategoriSelect = document.getElementById('kategori_tagihan');
        const listTagihanContainer = document.getElementById('list_tagihan_container');
        const listTagihan = document.getElementById('list_tagihan');

        kategoriSelect.addEventListener('change', function() {
            const kategori = this.value;
            listTagihan.innerHTML = '';
            if (!kategori) {
                listTagihanContainer.style.display = 'none';
                return;
            }

            if (kategori === 'perbulan') {
                const siswaId = siswaSelect.value;
                if (!siswaId) return;

                fetch(`/tagihan/daftarTagihan/${siswaId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.detail || !data.detail.length) {
                            listTagihan.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fa fa-exclamation-circle text-warning"></i> Tidak ada tagihan
                        </td>
                    </tr>`;
                            document.getElementById('total_tagihan').innerText = "Rp 0";
                            document.getElementById('nama_tagihan_wrapper').style.display = 'none';
                            return;
                        }

                        // isi dropdown nama tagihan
                        const tagihanSelect = document.getElementById('nama_tagihan');
                        tagihanSelect.innerHTML = '<option value="">-- Pilih Tagihan --</option>';
                        data.detail.forEach((tagihan, idx) => {
                            const opt = document.createElement('option');
                            opt.value = tagihan.id;

                            // ambil kategori pertama (kalau ada)
                            const kategoriNama = tagihan.kategori && tagihan.kategori.length > 0
                                ? tagihan.kategori[0].nama_kategori
                                : 'Tanpa Kategori';

                            const kategoriKode = tagihan.kategori && tagihan.kategori.length > 0
                                ? tagihan.kategori[0].kode_kategori
                                : '-';
                            opt.text = `${kategoriKode} - ${kategoriNama} - Rp ${parseInt(tagihan.nominal).toLocaleString('id-ID')}`;
                            tagihanSelect.appendChild(opt);
                        });


                        // ⬇️ disini wrapper di-show
                        document.getElementById('nama_tagihan_wrapper').style.display = 'block';
                        // simpan semua data
                        window.tagihanData = data.detail;
                        document.getElementById('total_tagihan').innerText = 'Rp ' + parseInt(data.total_tagihan).toLocaleString('id-ID');
                    })
                    .catch(err => console.error('Fetch tagihan error:', err));
            } else if (kategori === 'bebas') {
                // bebas → sembunyikan list tagihan
                listTagihanContainer.style.display = 'none';
            }
        });


        // Load siswa berdasarkan kelas
        filterKelas.addEventListener('change', function() {
            var kelasId = this.value;
            kelasHidden.value = kelasId;
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
                    document.getElementById('detail_lahir').innerText = `${data.tempat_lahir}, ${data.tanggal_lahir}`;
                    document.getElementById('detail_telp').innerText = data.no_hp;
                    if (data.foto) {
                        document.querySelector('img[alt="Foto Siswa"]').src = `/storage/${data.foto}`;
                    }
                    // update saldo awal
                })
                .catch(err => console.error(err));
        });
        document.getElementById('nama_tagihan').addEventListener('change', function() {
            const tagihanId = this.value;
            const siswaId = siswaSelect.value;

            if (!tagihanId) return;

            fetch(`/tagihan/perbulan/${siswaId}/${tagihanId}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.length) {
                        listTagihan.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fa fa-exclamation-circle text-warning"></i> Tidak ada data tagihan
                        </td>
                    </tr>`;
                        return;
                    }

                    listTagihan.innerHTML = data.map(tagihan => `
                <tr>
                    <td class="text-center">${tagihan.bulan} ${tagihan.tahun}</td>
                    <td class="text-center">${tagihan.nama_kategori}</td>
                    <td class="fw-bold text-end">Rp ${parseInt(tagihan.nominal).toLocaleString('id-ID')}</td>
                    <td class="text-center">
                        <span class="badge ${tagihan.status === 'lunas' ? 'bg-success' : 'bg-warning'}">
                            ${tagihan.status.toUpperCase()}
                        </span>
                    </td>
                    <td class="text-center">
                        ${tagihan.status === 'Lunas'
                        ? `<span class="text-success"><i class="fa fa-check-circle"></i> Lunas</span>`
                        : `<button type="button"
                                       class="btn btn-success btn-sm rounded-pill"
                                       onclick="bayarTagihan(${tagihan.id}, '${tagihan.bulan}', '${tagihan.tahun}', '${tagihan.nominal}', '${tagihan.id}')">
                                       <i class="fa fa-credit-card"></i> Bayar
                                   </button>`}
                    </td>
                </tr>
            `).join('');
                });
        });

    </script>
    <script>

        function bayarTagihan(tagihanId, bulan, tahun, nominal, kategoriId) {
            if (!confirm(`Yakin ingin bayar ${bulan} ${tahun} sebesar Rp ${parseInt(nominal).toLocaleString('id-ID')} ?`)) {
                return;
            }

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
                    nominal: nominal,
                    kategori_id: kategoriId,
                    metode: 'manual',
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status == 1) {
                        alert('Pembayaran berhasil!');
                        // reload list tagihan supaya status berubah lunas
                        document.getElementById('nama_tagihan').dispatchEvent(new Event('change'));
                    } else {
                        alert('Gagal: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Terjadi kesalahan saat membayar.');
                });
        }


    </script>
@endpush
