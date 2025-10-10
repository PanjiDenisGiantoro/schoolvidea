@extends('layouts.app')
@section('title', isset($rekening) ? (isset($show) && $show ? 'Lihat Rekening' : 'Edit Rekening') : 'Tambah Rekening')

@section('content')
    @include('partials.page-title', [
        'title' => isset($rekening) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Rekening'
    ])

    <div class="card">
        <div class="card-body">
            <form id="rekeningForm"
                  action="{{ isset($rekening) ? route('rekening.update', $rekening->id) : route('rekening.store') }}"
                  method="POST">
                @csrf
                @if(isset($rekening))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="type_rekening" class="form-label">Tipe Rekening</label>
                        <select name="type_rekening" id="type_rekening" class="form-select">
                            <option value="">-- Pilih Tipe Rekening --</option>
                            <option value="tabungan" {{ old('type_rekening', $rekening->type_rekening ?? '') == 'tabungan' ? 'selected' : '' }}>Tabungan</option>
                            <option value="tagihan" {{ old('type_rekening', $rekening->type_rekening ?? '') == 'tagihan' ? 'selected' : '' }}>Tagihan</option>
                            <option value="lainnya" {{ old('type_rekening', $rekening->type_rekening ?? '') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="nama_rekening" label="Nama Rekening"
                                       placeholder="Atas Nama"
                                       :value="old('nama_rekening', $rekening->nama_rekening ?? '')" />
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="no_rekening" label="Nomor Rekening"
                                       placeholder="Masukkan Nomor Rekening"
                                       :value="old('no_rekening', $rekening->no_rekening ?? '')" />
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="nama_pemilik_rekening" label="Nama Pemilik Rekening"
                                       placeholder="Masukkan Nama Pemilik Rekening"
                                       :value="old('nama_pemilik_rekening', $rekening->nama_pemilik_rekening ?? '')" />


                    </div>

                    <div class="col-md-4">
                        <label for="unit_id" class="form-label">Unit</label>
                        <select name="unit_id" id="unit_id" class="form-select">
                            <option value="">-- Pilih Unit --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id', $rekening->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="bank" label="Bank"
                                       placeholder="Nama Bank"
                                       :value="old('bank', $rekening->bank ?? '')" />
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="KCP" label="KCP"
                                       placeholder="KCP Cabang"
                                       :value="old('KCP', $rekening->KCP ?? '')" />
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="1" {{ old('status', $rekening->status ?? '') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status', $rekening->status ?? '') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($rekening) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ route('rekening.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        @if(isset($show) && $show)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#rekeningForm input, #rekeningForm select, #rekeningForm button[type="submit"]')
                .forEach(el => {
                    el.disabled = true;
                    if (el.type === 'submit') el.style.display = 'none';
                });
        });
        @endif
    </script>

    {{-- Konfirmasi Submit --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('rekeningForm');

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
