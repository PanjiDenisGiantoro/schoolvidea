@php
    $jabatanSelected = old('jabatan_id', $officer->position_id ?? $officer->officer->position_id ?? '');
@endphp
@extends('layouts.app')
@section('title', isset($officer->officer) ? (isset($show) && $show ? 'Lihat User' : 'Edit User') : 'Tambah User')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />


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
                        <x-input-field type="email" id="email_top" name="email" label="Email"
                                       placeholder="Email" icon="bx bx-envelope"
                                       :value="old('email', $officer->email ?? '')" required readonly/>
                    </div>
                    <div class="col-md-4">
                        <x-input-field type="text" name="username" label="Username"
                                       placeholder="Username" icon="bx bx-user"
                                       :value="old('username', $officer->username ?? '')" required readonly />
                    </div>
                    <div class="col-md-4">
                        <x-input-field type="text" name="password" label="Password"
                                       :placeholder="isset($officer) ? 'Kosongkan jika tidak ingin mengubah password' : 'Masukkan Password'"
                                       icon="bx bx-lock"
                                       :value="old('password')" readonly/>
                    </div>
                </div>

                {{-- Data Lengkap --}}
                <div class="card-title mb-0 mt-4">Data Lengkap Guru & Staff <span style="color: #dc3545 !important;">*</span><small class="text-muted">(Wajib diisi)</small></div>
                <p class="text-muted">Masukkan informasi detail petugas</p>
                <hr>
                <div class="row">
                    <!-- Colom - 1 -->
                    <div class="col-md-3">
                        <x-input-field type="number" name="nip" label="NIP" class="text-uppercase"
                                       placeholder="Masukkan NIP" icon="bx bx-id-card"
                                       :value="old('nip', $officer->officer->nip ?? '')" required/>
                    </div>
                    <div class="col-md-3">
                        <x-input-field type="number" name="nuptk" label="NUPTK"
                                       placeholder="Masukkan NUPTK" icon="bx bx-id-card"
                                       :value="old('nuptk', $officer->officer->nuptk ?? '')" required/>
                    </div>
                    <div class="col-md-3">
                        <x-input-field type="text" name="name" label="Nama Lengkap"
                                       placeholder="Masukkan nama lengkap" icon="bx bx-user"
                                       :value="old('name', $officer->name ?? '')" required />
                    </div>
                    <div class="col-md-3">
                        <x-input-field type="number" name="nik" label="NIK"
                                       placeholder="Masukkan NIK" icon="bx bx-id-card"
                                       :value="old('nik', $officer->officer->nik ?? '')" required/>
                    </div>
                    <div class="col-md-3">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span style="color: #dc3545 !important;">*</span></label>
                        <select required name="jenis_kelamin" class="form-select">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="Laki-laki" {{ old('jenis_kelamin', $officer->officer->jenis_kelamin ?? '') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin', $officer->officer->jenis_kelamin ?? '') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <x-input-field type="text" name="tempat_lahir" label="Tempat Lahir"
                                       placeholder="Masukkan tempat lahir" icon="bx bx-map"
                                       :value="old('tempat_lahir', $officer->officer->tempat_lahir ?? '')" />
                    </div>
                    <div class="col-md-3">
                        <x-input-field type="date" name="tanggal_lahir" label="Tanggal Lahir"
                                       icon=""
                                       :value="old('tanggal_lahir', $officer->officer->tanggal_lahir ?? '')" />
                    </div>
                    <div class="col-md-3">
                        <label for="agama" class="form-label">Agama <span style="color: #dc3545 !important;">*</span></label>
                        <select name="agama" id="agama" class="form-select text-uppercase" required>
                            <option value="">-- Pilih Agama --</option>
                            <option value="ISLAM" {{ old('agama', $officer->officer->agama ?? '') == 'ISLAM' ? 'selected' : '' }}>ISLAM</option>
                            <option value="KRISTEN PROTESTAN" {{ old('agama', $officer->officer->agama ?? '') == 'KRISTEN PROTESTAN' ? 'selected' : '' }}>KRISTEN PROTESTAN</option>
                            <option value="KRISTEN KATHOLIK" {{ old('agama', $officer->officer->agama ?? '') == 'KRISTEN KATHOLIK' ? 'selected' : '' }}>KRISTEN KATHOLIK</option>
                            <option value="HINDU" {{ old('agama', $officer->officer->agama ?? '') == 'HINDU' ? 'selected' : '' }}>HINDU</option>
                            <option value="BUDDHA" {{ old('agama', $officer->officer->agama ?? '') == 'BUDDHA' ? 'selected' : '' }}>BUDDHA</option>
                            <option value="KONGHUCU" {{ old('agama', $officer->officer->agama ?? '') == 'KONGHUCU' ? 'selected' : '' }}>KONGHUCU</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <x-input-field type="email" id="email_bottom" name="email" label="Email"
                                       placeholder="Email" icon="bx bx-envelope"
                                       :value="old('email', $officer->email ?? '')" required />
                    </div>
                    <div class="col-md-3">
                        <x-input-field type="number" name="no_hp" label="No. Telepon"
                                       placeholder="Masukkan Nomor Telepon" icon="bx bx-phone"
                                       :value="old('no_hp', $officer->officer->no_hp ?? '')" required/>
                    </div>
                    <div class="col-md-3">
                        <label for="alamat" class="form-label">Alamat <span style="color: #dc3545 !important;">*</span></label>
                        <textarea required name="alamat" id="alamat" class="form-control" rows="2">{{ old('alamat', $officer->officer->alamat ?? '') }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label for="image-dropzone" class="form-label">Upload Foto Guru</label>
                        <div class="dropzone" id="image-dropzone"></div>
                        <input type="hidden" name="image" id="image-hidden"
                               value="{{ old('image', $officer->image ?? '') }}">
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


                    {{-- Data Tambahan --}}
                    <div class="row mt-4">
                        <h5 class="card-title mb-0 mt-3">Data Tambahan Guru & Staff <span style="color: #dc3545 !important;">*</span><small class="text-muted">(Wajib diisi)</small></h5>
                        <p class="text-muted">Masukkan data tambahan guru & staff</p>
                        <hr>
                        <!-- Colom - 1 -->
                        <div class="col-md-3">
                            <div class="mb-4">
                                <label for="unit_id" class="form-label">Unit <span style="color: #dc3545 !important;">*</span></label>
                                <select name="unit_id" id="unit_id" class="form-select" data-choices data-choices-sorting-false required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" data-logo="{{ asset($u->image ?? 'images/default-logo.png') }}"
                                            {{ old('unit_id', $officer->officer->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <x-input-field type="text" name="va_guru" label="No. VA Guru & Staff"
                                           placeholder="Masukkan VA Guru & Staff" icon="bx bx-credit-card"
                                           :value="old('va_guru', $officer->va_guru ?? $officer->officer->va_guru ?? '')" required/>
                            <x-input-field type="text" name="no_kartu_rfid" label="No Kartu RFID"
                                           placeholder="Masukkan Nomor RFID" icon="bx bx-barcode"
                                           :value="old('no_kartu_rfid', $officer->officer->no_kartu_rfid ?? '')" />



                        </div>

                        <!-- Colom - 2 -->
                        <div class="col-md-3">
                            <div class="mb-4">
                                <label for="role_id" class="form-label">Role <span style="color: #dc3545 !important;">*</span></label>
                                <select name="role_id" class="form-select" data-choices data-choices-sorting-false required>
                                    <option value="">-- Pilih Role --</option>
                                    @foreach($roles as $r)
                                        <option value="{{ $r->id }}" {{ ($officer->officer->role_id ?? '') == $r->id ? 'selected' : '' }}>
                                            {{ $r->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <x-input-field type="text" name="bank" label="Bank"
                                           placeholder="Masukkan Nama Bank" icon="bx bx-bank"
                                           :value="old('bank', $officer->officer->bank ?? '')" />
                            <div class="mb-4">
                                <label for="jabatan_id" class="form-label">Jabatan</label>
                                <select name="jabatan_id" id="jabatan_id" class="form-select" data-choices data-choices-sorting-false>
                                    <option value="">-- Pilih Jabatan --</option>
                                    @foreach($positions as $position)
                                        <option value="{{ $position->id }}" {{ $jabatanSelected == $position->id ? 'selected' : '' }}>
                                            {{ strtoupper($position->positions_name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                        </div>

                        <!-- Colom - 3 -->
                        <div class="col-md-3">
                            <div class="mb-4" id="jurusan-wrapper">
                                <label for="jurusan" class="form-label">Jurusan</label>
                                <select name="jurusan[]" id="jurusan" class="form-control"  multiple>
                                    <option value="">--Pilih Jurusan--</option>
                                    @foreach ($jurusans as $jurusan)
                                        <option value="{{ $jurusan->id }}"
                                            {{ in_array($jurusan->id, old('jurusan', $officer->jurusan ?? [])) ? 'selected' : '' }}>
                                            {{ $jurusan->nama_jurusan }}
                                        </option>
                                    @endforeach
                                </select>


                            </div>
                            <x-input-field type="text" name="no_rekening" label="No Rekening"
                                           placeholder="Masukkan Nomor Rekening" icon="bx bx-bank"
                                           :value="old('no_rekening', $officer->officer->no_rekening ?? '')" />

                        </div>

                        <!-- Colom - 4 -->
                        <div class="col-md-3">

                            <div>
                                <label class="form-label">QR Code Preview</label>
                                <div id="qrcode" style="width:150px; height:150px; border:1px solid #ddd;"></div>
                            </div>


                        </div>

                        <div class="mt-3 text-end">
                            <button type="button" class="btn btn-primary" id="downloadQrBtn" style="display:none;">
                                Download QR Code
                            </button>
                            <button type="submit" class="btn btn-success">
                                {{ isset($officer) ? 'Update' : 'Simpan' }}
                            </button>
                            <a href="{{ url('officer/') }}" class="btn btn-secondary">Batal</a>
                        </div>
            </form>
        </div>
    </div>
@endsection

{{-- Scripts tetap sama, tinggal perhatikan preload image dan QR dari database --}}
@push('scripts')
    {{-- Semua JS Dropzone, QR Code, Swal, dsb seperti Blade sebelumnya --}}
@endpush


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#jurusan').select2({
                placeholder: "--Pilih Jurusan--", // Placeholder text
                allowClear: true                // Allows clearing the selected values
            });
        });
    </script>


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
        @if(isset($officer->officer) && $officer->officer->image)

        let mockFile = {
            name: "Current Image",
            size: 12345,
            type: 'image/jpeg', // bisa disesuaikan
            accepted: true
        };

        myDropzone.emit("addedfile", mockFile);
        myDropzone.emit("thumbnail", mockFile, "{{ asset($officer->officer->image) }}");
        myDropzone.emit("complete", mockFile);
        myDropzone.files.push(mockFile);
        @endif

    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const unitSelect = document.getElementById('unit_id');
            const jurusanWrapper = document.getElementById('jurusan-wrapper');
            const jurusanSelect = document.getElementById('jurusan');

            function toggleJurusan() {
                if (unitSelect.value) {
                    jurusanSelect.disabled = false;
                    jurusanSelect.classList.remove('disabled-select');
                    // aktifkan tampilan Choices
                    if (jurusanSelect.parentElement.querySelector('.choices')) {
                        jurusanSelect.parentElement.querySelector('.choices').classList.remove('disabled-select');
                    }
                } else {
                    jurusanSelect.disabled = true;
                    jurusanSelect.classList.add('disabled-select');
                    // nonaktifkan tampilan Choices
                    if (jurusanSelect.parentElement.querySelector('.choices')) {
                        jurusanSelect.parentElement.querySelector('.choices').classList.add('disabled-select');
                    }
                }
            }



            toggleJurusan(); // saat pertama load

            // Fetch jurusan saat unit berubah
            unitSelect.addEventListener('change', function() {
                toggleJurusan();
                const unitId = this.value;
                jurusanSelect.innerHTML = '<option value="">-- Pilih Jurusan --</option>'; // reset

                if (!unitId) return;

                fetch(`/siswa/jurusan/by-unit/${unitId}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(j => {
                            const opt = document.createElement('option');
                            opt.value = j.id;
                            opt.textContent = j.nama_jurusan;
                            jurusanSelect.appendChild(opt);
                        });
                    });
            });
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


    <script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.6.0/lib/qr-code-styling.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const vaInput = document.querySelector('input[name="va_guru"]');
            const qrContainer = document.getElementById('qrcode');
            const unitSelect = document.getElementById('unit_id');
            const downloadBtn = document.getElementById('downloadQrBtn');

            let defaultLogo = "{{ asset('images/default-logo.png') }}";
            vaInput.dispatchEvent(new Event('input'));

            const qrCode = new QRCodeStyling({
                width: 150,
                height: 150,
                data: vaInput.value || " ",
                image: defaultLogo,
                dotsOptions: { color: "#000", type: "rounded" },
                backgroundOptions: { color: "#fff" },
                imageOptions: {
                    crossOrigin: "anonymous",
                    margin: 4,
                    imageSize: 0.4,
                    hideBackgroundDots: true,
                    imageCornerRadius: 100
                }
            });

            qrCode.append(qrContainer);
            const qrTextInput = document.getElementById('qrcode-text');
            // Update QR code saat VA berubah
            vaInput.addEventListener('input', function() {
                const value = this.value || " ";
                qrCode.update({ data: value });
                qrTextInput.value = value; // simpan nilai QR ke input hidden
            });

            // Update QR code saat unit berubah
            function updateQr() {
                const selectedOption = unitSelect.options[unitSelect.selectedIndex];
                if (!selectedOption.value) {
                    // sembunyikan tombol kalau unit belum dipilih
                    downloadBtn.style.display = 'none';
                    qrCode.update({ image: defaultLogo });
                    return;
                }
                const logo = selectedOption.getAttribute('data-logo') || defaultLogo;
                qrCode.update({ image: logo });

                // tampilkan tombol download
                downloadBtn.style.display = 'inline-block';
            }

            unitSelect.addEventListener('change', updateQr);

            // Jalankan saat load halaman untuk cek default unit
            updateQr();

            // Tombol Download
            downloadBtn.addEventListener('click', function() {
                const fileName = 'qr-' + (vaInput.value || 'code') + '.png';
                qrCode.download({ name: fileName, extension: "png" });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nisInput = document.querySelector('input[name="nip"]');
            const nisnInput = document.querySelector('input[name="nik"]');
            const vaInput = document.querySelector('input[name="va_guru"]');

            // Jika user mengetik manual, kita tandai agar tidak auto-overwrite
            let manualVA = false;

            function normalize(num) {
                if (!num) return '';
                num = num.toString().trim();
                if (num.startsWith('0')) {
                    return num.substring(1, 9); // ambil digit ke-2 sampai ke-9
                }
                return num.substring(0, 8); // ambil 8 digit pertama
            }

            function generateVA() {
                // Kalau user sudah input manual, jangan auto-generate lagi
                if (manualVA) return;

                const nisPart = normalize(nisInput?.value || '');
                const nisnPart = normalize(nisnInput?.value || '');
                if (nisPart.length === 8 && nisnPart.length === 8) {
                    const va = nisnPart + nisPart;
                    vaInput.value = va;
                } else {
                    vaInput.value = '';
                }
                vaInput.dispatchEvent(new Event('input'));
            }

            if (nisInput && nisnInput && vaInput) {
                // kalau user mengetik di VA, berarti dia ingin manual
                vaInput.addEventListener('input', function() {
                    manualVA = vaInput.value.trim() !== '';
                });

                // generate hanya kalau user belum input manual
                nisInput.addEventListener('input', generateVA);
                nisnInput.addEventListener('input', generateVA);

                // saat pertama kali load
                generateVA();
            }
        });
    </script>

    @if(!isset($officer)) // Hanya saat create
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const emailBottom = document.querySelector('#email_bottom');
            const emailTop = document.querySelector('#email_top');

            if(emailBottom && emailTop){
                // update email atas saat mengetik di bawah
                emailBottom.addEventListener('input', function () {
                    emailTop.value = this.value;
                });

                // set email atas saat load halaman
                emailTop.value = emailBottom.value;
            }

            const nisnInput = document.getElementById('nip');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');

            if(nisnInput && usernameInput && passwordInput){
                nisnInput.addEventListener('input', function () {
                    const nisnValue = this.value.trim();
                    usernameInput.value = nisnValue;
                    passwordInput.value = nisnValue;
                });
            }
        });
    </script>

    @endif

@endpush
