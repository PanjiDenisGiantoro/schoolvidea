@extends('layouts.app')

@section('title', 'Tambah Tagihan')

@push('styles')
    <style>
        .form-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }

        .form-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.75rem;
        }

        .form-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .section-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #667eea;
            display: inline-block;
        }

        .form-control, .form-select {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .btn-add-row {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-add-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .row-item {
            background: #f8f9fa;
            padding: 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .row-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-submit-group {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-cancel:hover {
            background: #5a6268;
            color: white;
            transform: translateY(-2px);
        }

        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .text-danger-small {
            color: #dc3545;
            font-size: 0.875rem;
            display: block;
            margin-top: 0.375rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="form-header d-flex justify-content-between align-items-center text-white">
            <div>
                <h3><i class="fa fa-file-invoice-dollar me-2 text-white"></i> Tambah Tagihan Baru</h3>
                <p>Form untuk membuat tagihan siswa dengan item dan rekening pembayaran</p>
            </div>
            <a href="{{ route('tagihan.index') }}" class="btn btn-light">
                <i class="fa fa-arrow-left me-2"></i> Kembali
            </a>
        </div>

        <form action="{{ route('tagihan.store') }}" method="POST" id="formTagihan">
            @csrf

            {{-- Section 1: Informasi Dasar --}}
            <div class="section-card">
                <h5 class="section-title"><i class="fa fa-graduation-cap me-2"></i> Informasi Dasar</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unit Pendidikan <span class="text-danger">*</span></label>
                        <select name="unit_id" class="form-select" required>
                            <option value="">-- Pilih Unit --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="kelas" class="form-label">Pilih Kelas <span class="text-danger">*</span></label>
                        <select id="kelas" class="form-select" required name="kelas">
                            <option value="">-- Pilih Unit Terlebih Dahulu --</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Section 2: Target Siswa --}}
            <div class="section-card">
                <h5 class="section-title"><i class="fa fa-users me-2"></i> Target Siswa</h5>

                <div id="pilihanSiswa" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Pilih Target <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="target" id="target_all" value="all" checked>
                            <label class="form-check-label" for="target_all">Semua Siswa dalam Kelas</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="target" id="target_per" value="per">
                            <label class="form-check-label" for="target_per">Pilih Siswa Per Individu</label>
                        </div>
                    </div>

                    {{-- Tabel Siswa --}}
                    <div id="tableSiswaWrapper" class="d-none">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                <tr>
                                    <th width="5%">
                                        <input type="checkbox" class="form-check-input" id="checkAll">
                                    </th>
                                    <th>Nama Siswa</th>
                                    <th>NISN</th>
                                </tr>
                                </thead>
                                <tbody id="tableSiswa">
                                {{-- Ajax isi disini --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Periode Tagihan --}}
            <div class="section-card">
                <h5 class="section-title"><i class="fa fa-calendar me-2"></i> Periode Tagihan</h5>

                <div class="mb-3" hidden>
                    <input type="checkbox" id="jenisTagihanSwitch" name="jenis_tagihan" value="bulanan" checked>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bulan Mulai <span class="text-danger">*</span></label>
                        <select name="bulan_mulai" class="form-select" required>
                            <option value="">-- Pilih Bulan --</option>
                            @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $bulan)
                                <option value="{{ $i + 1 }}" {{ ($i + 1) == date('m') ? 'selected' : '' }}>{{ $bulan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tahun Mulai <span class="text-danger">*</span></label>
                        <select name="tahun_mulai" class="form-select" required>
                            @for ($y = date('Y'); $y <= date('Y') + 5; $y++)
                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div id="periodeWrapper" class="mb-3">
                    <label class="form-label">Jumlah Periode Tagihan <span class="text-danger">*</span></label>
                    <select name="periode" class="form-select">
                        <option value="">-- Pilih Periode --</option>
                        <optgroup label="Bulan">
                            @foreach (range(1, 12) as $i)
                                <option value="{{ $i }}">{{ $i }} Bulan</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Tahun">
                            <option value="24">2 Tahun (24 Bulan)</option>
                            <option value="36">3 Tahun (36 Bulan)</option>
                            <option value="48">4 Tahun (48 Bulan)</option>
                        </optgroup>
                    </select>
                </div>

                <div id="bebasWrapper" class="d-none mb-3">
                    <label class="form-label">Nominal Tagihan (Bebas)</label>
                    <input type="number" name="nominal_bebas" class="form-control" placeholder="Masukkan nominal tagihan">
                </div>
            </div>

            {{-- Section 4: Item Tagihan & Rekening Pembayaran --}}
            <div class="section-card">
                <h5 class="section-title"><i class="fa fa-box me-2"></i> Item Tagihan & Rekening Pembayaran</h5>

                <div id="itemRekeningWrapper">
                    <div class="row-item item-rekening-row" data-row-index="0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Item Tagihan 1 <span class="text-danger">*</span></label>
                                    <button type="button" class="btn btn-sm btn-danger d-none" onclick="hapusItemRekening(this)">
                                        <i class="fa fa-trash"></i> Hapus
                                    </button>
                                </div>
                                <select name="items[0][id]" class="form-select item-select" required>
                                    <option value="">-- Pilih Unit dan Kelas Terlebih Dahulu --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rekening Pembayaran 1 <span class="text-danger">*</span></label>
                                <select name="rekening[0][id]" class="form-select rekening-select" required>
                                    <option value="">-- Pilih Rekening --</option>
                                    @forelse ($datarekening as $rekening)
                                        <option value="{{ $rekening->id }}">{{ $rekening->account_number }} - {{ $rekening->account_name }}</option>
                                    @empty
                                        <option value="" disabled>Tidak ada rekening tersedia</option>
                                    @endforelse
                                </select>
                                @if(empty($datarekening) || $datarekening->isEmpty())
                                    <span class="text-danger-small">
                                        <i class="fa fa-warning"></i> Tidak ada data rekening. Harap tambah rekening terlebih dahulu di menu Data Master > Data Rekening.
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-add-row" onclick="tambahItemRekening()">
                    <i class="fa fa-plus me-2"></i> Tambah Item & Rekening
                </button>
            </div>

            {{-- Buttons Section --}}
            <div class="section-card">
                <div class="btn-submit-group">
                    <a href="{{ route('tagihan.index') }}" class="btn btn-cancel">
                        <i class="fa fa-times me-2"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-submit" id="btnSubmit">
                        <i class="fa fa-save me-2"></i> Simpan Tagihan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        let kategoriBudget = {}; // Cache untuk kategori per unit

        $(document).ready(function() {
            // Dependent Dropdown: Unit -> Kelas -> Item Tagihan
            $('select[name="unit_id"]').on('change', function() {
                let unitId = $(this).val();
                let kelasSelect = $('#kelas');

                // Reset kelas dropdown
                kelasSelect.html('<option value="">-- Pilih Kelas --</option>');

                // Reset dependent fields
                $('#pilihanSiswa').addClass('d-none');
                $('#tableSiswaWrapper').addClass('d-none');
                resetItemDropdowns();

                if (unitId) {
                    // Fetch kelas berdasarkan unit
                    $.get(`/kelas/by-unit/${unitId}`, function(data) {
                        data.forEach(function(kelas) {
                            kelasSelect.append(
                                `<option value="${kelas.id}">${kelas.nama_kelas}</option>`
                            );
                        });
                    }).fail(function() {
                        console.error('Gagal mengambil data kelas');
                    });

                    // Fetch kategori tagihan berdasarkan unit
                    fetchKategoriByUnit(unitId);
                }
            });

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

                    // Refresh item dropdowns ketika kelas berubah
                    updateItemDropdowns();
                } else {
                    $('#pilihanSiswa').addClass('d-none');
                    $('#tableSiswaWrapper').addClass('d-none');
                    resetItemDropdowns();
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

        let itemRekeningCount = 1;

        // Tambah item & rekening kombinasi
        function tambahItemRekening() {
            let container = document.getElementById('itemRekeningWrapper');
            let rekeningOptions = document.querySelector('.rekening-select').innerHTML;
            let html = `
        <div class="mb-3 item-rekening-row" data-row-index="${itemRekeningCount}">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Item Tagihan ${itemRekeningCount+1}</label>
                        <button type="button" class="btn btn-sm btn-danger" onclick="hapusItemRekening(this)">
                            <i class="fa fa-trash"></i> Hapus
                        </button>
                    </div>
                    <select name="items[${itemRekeningCount}][id]" class="form-control item-select" required>
                        <option value="">-- Pilih Item --</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rekening Pembayaran ${itemRekeningCount+1}</label>
                    <select name="rekening[${itemRekeningCount}][id]" class="form-control rekening-select" required>
                        ${rekeningOptions}
                    </select>
                </div>
            </div>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', html);
            itemRekeningCount++;
            updateDeleteButtons();
            updateItemDropdowns();
        }

        // Hapus item & rekening row
        function hapusItemRekening(btn) {
            const wrapper = btn.closest('.item-rekening-row');
            wrapper.remove();
            updateDeleteButtons();
            updateItemLabels();
        }

        // Update visibility tombol hapus
        function updateDeleteButtons() {
            const rows = document.querySelectorAll('.item-rekening-row');
            rows.forEach((row, index) => {
                const deleteBtn = row.querySelector('.btn-danger');
                if (rows.length > 1) {
                    deleteBtn.classList.remove('d-none');
                } else {
                    deleteBtn.classList.add('d-none');
                }
            });
        }

        // Update label item tagihan
        function updateItemLabels() {
            const rows = document.querySelectorAll('.item-rekening-row');
            rows.forEach((row, index) => {
                const itemLabel = row.querySelector('.col-md-6:first-child .form-label');
                itemLabel.textContent = `Item Tagihan ${index + 1}`;
            });
        }

        /**
         * Fetch kategori berdasarkan unit
         */
        function fetchKategoriByUnit(unitId) {
            $.ajax({
                url: `/kategoritagihan/by-unit-kelas/${unitId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        kategoriBudget[unitId] = response.data;
                        updateItemDropdowns();
                    }
                },
                error: function(error) {
                    console.error('Gagal mengambil data kategori:', error);
                }
            });
        }

        /**
         * Update semua item dropdown dengan data kategori
         */
        function updateItemDropdowns() {
            let unitId = $('select[name="unit_id"]').val();
            let kelasId = $('#kelas').val();

            if (!unitId) {
                resetItemDropdowns();
                return;
            }

            let options = '<option value="">-- Pilih Item --</option>';

            if (kategoriBudget[unitId]) {
                kategoriBudget[unitId].forEach(function(item) {
                    let nominal = item.biaya_tagihan.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    options += `<option value="${item.id}">${item.nama_kategori} - Rp ${nominal}</option>`;
                });
            }

            // Update semua item select
            $('.item-select').each(function() {
                let currentValue = $(this).val();
                $(this).html(options);
                if (currentValue) {
                    $(this).val(currentValue);
                }
            });
        }

        /**
         * Reset semua item dropdown
         */
        function resetItemDropdowns() {
            const html = '<option value="">-- Pilih Unit dan Kelas Terlebih Dahulu --</option>';
            $('.item-select').html(html);
        }

        // Handle form submission dengan confirm dialog
        document.getElementById('formTagihan').addEventListener('submit', function(e) {
            e.preventDefault();

            // Validasi form
            if (!this.checkValidity()) {
                alert('Harap isi semua field yang diperlukan');
                return;
            }

            // Hitung ringkasan data
            const unit = document.querySelector('select[name="unit_id"]').selectedOptions[0].text;
            const kelas = document.querySelector('select[name="kelas"]').selectedOptions[0].text;
            const periode = document.querySelector('select[name="periode"]').value;
            const periodeText = periode ? document.querySelector('select[name="periode"]').selectedOptions[0].text : 'Bebas';
            const itemRows = document.querySelectorAll('.item-rekening-row').length;
            const target = document.querySelector('input[name="target"]:checked').value;
            const targetText = target === 'all' ? 'Semua Siswa' : 'Per Siswa';

            // Buat pesan confirm
            let confirmMessage = `Apakah Anda yakin ingin menyimpan tagihan dengan rincian berikut?\n\n`;
            confirmMessage += `Unit: ${unit}\n`;
            confirmMessage += `Kelas: ${kelas}\n`;
            confirmMessage += `Target: ${targetText}\n`;
            confirmMessage += `Periode: ${periodeText}\n`;
            confirmMessage += `Jumlah Item: ${itemRows}\n\n`;
            confirmMessage += `Setelah disimpan, tagihan akan dibuat untuk semua siswa.`;

            // Gunakan SweetAlert2 jika tersedia
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Simpan Tagihan',
                    html: `
                        <div class="text-start">
                            <p><strong>Unit:</strong> ${unit}</p>
                            <p><strong>Kelas:</strong> ${kelas}</p>
                            <p><strong>Target:</strong> ${targetText}</p>
                            <p><strong>Periode:</strong> ${periodeText}</p>
                            <p><strong>Jumlah Item:</strong> ${itemRows}</p>
                            <hr>
                            <p class="text-muted small">Setelah disimpan, tagihan akan dibuat untuk semua siswa yang sesuai dengan kriteria.</p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Simpan Tagihan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('formTagihan').submit();
                    }
                });
            } else {
                // Fallback ke confirm biasa
                if (confirm(confirmMessage)) {
                    document.getElementById('formTagihan').submit();
                }
            }
        });

        // Update label rekening saat tambah
        function updateRekeningLabels() {
            const rows = document.querySelectorAll('.item-rekening-row');
            rows.forEach((row, index) => {
                const labels = row.querySelectorAll('.form-label');
                labels[0].textContent = `Item Tagihan ${index + 1} *`;
                labels[1].textContent = `Rekening Pembayaran ${index + 1} *`;
            });
        }
    </script>
@endpush
