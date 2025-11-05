@extends('layouts.app')

@section('title', 'Tambah Tagihan')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-3">
            <h3>TAMBAH TAGIHAN</h3>
            <a href="{{ route('tagihan.index') }}" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card rounded-3 shadow-sm">
            <div class="card-body">
                <form action="{{ route('tagihan.store') }}" method="POST">
                    @csrf

                    {{-- Unit Pendidikan --}}
                    <div class="mb-3">
                        <label class="form-label">Unit Pendidikan</label>
                        <select name="unit_id" class="form-control" required>
                            <option value="">-- Pilih Unit --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pilih Kelas --}}
                    <div class="mb-3">
                        <label for="kelas" class="form-label">Pilih Kelas</label>
                        <select id="kelas" class="form-control" data-choices data-choices-sorting-false required
                            name="kelas">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pilihan Semua / Per Siswa --}}
                    <div id="pilihanSiswa" class="d-none mb-3">
                        <label class="form-label">Pilih Target</label><br>
                        <input type="radio" name="target" value="all" checked> Semua Siswa
                        <input type="radio" name="target" value="per"> Pilih Per Siswa
                    </div>

                    {{-- Tabel Siswa --}}
                    <div id="tableSiswaWrapper" class="d-none">
                        <table class="table-bordered table">
                            <thead>
                                <tr>
                                    <th width="5%">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th>Nama Siswa</th>
                                    <th>Nomor Induk</th>
                                </tr>
                            </thead>
                            <tbody id="tableSiswa">
                                {{-- Ajax isi disini --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-3" hidden>
                        <label class="form-label">Jenis Tagihan</label><br>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="jenisTagihanSwitch" name="jenis_tagihan"
                                value="bulanan" checked>
                            <label class="form-check-label" for="jenisTagihanSwitch">
                                Bulanan <span class="text-muted">(matikan switch untuk Bebas)</span>
                            </label>
                        </div>
                    </div>

                    {{-- Jumlah Periode --}}

                    {{-- Jika Bulanan tampilkan periode --}}
                    <div id="periodeWrapper" class="mb-3">
                        <label class="form-label">Jumlah Periode Tagihan</label>
                        <select name="periode" class="form-control">
                            <option value="">-- Pilih Periode --</option>
                            <optgroup label="Bulan">
                                @foreach (range(1, 12) as $i)
                                    <option value="{{ $i }}">{{ $i }} Bulan</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Tahun">
                                <option value="24">Bulanan (2 Tahun)</option>
                                <option value="36">Bulanan (3 Tahun)</option>
                                <option value="48">Bulanan (4 Tahun)</option>
                            </optgroup>
                        </select>
                    </div>
                    <div id="bebasWrapper" class="d-none mb-3">
                        <label class="form-label">Nominal Tagihan (Bebas)</label>
                        <input type="number" name="nominal_bebas" class="form-control"
                            placeholder="Masukkan nominal bebas">
                    </div>

                    {{-- Bulan & Tahun Mulai --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Bulan Mulai</label>
                            <select name="bulan_mulai" class="form-control" required>
                                <option value="">-- Pilih Bulan --</option>
                                @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $bulan)
                                    <option value="{{ $i + 1 }}">{{ $bulan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Mulai</label>
                            <select name="tahun_mulai" class="form-control" required>
                                @for ($y = date('Y'); $y <= date('Y') + 5; $y++)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Item Tagihan --}}
                    <div id="itemTagihan">
                        <div class="mb-3 item-wrapper" data-item-index="0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Item Tagihan 1</label>
                                <button type="button" class="btn btn-sm btn-danger d-none" onclick="hapusItem(this)">
                                    <i class="fa fa-trash"></i> Hapus
                                </button>
                            </div>
                            <select name="items[0][id]" class="form-control">
                                <option value="">-- Pilih Item --</option>
                                @foreach ($kategoriTagihan as $kat)
                                    <option value="{{ $kat->id }}">
                                        {{ $kat->nama_kategori }} - Rp
                                        {{ number_format($kat->biaya_tagihan, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="tambahItem()">+ Tambah Item</button>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill">Simpan</button>
                        <a href="{{ route('tagihan.index') }}" class="btn btn-secondary rounded-pill">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            $('#jenisTagihanSwitch').on('change', function() {
                if ($(this).is(':checked')) {
                    // Mode bulanan
                    $('#periodeWrapper').removeClass('d-none');
                    $('#bebasWrapper').addClass('d-none');
                    $(this).val('bulanan');
                } else {
                    // Mode bebas
                    $('#periodeWrapper').addClass('d-none');
                    $('#bebasWrapper').removeClass('d-none');
                    $(this).val('bebas');
                }
            });

            $('#kelas').on('change', function() {
                let kelasId = $(this).val();
                if (kelasId) {
                    $('#pilihanSiswa').removeClass('d-none');
                    $('input[name="target"][value="all"]').prop('checked', true);
                    $('#tableSiswaWrapper').addClass('d-none'); // reset
                    loadSiswa(kelasId);
                } else {
                    $('#pilihanSiswa').addClass('d-none');
                    $('#tableSiswaWrapper').addClass('d-none');
                }
            });

            // Radio toggle
            $('input[name="target"]').on('change', function() {
                if ($(this).val() === 'per') {
                    $('#tableSiswaWrapper').removeClass('d-none');
                } else {
                    $('#tableSiswaWrapper').addClass('d-none');
                }
            });

            // Check All
            $(document).on('change', '#checkAll', function() {
                $('.checkItem').prop('checked', $(this).prop('checked'));
            });
        });

        // Ajax load siswa
        function loadSiswa(kelasId) {
            $.get(`/kelas/${kelasId}/siswa`, function(data) {
                let rows = '';
                data.forEach((s, i) => {
                    rows += `
                <tr>
                    <td><input type="checkbox" name="siswa[]" value="${s.id}" class="checkItem"></td>
                    <td>${s.user.name}</td>
                    <td>${s.nisn}</td>
                </tr>
            `;
                });
                $('#tableSiswa').html(rows);
                $('#checkAll').prop('checked', false);
            });
        }

        let itemCount = 1;

        // Tambah item tagihan
        function tambahItem() {
            let container = document.getElementById('itemTagihan');
            let html = `
        <div class="mb-3 item-wrapper" data-item-index="${itemCount}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Item Tagihan ${itemCount+1}</label>
                <button type="button" class="btn btn-sm btn-danger" onclick="hapusItem(this)">
                    <i class="fa fa-trash"></i> Hapus
                </button>
            </div>
            <select name="items[${itemCount}][id]" class="form-control">
                <option value="">-- Pilih Item --</option>
                @foreach ($kategoriTagihan as $kat)
            <option value="{{ $kat->id }}">
                        {{ $kat->nama_kategori }} - Rp {{ number_format($kat->biaya_tagihan, 0, ',', '.') }}
            </option>
@endforeach
            </select>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', html);
            itemCount++;
            updateDeleteButtons();
        }

        // Hapus item tagihan
        function hapusItem(btn) {
            const wrapper = btn.closest('.item-wrapper');
            wrapper.remove();
            updateDeleteButtons();
            updateItemLabels();
        }

        // Update visibility tombol hapus
        function updateDeleteButtons() {
            const items = document.querySelectorAll('.item-wrapper');
            items.forEach((item, index) => {
                const deleteBtn = item.querySelector('.btn-danger');
                if (items.length > 1) {
                    deleteBtn.classList.remove('d-none');
                } else {
                    deleteBtn.classList.add('d-none');
                }
            });
        }

        // Update label item tagihan
        function updateItemLabels() {
            const items = document.querySelectorAll('.item-wrapper');
            items.forEach((item, index) => {
                const label = item.querySelector('.form-label');
                label.textContent = `Item Tagihan ${index + 1}`;
            });
        }
    </script>
@endpush
