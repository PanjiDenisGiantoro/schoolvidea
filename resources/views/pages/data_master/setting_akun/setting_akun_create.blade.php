@extends('layouts.app')
@section('title', isset($setting) ? (isset($show) && $show ? 'Lihat Setting Akun' : 'Edit Setting Akun') : 'Tambah Setting Akun')

@section('content')
    @include('partials.page-title', [
        'title' => isset($setting) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Setting Akun'
    ])

    <div class="card">
        <div class="card-body">
            <form id="settingAkunForm"
                  action="{{ isset($setting) ? route('setting_akun.update', $setting->id) : route('setting_akun.store') }}"
                  method="POST">
                @csrf
                @if(isset($setting))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    {{-- Unit --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-select" data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Unit --</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('unit_id', $setting->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- Kategori --}}
                    <div class="col-md-4">
                        <x-input-field type="text" name="kategori" label="Kategori"
                                       placeholder="Masukkan Kategori" icon="bx bx-list-ul"
                                       :value="old('kategori', $setting->kategori ?? '')" required/>
                    </div>
                    {{-- Akun --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="akun_id" class="form-label">Akun</label>


                            <select name="akun_id" id="akun_id" class="form-control"  data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Akun --</option>

                                {{--                                @foreach($akunOptions as $opt)--}}
                                {{--                                    <option value="{{ $opt['id'] }}" {{ old('akun_id', $akun->akun_id ?? '') == $opt['id'] ? 'selected' : '' }}>--}}
                                {{--                                        {{ $opt['nama'] }}--}}
                                {{--                                    </option>--}}
                                {{--                                @endforeach--}}
                                {{--                                --}}
                                @foreach($akuns as $a)
                                    <option value="{{ $a->id }}"
                                        {{ old('akun_id', $setting->akun_id ?? '') == $a->id ? 'selected' : '' }}>
                                        {{ $a->kode_akun }} - {{ $a->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Nama Setting --}}
                    <div class="col-md-4">
                        <x-input-field type="text" name="nama_setting" label="Nama Setting"
                                       placeholder="Masukkan Nama Setting" icon="bx bx-cog"
                                       :value="old('nama_setting', $setting->nama_setting ?? '')" required/>
                    </div>





                    {{-- Debit --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="debit" class="form-label">Debit</label>
                            <select name="debit" id="debit" class="form-select">
                                <option value="">-- Pilih --</option>
                                <option value="1" {{ old('debit', $setting->debit ?? '') == 1 ? 'selected' : '' }}>1</option>
                                <option value="0" {{ old('debit', $setting->debit ?? '') === 0 ? 'selected' : '' }}>0</option>
                            </select>
                        </div>
                    </div>

                    {{-- Kredit (readonly) --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="kredit" class="form-label">Kredit</label>
                            <input type="text" name="kredit" id="kredit" class="form-control py-3"
                                   value="{{ old('kredit', $setting->kredit ?? '') }}" readonly>
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control"
                                      placeholder="Masukkan Keterangan">{{ old('keterangan', $setting->keterangan ?? '') }}</textarea>
                        </div>
                    </div>



                    {{-- Status --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="1" {{ old('status', $setting->status ?? '') == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status', $setting->status ?? '') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    @if(!isset($show) || !$show)
                        <button type="submit" class="btn btn-success">
                            {{ isset($setting) ? 'Update' : 'Simpan' }}
                        </button>
                    @endif
                    <a href="{{ route('setting_akun.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const debit = document.getElementById('debit');
            const kredit = document.getElementById('kredit');

            function syncDebitKredit() {
                if (debit.value === "1") {
                    kredit.value = 0;
                } else if (debit.value === "0") {
                    kredit.value = 1;
                } else {
                    kredit.value = '';
                }
            }

            debit.addEventListener('change', syncDebitKredit);

            syncDebitKredit();

            @if(isset($show) && $show)
            const formElements = document.querySelectorAll('#settingAkunForm input, #settingAkunForm select, #settingAkunForm textarea, #settingAkunForm button[type="submit"]');
            formElements.forEach(el => {
                el.disabled = true;
                if (el.type === 'submit') el.style.display = 'none';
            });
            @endif
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('settingAkunForm');

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
    @if(session('success'))
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

    @if($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: `
        <ul style="text-align:left;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
                </ul>
`,
                confirmButtonColor: '#d33',
            });
        </script>
    @endif
@endpush
