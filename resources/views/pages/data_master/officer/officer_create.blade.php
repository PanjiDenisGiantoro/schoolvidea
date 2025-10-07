@extends('layouts.app')
@section('title', isset($officer->officer) ? (isset($show) && $show ? 'Lihat User' : 'Edit User') : 'Tambah User')

@section('content')
    @include('partials.page-title', [
        'title' => isset($officer->officer) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Guru & Staff'
    ])

    <div class="card">
        <div class="card-body">
            <form id="userForm" action="{{ isset($officer->officer) ? route('officer.update', $officer->officer->id) : route('officer.store') }}"
                  method="POST">
                @csrf
                @if(isset($officer->officer))
                    @method('PUT')
                @endif

                {{-- Data Akses --}}
                <h5 class="card-title mb-0 mt-3">Data Akses Guru & Staff</h5>
                <p class="text-muted">Masukkan data akses untuk login</p>
                <hr>
                <div class="row g-3">
                    <div class="col-md-4">
                        <x-input-field type="text" name="name" label="Nama Lengkap"
                                       placeholder="Masukkan nama lengkap" icon="bx bx-user"
                                       :value="old('name', $officer->name ?? '')" required />
                    </div>
                    <div class="col-md-4">
                        <x-input-field type="email" name="email" label="Email"
                                       placeholder="Masukkan email" icon="bx bx-envelope"
                                       :value="old('email', $officer->email ?? '')" required />
                    </div>
                    <div class="col-md-4">
                        <x-input-field type="password" name="password" label="Password"
                                       :placeholder="isset($officer) ? 'Kosongkan jika tidak ingin mengubah password' : 'Masukkan Password'"
                                       icon="bx bx-lock"
                                       :value="old('password')" />
                    </div>
                </div>

                {{-- Data Lengkap --}}
                <h5 class="card-title mb-0 mt-4">Data Lengkap Guru & Staff</h5>
                <p class="text-muted">Masukkan informasi detail petugas</p>
                <hr>
                <div class="row">
                    <!-- Colom - 1 -->
                    <div class="col-md-3">
                        <div class="mb-4">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-select" data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Unit --</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}" {{ old('unit_id', $officer->officer->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-field type="text" name="nip" label="NIP" class="text-uppercase"
                                       placeholder="Masukkan NIP" icon="bx bx-id-card"
                                       :value="old('nip', $officer->officer->nip ?? '')" />
                        <x-input-field type="text" name="tempat_lahir" label="Tempat Lahir"
                                       placeholder="Masukkan tempat lahir" icon="bx bx-map"
                                       :value="old('tempat_lahir', $officer->officer->tempat_lahir ?? '')" />

                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin', $officer->officer->jenis_kelamin ?? '') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin', $officer->officer->jenis_kelamin ?? '') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <x-input-field type="text" name="no_rekening" label="No Rekening"
                                       placeholder="Masukkan Nomor Rekening" icon="bx bx-credit-card"
                                       :value="old('no_rekening', $officer->officer->no_rekening ?? '')" />
                        <x-input-field type="text" name="qr_code" label="QR Code"
                                       placeholder="Masukkan QR Code" icon="bx bx-qr"
                                       :value="old('qr_code', $officer->officer->qr_code ?? '')" />
                    </div>
                    <!-- Colom - 2 -->
                    <div class="col-md-3">
                        <div class="mb-4">
                            <label for="role_id" class="form-label">Role</label>
                            <select name="role_id" class="form-select" data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}" {{ ($officer->officer->role_id ?? '') == $r->id ? 'selected' : '' }}>
                                        {{ $r->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-field type="text" name="nik" label="NIK"
                                       placeholder="Masukkan NIK" icon="bx bx-id-card"
                                       :value="old('nik', $officer->officer->nik ?? '')" />
                        <x-input-field type="text" name="agama" label="Agama"
                                       placeholder="Masukkan agama" icon="bx bx-book"
                                       :value="old('agama', $officer->officer->agama ?? '')" />
                        <x-input-field type="text" name="no_hp" label="No. Telepon"
                                       placeholder="Masukkan Nomor Telepon" icon="bx bx-phone"
                                       :value="old('no_hp', $officer->officer->no_hp ?? '')" />
                        <x-input-field type="text" name="va_guru" label="VA Guru & Staff"
                                       placeholder="Masukkan VA Guru & Staff" icon="bx bx-credit-card"
                                       :value="old('va_guru', $officer->officer->va_guru ?? '')" />
                    </div>
                    <!-- Colom - 3 -->
                    <div class="col-md-3">
                        <div class="mb-4">
                            <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                            <select name="tahun_ajaran_id" class="form-select" data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach($tahun_ajaran as $t)
                                    <option value="{{ $t->id }}" {{ old('tahun_ajaran_id', $officer->tahun_ajaran_id ?? ($tahun_ajaran_selected->id ?? '')) == $t->id ? 'selected' : '' }}>
                                        {{ $t->tahun_ajaran }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-field type="text" name="nuptk" label="NUPTK"
                                       placeholder="Masukkan NUPTK" icon="bx bx-id-card"
                                       :value="old('nuptk', $officer->officer->nuptk ?? '')" />

                        <x-input-field type="date" name="tanggal_lahir" label="Tanggal Lahir"
                                       icon="bx bx-calendar"
                                       :value="old('tanggal_lahir', $officer->officer->tanggal_lahir ?? '')" />
                        <x-input-field type="text" name="bank" label="Bank"
                                       placeholder="Masukkan Nama Bank" icon="bx bx-bank"
                                       :value="old('bank', $officer->officer->bank ?? '')" />
                        <x-input-field type="text" name="no_kartu_rfid" label="No Kartu RFID"
                                       placeholder="Masukkan Nomor RFID" icon="bx bx-barcode"
                                       :value="old('no_kartu_rfid', $officer->officer->no_kartu_rfid ?? '')" />
                    </div>
                    <!-- Colom - 4 -->
                    <div class="col-md-3">
                        <div class="mb-4" id="jurusan-wrapper">
                            <label for="jurusan" class="form-label">Jurusan</label>
                            <select name="jurusan[]" id="jurusan"  class="form-select" data-choices data-choices-sorting-false>
                                <option value="">--Pilih Jurusan--</option>
                                @foreach ($jurusans as $jurusan)
                                    <option value="{{ $jurusan->id }}"
                                        {{ in_array($jurusan->id, old('jurusan', $jurusanArray ?? [])) ? 'selected' : '' }}>
                                        {{ $jurusan->nama_jurusan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamat" class="form-control" rows="6">{{ old('alamat', $officer->officer->alamat ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image-dropzone" class="form-label">Upload Gambar</label>
                            <div class="dropzone" id="image-dropzone"></div>
                            <input type="hidden" name="image" id="image-hidden"
                                   value="{{ old('image', $officer->officer->iamge ?? '') }}">
                            <small class="text-muted">Format: JPG, PNG | Max: 1MB</small>
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
                        {{ isset($officer) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ url('officer/') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if(isset($show) && $show)
        document.addEventListener('DOMContentLoaded', function() {
            const formElements = document.querySelectorAll('#userForm input, #userForm textarea, #userForm select, #userForm button[type="submit"]');
            formElements.forEach(el => {
                el.disabled = true;
                if(el.type === 'submit'){
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
            url: "{{ route('officer.upload') }}",
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
        @if(isset($officer->officer) && $officer->officer->iamge)

        let mockFile = {
            name: "Current Image",
            size: 12345,
            type: 'image/jpeg', // bisa disesuaikan
            accepted: true
        };

        myDropzone.emit("addedfile", mockFile);
        myDropzone.emit("thumbnail", mockFile, "{{ asset($officer->officer->iamge) }}");
        myDropzone.emit("complete", mockFile);
        myDropzone.files.push(mockFile);
        @endif

    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const unitSelect = document.getElementById('unit_id');
            const jurusanWrapper = document.getElementById('jurusan-wrapper');

            function toggleJurusan() {
                if (unitSelect.value === '') {
                    jurusanWrapper.style.display = 'none';
                } else {
                    jurusanWrapper.style.display = 'block';
                }
            }

            // Jalankan saat pertama kali halaman dimuat
            toggleJurusan();

            // Jalankan setiap kali unit berubah
            unitSelect.addEventListener('change', toggleJurusan);
        });
    </script>
    {{-- Konfirmasi Submit --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('userForm');

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
