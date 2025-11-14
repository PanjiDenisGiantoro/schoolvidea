@extends('layouts.app')
@section('title', isset($datarekening) ? (isset($show) && $show ? 'Lihat Data Rekening' : 'Edit Data Rekening') : 'Tambah Rekening')

@section('content')
    @include('partials.page-title', [
        'title' => isset($datarekening) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Jurusan',
    ])

    <div class="card">
        <div class="card-body">
            <form id="datarekeningForm"
                action="{{ isset($datarekening) ? route('data-rekening.update', $datarekening->id) : route('data-rekening.store') }}"
                method="POST">
                @csrf
                @if (isset($datarekening))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    {{-- Unit --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit <span class="text-danger">*</span></label>
                            <select name="unit_id" id="unit_id" class="form-select" required>
                                <option>-- Pilih Unit --</option>
                                @foreach ($units as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('unit_id', $datarekening->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @php
                        $options = [
                        'Semua Pembayaran',
                        'Pembayaran Tagihan',
                        'Pembayaran Tabungan',
                        'Pembayaran PPDB',
                        'Pembayaran E-Kantin',
                        'Pembayaran Umum'
                    ]
                    @endphp
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="allotment" class="form-label">Peruntukan Rekening <span class="text-danger">*</span></label>
                            <select name="allotment" id="allotment" class="form-select" required>
                                <option value="">--Pilih--</option>
                                @foreach ($options as $opt)
                                        <option value="{{ $opt }}" {{ old('allotment', $datarekening->allotment ?? '') == $opt ? 'selected' : '' }}>
                                            {{ $opt }}
                                        </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Nama --}}
                    <div class="col-md-4">
                        <x-input-field type="text" name="account_name" label="Bank"
                            placeholder="Masukkan Nama Rekening" icon="bx bx-barcode" :value="old('account_name', $datarekening->account_name ?? '')" required />
                    </div>

                    {{-- Nama  --}}
                    <div class="col-md-4">
                        <x-input-field type="text" name="owner_name" label="Nama Pemilik Rekening"
                            placeholder="Masukkan Nama Pemilik Rekening" icon="bx bx-user" :value="old('owner_name', $datarekening->owner_name ?? '')" required />
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="account_number" label="Nomor Rekening"
                            placeholder="Masukkan Nomor Rekening" icon="bx bx-book" :value="old('account_number', $datarekening->account_number ?? '')" required />
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="account_code" label="Kode Rekening"
                            placeholder="Masukkan Kode Rekening" icon="bx bx-book" :value="old('account_code', $datarekening->account_code ?? '')" required />
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="kcp_name" label="Nama KCP"
                            placeholder="Masukkan Nama KCP" icon="bx bx-book" :value="old('kcp_name', $datarekening->kcp_name ?? '')" required />
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="1"
                                    {{ old('status', $datarekening->status ?? '') == '1' ? 'selected' : '' }}>
                                    Aktif
                                </option>
                                <option value="0"
                                    {{ old('status', $datarekening->status ?? '') == '0' ? 'selected' : '' }}>
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="image-dropzone" class="form-label">Upload Gambar</label>
                            <div class="dropzone" id="image-dropzone"></div>
                            <input type="hidden" name="image" id="image-hidden"
                                value="{{ old('image', $datarekening->account_pict ?? '') }}">
                            <small class="text-muted">Format: JPG, PNG, GIF | Max: 1MB</small>
                        </div>
                        <div class="modal fade" id="imageModal" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-body text-center">
                                        <img id="previewImage" src="" class="img-fluid rounded" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($datarekening) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ url('data-rekening/') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if (isset($show) && $show)
            document.addEventListener('DOMContentLoaded', function() {
                const formElements = document.querySelectorAll(
                    '#datarekeningForm input, #datarekeningForm textarea, #datarekeningForm select, #datarekeningForm button[type="submit"]'
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
    <script>
        Dropzone.autoDiscover = false;



        let myDropzone = new Dropzone("#image-dropzone", {
            url: "{{ route('data-rekening.upload') }}",
            paramName: "file",
            maxFiles: 1,
            maxFilesize: 1, // MB
            acceptedFiles: ".jpg,.jpeg,.png",
            addRemoveLinks: true,
            thumbnailWidth: 200, // ubah default (120px)
            thumbnailHeight: 200, // biar lebih proporsional
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function(file, response) {
                document.querySelector("#image-hidden").value = response.filepath;
            },
            removedfile: function(file) {
                file.previewElement.remove();
                document.querySelector("#image-hidden").value = ""; // kosongkan kalau dihapus
            }
        });

myDropzone.on("addedfile", function(file) {
    file.previewElement.addEventListener("click", function() {
        let imgSrc = file.dataURL || "{{ asset($datarekening->account_pict ?? '') }}";
        document.getElementById("previewImage").src = imgSrc;
        var modal = new bootstrap.Modal(document.getElementById("imageModal"));
        modal.show();
    });
});
        // Kalau edit, preload gambar lama
        @if (isset($datarekening) && $datarekening->account_pict)

            let mockFile = {
                name: "Current Image",
                size: 12345,
                type: 'image/jpeg', // bisa disesuaikan
                accepted: true
            };

            myDropzone.emit("addedfile", mockFile);
            myDropzone.emit("thumbnail", mockFile, "{{ asset($datarekening->account_pict) }}");
            myDropzone.emit("complete", mockFile);
            myDropzone.files.push(mockFile);
        @endif
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('datarekeningForm');

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
