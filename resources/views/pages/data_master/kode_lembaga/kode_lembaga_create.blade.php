
@extends('layouts.app')
@section('title', isset($lembagaunit) ? (isset($show) && $show ? 'Lihat Lembaga Unit' : 'Edit Lembaga Unit') : 'Tambah Lembaga Unit')

@section('content')
    @include('partials.page-title', [
        'title' => isset($lembagaunit) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Lembaga Unit'
    ])
    <style>
        .dropzone .dz-image img {
            width: 100% !important;
            height: auto !important;
            object-fit: contain; /* biar tidak crop */
        }
    </style>
    <div class="card">
        <div class="card-body">
            <form id="lembagaForm"
                  action="{{ isset($lembagaunit) ? route('lembagaunit.update', $lembagaunit->id) : route('lembagaunit.store') }}"
                  method="POST">
                @csrf
                @if(isset($lembagaunit))
                    @method('PUT')
                @endif

                <div class="row ">
                    <div class="col-md-4">
                        <x-input-field type="text" name="kode_yayasan" label="Kode Yayasan"
                                       placeholder="Masukkan Kode Yayasan" icon="bx bx-building"
                                       :value="old('kode_yayasan', $lembagaunit->kode_yayasan ?? '')" required/>
                        <x-input-field type="text" name="no_hp" label="No. Telepon"
                                       placeholder="Masukkan nomor Telepon" icon="bx bx-phone"
                                       :value="old('no_hp', $lembagaunit->no_hp ?? '')"/>


                        <x-input-field type="text" name="central_code" label="Central Code"
                                       placeholder="Kosongkan Untuk Auto Generate"
                                       icon="bx bx-code"
                                       :value="old('central_code', $lembagaunit->central_code ?? '')"/>

                        {{-- Status --}}
                        <div class="mb-2 ">
                            <label for="status" class="form-label">Status Unit </label>
                            <select name="status" id="status" class="form-select ">
                                <option value="1" {{ old('status', $lembagaunit->status ?? '') == 1 ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status', $lembagaunit->status ?? '') == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="nama_yayasan" label="Nama Yayasan"
                                       placeholder="Masukkan Nama Yayasan" icon="bx bx-building"
                                       :value="old('nama_yayasan', $lembagaunit->nama_yayasan ?? '')" required/>


                        <x-input-field type="email" name="email" label="Email"
                                       placeholder="Masukkan Email" icon="bx bx-envelope"
                                       :value="old('email', $lembagaunit->email ?? '')"/>

                        <x-input-field type="text" name="nama_pimpinan" label="Nama Pimpinan"
                                       placeholder="Masukkan Nama Pimpinan" icon="bx bx-user"
                                       :value="old('nama_pimpinan', $lembagaunit->nama_pimpinan ?? '')"/>
                        <x-input-field type="text" name="website" label="Website"
                                       placeholder="Masukkan Website" icon="bx bx-globe"
                                       :value="old('website', $lembagaunit->website ?? '')"/>

                        {{-- Dropzone Upload --}}

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

                    <div class="col-md-4">
                        <div class="mb-4">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamat" class="form-control" rows="6"
                                      placeholder="Masukkan alamat">{{ old('alamat', $lembagaunit->alamat ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image-dropzone" class="form-label">Upload Gambar Logo</label>
                            <div class="dropzone" id="image-dropzone"></div>
                            <input type="hidden" name="image" id="image-hidden"
                                   value="{{ old('image', $lembagaunit->image ?? '') }}">
                            <small class="text-muted">Format: JPG, PNG | Max: 1MB</small>
                        </div>

                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($lembagaunit) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ url('lembagaunit/') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>

        </div>
    </div>

@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if(isset($show) && $show)
        document.addEventListener('DOMContentLoaded', function () {
            const formElements = document.querySelectorAll('#lembagaForm input, #lembagaForm textarea, #lembagaForm select, #lembagaForm button[type="submit"]');

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

        Dropzone.options.myDropzone = {
            paramName: "file",
            maxFilesize: 1,
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            init: function () {
                this.on("addedfile", function (file) {
                    file.previewElement.addEventListener("click", function () {
                        // ambil url preview
                        let imgSrc = file.dataURL;
                        document.getElementById("previewImage").src = imgSrc;
                        // buka modal
                        var modal = new bootstrap.Modal(document.getElementById("imageModal"));
                        modal.show();
                    });
                });
            }
        };

        let myDropzone = new Dropzone("#image-dropzone", {
            url: "{{ route('lembagaunit.upload') }}",
            paramName: "file",
            maxFiles: 1,
            maxFilesize: 1, // MB
            acceptedFiles: ".jpg,.jpeg,.png",
            addRemoveLinks: true,
            thumbnailWidth: 200,  // ubah default (120px)
            thumbnailHeight: 200, // biar lebih proporsional
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function (file, response) {
                document.querySelector("#image-hidden").value = response.filepath;
            },
            removedfile: function(file) {
                file.previewElement.remove();
                document.querySelector("#image-hidden").value = ""; // kosongkan kalau dihapus
            }
        });

        // Kalau edit, preload gambar lama
        @if(isset($lembagaunit) && $lembagaunit->image)

        let mockFile = {
            name: "Current Image",
            size: 12345,
            type: 'image/jpeg', // bisa disesuaikan
            accepted: true
        };

        myDropzone.emit("addedfile", mockFile);
        myDropzone.emit("thumbnail", mockFile, "{{ asset($lembagaunit->image) }}");
        myDropzone.emit("complete", mockFile);
        myDropzone.files.push(mockFile);
        @endif

    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('kelasForm');

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
