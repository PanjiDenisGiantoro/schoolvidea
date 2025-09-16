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
            <div class="card p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <label for="filter_kelas" class="form-label">Filter Kelas</label>
                        <select id="filter_kelas" class="form-select">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="siswa_id" class="form-label">Pilih Siswa</label>
                        <select id="siswa_id" class="form-select">
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
                    <div class="card p-3">
                        <div class="text-center mb-3">
                            <img src="{{ asset('images/default-user.png') }}" alt="Foto Siswa" class="img-fluid rounded-circle" width="100">
                        </div>
                        <ul class="list-unstyled">
                            <li>Nama Lengkap: <span id="detail_nama">-</span></li>
                            <li>Nomor Induk: <span id="detail_nisn">-</span></li>
                            <li>Unit Pendidikan: <span id="detail_unit">-</span></li>
                            <li>Kelas Sekarang: <span id="detail_kelas">-</span></li>
                            <li>Nama Jurusan: <span id="detail_jurusan">-</span></li>
                            <li>Tahun Ajaran: <span id="detail_tahun">-</span></li>
                            <li>Jenis Kelamin: <span id="detail_gender">-</span></li>
                            <li>Tempat & Tanggal Lahir: <span id="detail_lahir">-</span></li>
                            <li>Nomor Telepon: <span id="detail_telp">-</span></li>
                        </ul>
                    </div>
                </div>

                {{-- Form Transaksi --}}
                <div class="col-md-8">
                    <form action="{{ route('tabungan.store') }}" method="POST" id="formTabungan">
                        @csrf
                        <input type="hidden" name="kelas_id" id="kelas_hidden">
                        <input type="hidden" name="penerima_id" id="penerima_hidden">

                        <div class="card p-3">
                            <h5>Detail Transaksi</h5>
                            <hr>

                            <div class="mb-3">
                                <label>Jumlah Saldo Awal</label>
                                <div id="saldo_awal">Rp 0</div>
                            </div>

                            <div class="mb-3">
                                <label for="jumlah" class="form-label">Jumlah Setoran <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah" id="jumlah" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <textarea name="keterangan" id="keterangan" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="mb-3">
                                <label>Jumlah Transaksi</label>
                                <div id="jumlah_transaksi">Rp 0</div>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <i class="bx bx-check-circle"></i> Proses Tambah Saldo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Update jumlah transaksi real-time
        const jumlahInput = document.getElementById('jumlah');
        const jumlahTransaksi = document.getElementById('jumlah_transaksi');

        jumlahInput.addEventListener('input', function() {
            const value = parseInt(this.value) || 0;
            jumlahTransaksi.innerText = 'Rp ' + value.toLocaleString('id-ID');
        });

        const filterKelas = document.getElementById('filter_kelas');
        const siswaSelect = document.getElementById('siswa_id');
        const kelasHidden = document.getElementById('kelas_hidden');
        const penerimaHidden = document.getElementById('penerima_hidden');

        // Load siswa berdasarkan kelas
        filterKelas.addEventListener('change', function() {
            const kelasId = this.value;
            kelasHidden.value = kelasId;
            siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';

            if (!kelasId) return;

            fetch(`/siswa/by-kelas/${kelasId}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(siswa => {
                        const option = document.createElement('option');
                        option.value = siswa.id;
                        option.text = siswa.user.name; // sesuaikan field nama user
                        siswaSelect.appendChild(option);
                    });
                })
                .catch(err => console.error(err));
        });

        // Load detail siswa saat dipilih
        siswaSelect.addEventListener('change', function() {
            const siswaId = this.value;
            penerimaHidden.value = siswaId; // update hidden input untuk request

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
                    // Optional: update saldo awal jika ada
                    if (data.saldo_akhir) {
                        document.getElementById('saldo_awal').innerText = 'Rp ' + parseInt(data.saldo_akhir).toLocaleString('id-ID');
                    }
                });
        });
    </script>
@endpush
