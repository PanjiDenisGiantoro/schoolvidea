@extends('layouts.app')
@section('title', isset($akun) ? (isset($show) && $show ? 'Lihat Akun' : 'Edit Akun') : 'Tambah Akun')

@section('content')
    @include('partials.page-title', [
        'title' => isset($akun) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Akun'
    ])

    <div class="card">
        <div class="card-body">
            <form id="akunForm"
                  action="{{ isset($akun) ? route('akun.update', $akun->id) : route('akun.store') }}"
                  method="POST">
                @csrf
                @if(isset($akun))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    {{-- Kode Akun --}}
                    <div class="col-md-4">
                        <x-input-field type="text" name="kode_akun" label="Kode Akun"
                                       placeholder="Masukkan Kode Akun" icon="bx bx-barcode"
                                       :value="old('kode_akun', $akun->kode_akun ?? '')" required/>
                    </div>

                    {{-- Nama Akun --}}
                    <div class="col-md-4">
                        <x-input-field type="text" name="nama_akun" label="Nama Akun"
                                       placeholder="Masukkan Nama Akun" icon="bx bx-book"
                                       :value="old('nama_akun', $akun->nama_akun ?? '')" required/>
                    </div>
                    {{--                    list kategori ada tabungan dan transaksi--}}
                    <div class="col-md-4">
                        <label for="kategori_akun" class="form-label">Kategori Akun</label>
                        <select name="kategori_akun" id="kategori_akun" class="form-control" required data-choices data-choices-sorting-false>
                            <option value="">-- Pilih Kategori Akun --</option>
                            <option value="tabungan" {{ old('kategori_akun', $akun->kategori_akun ?? '') == 'tabungan' ? 'selected' : '' }}>Tabungan</option>
                            <option value="transaksi" {{ old('kategori_akun', $akun->kategori_akun ?? '') == 'transaksi' ? 'selected' : '' }}>Transaksi</option>
                        </select>
                    </div>

                    {{-- Tipe --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="tipe" class="form-label">Tipe</label>
                            <select name="tipe" id="tipe" class="form-control" required data-choices data-choices-sorting-false>
                                @php
                                    $types = ['ASET', 'LIABILITAS', 'EKUITAS', 'PENDAPATAN', 'BEBAN'];
                                @endphp
                                @foreach($types as $type)
                                    <option value="{{ $type }}" {{ old('tipe', $akun->tipe ?? '') == $type ? 'selected' : '' }}>
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
                            <select name="parent_id" id="parent_id" class="form-select" required data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Parent --</option>
                                @foreach($parents as $p)
                                    @if(!isset($akun) || $p->id != $akun->id)
                                        <option value="{{ $p->id }}"
                                            {{ old('parent_id', $akun->parent_id ?? '') == $p->id ? 'selected' : '' }}>
                                            {{ $p->nama_akun }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Unit --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-control" required data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Unit --</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('unit_id', $akun->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="1" {{ old('status', $akun->status ?? '') == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status', $akun->status ?? '') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    @if(!isset($show) || !$show)
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(isset($show) && $show)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const formElements = document.querySelectorAll('#akunForm input, #akunForm select, #akunForm button[type="submit"]');
                formElements.forEach(el => {
                    el.disabled = true;
                    if (el.type === 'submit') el.style.display = 'none';
                });
            });
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('jurusanForm');

            form.addEventListener('submit', function(e) {
                e.preventDefault(); // cegah submit langsung

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
                        // tampilkan loading sebelum submit
                        Swal.fire({
                            title: 'Menyimpan...',
                            text: 'Harap tunggu sebentar.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // submit form setelah konfirmasi
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
