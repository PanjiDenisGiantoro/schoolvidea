@extends('layouts.app')
@section('title', 'Tambah Transaksi Tabungan')

@section('content')
    @include('partials.page-title', [
        'title' => 'Tambah Transaksi',
        'subTitle' => 'Tabungan / Keuangan'
    ])

    <div class="row g-4">
        {{-- Pilih Siswa --}}
        <div class="col-12">
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
                </div>
            </div>
        </div>

        {{-- Detail Siswa dan Form Tabungan --}}
        <div class="col-12">
            <div class="row g-4">
                {{-- Detail Siswa --}}
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

                {{-- Form Transaksi --}}
                <div class="col-md-8">
                    <form action="{{ route('tabungan.store') }}" method="POST" id="formTabungan">
                        @csrf
                        <input type="hidden" name="kelas_id" id="kelas_hidden">
                        <input type="hidden" name="penerima_id" id="penerima_hidden">

                        <div class="card p-4 shadow-sm rounded-4 border-0">
                            <h5 class="fw-bold text-primary">Detail Transaksi</h5>
                            <hr>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jumlah Saldo Awal</label>
                                <div id="saldo_awal" class="fw-bold text-success">Rp 0</div>
                            </div>

                            <div class="mb-3">
                                <label for="jumlah" class="form-label fw-semibold">Jumlah Setoran <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah" id="jumlah" class="form-control rounded-pill shadow-sm" required>
                            </div>

                            <div class="mb-3">
                                <label for="keterangan" class="form-label fw-semibold">Keterangan</label>
                                <textarea name="keterangan" id="keterangan" class="form-control rounded-4 shadow-sm" rows="2"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jumlah Transaksi</label>
                                <div id="jumlah_transaksi" class="fw-bold text-info">Rp 0</div>
                            </div>

                            <button type="submit" class="btn btn-success w-100 rounded-pill shadow-lg animate-btn">
                                <i class="bx bx-check-circle"></i> Proses Tambah Saldo
                            </button>
                        </div>
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
        // Update jumlah transaksi real-time
        const jumlahInput = document.getElementById('jumlah');
        const jumlahTransaksi = document.getElementById('jumlah_transaksi');

        jumlahInput.addEventListener('input', function() {
            const value = parseInt(this.value) || 0;
            jumlahTransaksi.innerText = 'Rp ' + value.toLocaleString('id-ID');
        });

        // Element DOM
        const filterKelas = document.getElementById('filter_kelas');
        const siswaSelect = document.getElementById('siswa_id');
        const kelasHidden = document.getElementById('kelas_hidden');
        const penerimaHidden = document.getElementById('penerima_hidden');

        // Load siswa berdasarkan kelas
        filterKelas.addEventListener('change', function() {
            const kelasId = this.value;
            kelasHidden.value = kelasId; // update hidden input
            siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';

            if (!kelasId) return;

            fetch(`/siswa/by-kelas/${kelasId}`)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    if (!data.length) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.text = 'Tidak ada siswa';
                        siswaSelect.appendChild(option);
                        return;
                    }

                    data.forEach(siswa => {
                        const option = document.createElement('option');
                        option.value = siswa.id;
                        option.text = siswa.user && siswa.user.name ? siswa.user.name : 'Nama tidak tersedia';
                        siswaSelect.appendChild(option);
                    });
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    const option = document.createElement('option');
                    option.value = '';
                    option.text = 'Gagal memuat siswa';
                    siswaSelect.appendChild(option);
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
                    document.getElementById('detail_unit').innerText = data.unit || '-';
                    document.getElementById('detail_kelas').innerText = data.kelas || '-';
                    document.getElementById('detail_jurusan').innerText = data.jurusan || '-';
                    document.getElementById('detail_tahun').innerText = data.tahun_ajaran || '-';
                    document.getElementById('detail_gender').innerText = data.gender || '-';
                    document.getElementById('detail_lahir').innerText = `${data.tempat_lahir || '-'}, ${data.tanggal_lahir || '-'}`;
                    document.getElementById('detail_telp').innerText = data.no_hp || '-';

                    if (data.foto) {
                        document.querySelector('img[alt="Foto Siswa"]').src = `/storage/${data.foto}`;
                    } else {
                        document.querySelector('img[alt="Foto Siswa"]').src = `{{ asset('images/default-user.png') }}`;
                    }

                    // update saldo awal jika ada
                    document.getElementById('saldo_awal').innerText = data.saldo_akhir
                        ? 'Rp ' + parseInt(data.saldo_akhir).toLocaleString('id-ID')
                        : 'Rp 0';
                })
                .catch(err => console.error('Fetch detail siswa error:', err));
        });

    </script>
@endpush
