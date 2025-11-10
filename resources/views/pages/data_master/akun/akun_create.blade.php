@extends('layouts.app')
@section('title', isset($akun) ? (isset($show) && $show ? 'Lihat Akun' : 'Edit Akun') : 'Tambah Akun')

@section('content')
    @include('partials.page-title', [
        'title' => isset($akun) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Akun',
    ])

    <div class="card">
        <div class="card-body">
            <form id="akunForm" action="{{ isset($akun) ? route('akun.update', $akun->id) : route('akun.store') }}"
                method="POST">
                @csrf
                @if (isset($akun))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    {{-- Unit --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-control" data-choices
                                data-choices-sorting-false @if (isset($show) && $show) disabled @endif>
                                <option value="">-- Pilih Unit --</option>
                                @foreach ($units as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('unit_id', $akun->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{--                    list kategori ada tabungan dan transaksi --}}
                    <div class="col-md-4">
                        <label for="kategori_akun" class="form-label">Kategori Akun</label>
                        <select name="kategori_akun" id="kategori_akun" class="form-control" data-choices
                            data-choices-sorting-false @if (isset($show) && $show) disabled @endif>
                            <option value="">-- Pilih Kategori Akun --</option>
                            <option value="tabungan"
                                {{ old('kategori_akun', $akun->kategori_akun ?? '') == 'tabungan' ? 'selected' : '' }}>
                                Tabungan</option>
                            <option value="transaksi"
                                {{ old('kategori_akun', $akun->kategori_akun ?? '') == 'transaksi' ? 'selected' : '' }}>
                                Transaksi</option>
                        </select>
                    </div>
                    {{-- Kode Akun --}}
                    <div class="col-md-4">
                        <x-input-field type="text" name="kode_akun" label="Kode Akun" placeholder="Masukkan Kode Akun"
                            icon="bx bx-barcode" :value="old('kode_akun', $akun->kode_akun ?? '')" />
                    </div>

                    {{-- Nama Akun --}}
                    <div class="col-md-4">
                        <x-input-field type="text" name="nama_akun" label="Nama Akun" placeholder="Masukkan Nama Akun"
                            icon="bx bx-book" :value="old('nama_akun', $akun->nama_akun ?? '')" />
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control p-3" rows="1" placeholder="Tambahkan keterangan">{{ old('keterangan', $akun->keterangan ?? '') }}</textarea>
                        </div>
                    </div>
                    {{-- Status --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="1" {{ old('status', $akun->status ?? '') == '1' ? 'selected' : '' }}>
                                    Aktif</option>
                                <option value="0" {{ old('status', $akun->status ?? '') == '0' ? 'selected' : '' }}>
                                    Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    {{-- Tipe --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="tipe" class="form-label">Tipe</label>
                            <select name="tipe" id="tipe" class="form-control" data-choices
                                data-choices-sorting-false @if (isset($show) && $show) disabled @endif>
                                @php
                                    $types = ['ASET', 'LIABILITAS', 'EKUITAS', 'PENDAPATAN', 'BEBAN'];
                                @endphp
                                @foreach ($types as $type)
                                    <option value="{{ $type }}"
                                        {{ old('tipe', $akun->tipe ?? '') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Parent --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent</label>
                            <select name="parent_id" id="parent_id" class="form-select" data-choices
                                data-choices-sorting-false @if (isset($show) && $show) disabled @endif>
                                <option value="" selected disabled>-- Pilih Parent --</option>
                                @foreach ($parents as $p)
                                    @if (!isset($akun) || $p->id != ($akun->id ?? null))
                                        <option value="{{ $p->id }}"
                                            {{ (string) old('parent_id', $akun->parent_id ?? '') === (string) $p->id ? 'selected' : '' }}>
                                            {{ $p->nama_akun }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>





                    </div>

                    <div class="mt-3 text-end">
                        @if (!isset($show) || !$show)
                            <button type="submit" class="btn btn-success">
                                {{ isset($akun) ? 'Update' : 'Simpan' }}
                            </button>
                        @endif
                        <a href="{{ route('akun.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (isset($show) && $show)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const formElements = document.querySelectorAll(
                    '#akunForm input, #akunForm select, #akunForm textarea, #akunForm button[type="submit"]');
                formElements.forEach(el => {
                    el.disabled = true;
                    if (el.type === 'submit') el.style.display = 'none';
                });
            });
        </script>
    @endif

    {{-- SweetAlert tetap --}}

    <script>
        (function initChoicesOnce() {
            const init = () => {
                document.querySelectorAll('select[data-choices]').forEach(el => {
                    if (el.dataset.choicesInited === '1') return; // cegah double init
                    new Choices(el, {
                        searchEnabled: true,
                        shouldSort: false,
                        removeItemButton: false,
                        placeholder: true,
                        placeholderValue: el.querySelector('option[disabled]')?.textContent ??
                            'Pilih...'
                    });
                    el.dataset.choicesInited = '1';
                });
            };

            document.addEventListener('DOMContentLoaded', init);
            // Jika pakai Turbo/PJAX, aktifkan event berikut (opsional):
            document.addEventListener('turbo:load', init);
            document.addEventListener('pjax:end', init);
        })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('akunForm');
            if (!form) return;

            const handleSubmit = function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Apakah data sudah benar?',
                    text: 'Pastikan semua data sudah diisi dengan benar sebelum menyimpan.',
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
                            allowOutsideClick: false,
                            didOpen: Swal.showLoading
                        });
                        form.removeEventListener('submit', handleSubmit);
                        form.submit();
                    }
                });
            };

            form.addEventListener('submit', handleSubmit);
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

@endpush
