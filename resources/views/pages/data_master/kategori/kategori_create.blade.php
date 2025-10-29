@extends('layouts.app')
@section('title', isset($kategoritagihan) ? (isset($show) && $show ? 'Lihat Kategori Tagihan' : 'Edit Kategori Tagihan')
    : 'Tambah Kategori Tagihan')

@section('content')
    @include('partials.page-title', [
        'title' => isset($kategoritagihan) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Kategori Tagihan',
    ])

    <div class="card">
        <div class="card-body">
            <form id="kategoriForm"
                action="{{ isset($kategoritagihan) ? route('kategoritagihan.update', $kategoritagihan->id) : route('kategoritagihan.store') }}"
                method="POST">
                @csrf
                @if (isset($kategoritagihan))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    {{-- Unit --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-select" data-choices
                                data-choices-sorting-false required @if (isset($show) && $show) disabled @endif>
                                <option value="">-- Pilih Unit --</option>
                                @foreach ($units as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('unit_id', $kategoritagihan->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                            <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" data-choices
                                data-choices-sorting-false required @if (isset($show) && $show) disabled @endif>
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach ($tahun_ajaran as $t)
                                    <option value="{{ $t->id }}"
                                        {{ old('tahun_ajaran_id', $kategoritagihan->tahun_ajaran_id ?? ($tahun_ajaran_selected->id ?? '')) == $t->id ? 'selected' : '' }}>
                                        {{ $t->tahun_ajaran }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Kode Kategori --}}
                    <div class="col-md-4">
                        <x-input-field type="text" name="kode_kategori" label="Kode Kategori"
                            placeholder="Masukkan Kode Kategori" icon="bx bx-barcode" :value="old('kode_kategori', $kategoritagihan->kode_kategori ?? '')" required />
                    </div>

                    {{-- Nama Kategori --}}
                    <div class="col-md-4">
                        <x-input-field type="text" name="nama_kategori" label="Nama Kategori"
                            placeholder="Masukkan Nama Kategori" icon="bx bx-book" :value="old('nama_kategori', $kategoritagihan->nama_kategori ?? '')" required />
                    </div>



                    {{-- Biaya Tagihan --}}
                    <div class="col-md-4">
                        <x-input-field type="text" name="biaya_tagihan" label="Biaya Tagihan"
                            placeholder="Masukkan Biaya Tagihan" icon="bx bx-money" :value="old('biaya_tagihan', $kategoritagihan->biaya_tagihan ?? '')"
                            oninput="formatCurrencyInput(this)" required />
                    </div>
                    {{-- Status --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="1"
                                    {{ old('status', $kategoritagihan->status ?? '') == '1' ? 'selected' : '' }}>Aktif
                                </option>
                                <option value="0"
                                    {{ old('status', $kategoritagihan->status ?? '') == '0' ? 'selected' : '' }}>Tidak Aktif
                                </option>
                            </select>
                        </div>
                    </div>
                    {{-- Keterangan --}}
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" rows="2" placeholder="Tambahkan keterangan">{{ old('keterangan', $kategoritagihan->keterangan ?? '') }}</textarea>
                        </div>
                    </div>


                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($kategoritagihan) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ url('kategoritagihan/') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        @if (isset($show) && $show)
            document.addEventListener('DOMContentLoaded', function() {
                const formElements = document.querySelectorAll(
                    '#kategoriForm input, #kategoriForm textarea, #kategoriForm select, #kategoriForm button[type="submit"]'
                );
                formElements.forEach(el => {
                    el.disabled = true;
                    if (el.type === 'submit') {
                        el.style.display = 'none';
                    }
                });
            });
        @endif
    </script>

    {{-- Konfirmasi Submit --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('kategoriForm');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Apakah data sudah benar?',
                    text: "Pastikan semua data sudah diisi dengan benar sebelum menyimpan.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menyimpan...',
                            text: 'Harap tunggu sebentar.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            });
        });
    </script>

    {{-- Alert Sukses & Error --}}
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: `
        <ul style="text-align:left;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
                </ul>
`,
                confirmButtonColor: '#d33',
            });
        </script>
    @endif
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
@endpush
