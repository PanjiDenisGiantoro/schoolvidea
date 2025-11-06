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
                    <div class="col-md-6">
                        <label for="filter_kelas" class="form-label fw-semibold">Filter Kelas</label>
                        <select id="filter_kelas" class="form-control rounded-pill shadow-sm" data-choices
                            data-choices-sorting-false>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="siswa_id" class="form-label fw-semibold">Pilih Siswa</label>
                        <select id="siswa_id" class="form-control rounded-pill shadow-sm"data-choices
                            data-choices-sorting-false>
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
                    <div class="card rounded-4 border-0 p-4 shadow-sm">
                        <div class="mb-3 text-center">
                            <img src="{{ asset('images/default-user.png') }}" alt="Foto Siswa"
                                class="img-fluid rounded-circle border shadow-sm" width="120">
                        </div>

<table class="table table-sm table-borderless w-100 small">
  <tr><th>Nama Lengkap</th><td id="detail_nama">-</td></tr>
  <tr><th>Nomor Induk</th><td id="detail_nisn">-</td></tr>
  <tr><th>Unit Pendidikan</th><td id="detail_unit">-</td></tr>
  <tr><th>Kelas Sekarang</th><td id="detail_kelas">-</td></tr>
  <tr><th>Nama Jurusan</th><td id="detail_jurusan">-</td></tr>
  <tr><th>Tahun Ajaran</th><td id="detail_tahun">-</td></tr>
  <tr><th>Jenis Kelamin</th><td id="detail_gender">-</td></tr>
  <tr><th>TTL</th><td id="detail_lahir">-</td></tr>
  <tr><th>Telepon</th><td id="detail_telp">-</td></tr>
</table>

                    </div>
                </div>

                {{-- Form Transaksi --}}
                <div class="col-md-8">
                    <form action="{{ route('tabungan.store') }}" method="POST" id="formTabungan">
                        @csrf
                        <input type="hidden" name="kelas_id" id="kelas_hidden">
                        <input type="hidden" name="penerima_id" id="penerima_hidden">

                        <div class="card rounded-4 border-0 p-4 shadow-sm">
                            <h5 class="fw-bold text-primary">Detail Transaksi</h5>
                            <hr>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jumlah Saldo Awal</label>
                                <div id="saldo_awal" class="fw-bold text-success">Rp 0</div>
                            </div>

                            <div class="mb-3">
                                <label for="jumlah" class="form-label fw-semibold">Jumlah Setoran <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="jumlah" id="jumlah"
                                    class="form-control rounded-pill shadow-sm"
                                    oninput="formatCurrencyInput(this)"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jumlah Transaksi</label>
                                <div id="jumlah_transaksi" class="fw-bold text-info">Rp 0</div>
                            </div>


                            <div class="mb-3">
                                <label for="keterangan" class="form-label fw-semibold">Keterangan</label>
                                <textarea name="keterangan" id="keterangan" class="form-control rounded-4 shadow-sm" rows="2"></textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100 rounded-pill animate-btn shadow-lg">
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

        .form-control:focus,
        .form-select:focus {
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
        const siswaChoices = new Choices(siswaSelect, {
            removeItemButton: false,
            shouldSort: false
        });

        filterKelas.addEventListener('change', function() {
            const kelasId = this.value;
            kelasHidden.value = kelasId;

            // reset select siswa
            siswaChoices.clearStore();
            siswaChoices.setChoices([{
                value: '',
                label: '-- Pilih Siswa --',
                selected: true
            }], 'value', 'label', true);

            if (!kelasId) return;

            fetch(`/siswa/by-kelas/${kelasId}`)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
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
                    document.getElementById('detail_nama').innerText = ': ' + (data.nama_lengkap || '-');
                    document.getElementById('detail_nisn').innerText = ': ' + (data.nisn || '-');
                    document.getElementById('detail_unit').innerText = ': ' + (data.unit || '-');
                    document.getElementById('detail_kelas').innerText = ': ' + (data.kelas || '-');
                    document.getElementById('detail_jurusan').innerText = ': ' + (data.jurusan || '-');
                    document.getElementById('detail_tahun').innerText = ': ' + (data.tahun_ajaran || '-');
                    document.getElementById('detail_gender').innerText = ': ' + (data.gender || '-');
                    document.getElementById('detail_lahir').innerText =
                        `: ${data.tempat_lahir || '-'}, ${data.tanggal_lahir || '-'}`;
                    document.getElementById('detail_telp').innerText = ': ' + data.no_hp || '-';

                    if (data.foto) {
                        document.querySelector('img[alt="Foto Siswa"]').src = `${data.foto}`;
                        console.log('path foto: ', data.foto)
                    } else {
                        document.querySelector('img[alt="Foto Siswa"]').src =
                            `{{ asset('images/default-user.png') }}`;
                    }

                    // update saldo awal jika ada
                    document.getElementById('saldo_awal').innerText = data.saldo_akhir ?
                        'Rp ' + parseInt(data.saldo_akhir).toLocaleString('id-ID') :
                        'Rp 0';
                })
                .catch(err => console.error('Fetch detail siswa error:', err));
        });
    </script>
        <script>
        function formatCurrencyInput(input) {
            let value = input.value.replace(/[^\d]/g, '');
            if (value === '') {
                input.value = '';
                return;
            }
            input.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        }



        // Sebelum submit → hapus semua titik agar dikirim sebagai angka murni
        document.addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll(
                '.component-value, .deduction-value, [id$="_allowance"], [name="salary"]'
            );
            inputs.forEach(input => {
                input.value = input.value.replace(/[^\d]/g, '');
            });
        });
    </script>
@endpush
