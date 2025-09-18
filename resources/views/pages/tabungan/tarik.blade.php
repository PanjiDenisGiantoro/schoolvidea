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

        {{-- Detail Siswa dan Form Penarikan --}}
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
                            <li><strong>Kelas Sekarang:</strong> <span id="detail_kelas">-</span></li>
                            <li><strong>Saldo Tabungan:</strong> <span id="saldo_awal">Rp 0</span></li>
                        </ul>
                    </div>
                </div>

                {{-- Form Transaksi --}}
                <div class="col-md-8">
                    <form action="{{ route('tabungan.tarik.store') }}" method="POST" id="formTarik">
                        @csrf
                        <input type="hidden" name="kelas_id" id="kelas_hidden">
                        <input type="hidden" name="penerima_id" id="penerima_hidden">

                        <div class="card p-4 shadow-sm rounded-4 border-0">
                            <h5 class="fw-bold text-danger">Detail Penarikan</h5>
                            <hr>

                            <div class="mb-3">
                                <label for="jumlah" class="form-label fw-semibold">Jumlah Penarikan <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah" id="jumlah" class="form-control rounded-pill shadow-sm" required>
                            </div>

                            <div class="mb-3">
                                <label for="keterangan" class="form-label fw-semibold">Keterangan</label>
                                <textarea name="keterangan" id="keterangan" class="form-control rounded-4 shadow-sm" rows="2"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jumlah Transaksi</label>
                                <div id="jumlah_transaksi" class="fw-bold text-danger">Rp 0</div>
                            </div>

                            <button type="submit" class="btn btn-danger w-100 rounded-pill shadow-lg animate-btn">
                                <i class="bx bx-wallet-alt"></i> Proses Tarik Saldo
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
        const jumlahInput = document.getElementById('jumlah');
        const jumlahTransaksi = document.getElementById('jumlah_transaksi');
        const saldoAwalEl = document.getElementById('saldo_awal');
        let saldoAwal = 0;

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
                        option.text = siswa.user.name;
                        siswaSelect.appendChild(option);
                    });
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
                    document.getElementById('detail_nama').innerText = data.nama_lengkap;
                    document.getElementById('detail_nisn').innerText = data.nisn;
                    document.getElementById('detail_kelas').innerText = data.kelas;

                    if (data.saldo_akhir) {
                        saldoAwal = parseInt(data.saldo_akhir); // simpan saldo untuk validasi
                        saldoAwalEl.innerText = 'Rp ' + saldoAwal.toLocaleString('id-ID');
                    }

                    if (data.foto) {
                        document.querySelector('img[alt="Foto Siswa"]').src = `/storage/${data.foto}`;
                    }
                });
        });

        // Update jumlah transaksi real-time + validasi saldo
        jumlahInput.addEventListener('input', function() {
            let value = parseInt(this.value) || 0;

            if (value > saldoAwal) {
                alert("Jumlah penarikan tidak boleh lebih besar dari saldo (" + saldoAwal.toLocaleString('id-ID') + ")");
                this.value = saldoAwal; // otomatis set ke saldo maksimal
                value = saldoAwal;
            }

            jumlahTransaksi.innerText = 'Rp ' + value.toLocaleString('id-ID');
        });
    </script>
@endpush
