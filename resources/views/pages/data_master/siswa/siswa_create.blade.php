@extends('layouts.app')
@section('title', isset($siswa) ? (isset($show) && $show ? 'Lihat Siswa' : 'Edit Siswa') : 'Tambah Siswa')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

@section('content')
    @include('partials.page-title', [
        'title' => isset($siswa) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Siswa',
    ])

    <div class="card">
        <div class="card-body">
            <form id="siswaForm" action="{{ isset($siswa) ? route('siswa.update', $siswa->id) : route('siswa.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @if (isset($siswa))
                    @method('PUT')
                @endif

                <div class="row">
                    <h5 class="card-title mb-0 mt-3">Data Akun Siswa</h5>
                    <p class="text-muted">Masukkan data login untuk siswa</p>
                    <hr>
                    <div class="col-md-4">
                        <x-input-field type="email" id="email_top" name="" label="Email" placeholder="Email"
                            icon="bx bx-envelope" :value="old('email', $siswa->user->email ?? '')" :disabled="(isset($siswa) && isset($show) && $show) || !isset($siswa)" />
                    </div>
                    <div class="col-md-4">
                        <x-input-field type="text" id="username" name="username" label="Username/NISN"
                            placeholder="Username/NISN" icon="bx bx-user" :value="old('username', $siswa->user->username ?? '')" :disabled="(isset($siswa) && isset($show) && $show) || !isset($siswa)" />
                    </div>
                    <div class="col-md-4">

                        <x-input-field type="text" id="password" name="password" label="Password" :placeholder="isset($siswa) ? 'Kosongkan jika tidak ingin mengubah password' : 'Masukkan Password'"
                            icon="bx bx-lock" :value="old('password')" :disabled="(isset($siswa) && isset($show) && $show) || !isset($siswa)" />
                    </div>
                </div>

                <div class="row g-3 mt-4">
                    <h5 class="card-title mb-0 mt-3">Data Lengkap Siswa <span
                            style="color: #dc3545 !important;">*</span><small class="text-muted">(Wajib diisi)</small></h5>
                    <p class="text-muted">Masukkan data lengkap siswa</p>
                    <hr>
                    <div class="col-md-3">
                        <x-input-field type="text" id="nisn" name="nisn" label="NISN"
                            placeholder="Masukkan NISN" icon="bx bx-id-card" :value="old('nisn', $siswa?->nisn ?? '')"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57" required />
                    </div>
                    <div class="col-md-3">
                        <x-input-field type="text" id="nis" name="nis" label="NIS"
                            placeholder="Masukkan NIS" icon="bx bx-id-card" :value="old('nis', $siswa?->nis ?? '')"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57" required />
                    </div>
                    <div class="col-md-3">
                        <x-input-field type="text" id="name" name="name" label="Nama Lengkap"
                            placeholder="Masukkan nama lengkap" icon="bx bx-user" :value="old('name', $siswa->user->name ?? '')" required />
                    </div>
                    <div class="col-md-3">
                        <x-input-field type="number" id="nik" name="nik" label="NIK"
                            placeholder="Masukkan NIK" icon="bx bx-id-card" :value="old('nik', $siswa?->nik ?? '')"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57" required />
                    </div>
                    <div class="col-md-3">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span
                                style="color: #dc3545 !important;">*</span></label>
                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="L"
                                {{ old('jenis_kelamin', $siswa?->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki
                            </option>
                            <option value="P"
                                {{ old('jenis_kelamin', $siswa?->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <x-input-field type="text" id="tempat_lahir" name="tempat_lahir" label="Tempat Lahir"
                            placeholder="Masukkan tempat lahir" icon="bx bx-map" :value="old('tempat_lahir', $siswa?->tempat_lahir ?? '')" />
                    </div>
                    <div class="col-md-3">
                        <x-input-field type="date" id="tanggal_lahir" name="tanggal_lahir" label="Tanggal Lahir"
                            placeholder="Masukkan tanggal lahir" :value="old('tanggal_lahir', $siswa?->tanggal_lahir ?? '')" />
                    </div>
                    <div class="col-md-3">
                        <label for="agama" class="form-label">Agama <span
                                style="color: #dc3545 !important;">*</span></label>
                        <select name="agama" id="agama" class="form-select text-uppercase" required>
                            <option value="">-- Pilih Agama --</option>
                            <option value="ISLAM" {{ old('agama', $siswa?->agama ?? '') == 'ISLAM' ? 'selected' : '' }}>
                                ISLAM</option>
                            <option value="PROTESTAN"
                                {{ old('agama', $siswa?->agama ?? '') == 'PROTESTAN' ? 'selected' : '' }}>
                                PROTESTAN</option>
                            <option value="KATHOLIK"
                                {{ old('agama', $siswa?->agama ?? '') == 'KATHOLIK' ? 'selected' : '' }}>
                                KATHOLIK</option>
                            <option value="HINDU" {{ old('agama', $siswa?->agama ?? '') == 'HINDU' ? 'selected' : '' }}>
                                HINDU</option>
                            <option value="BUDDHA" {{ old('agama', $siswa?->agama ?? '') == 'BUDHHA' ? 'selected' : '' }}>
                                BUDDHA</option>
                            <option value="KONGHUCU"
                                {{ old('agama', $siswa?->agama ?? '') == 'KONGHUCU' ? 'selected' : '' }}>KONGHUCU</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <x-input-field type="text" id="no_hp" name="no_hp" label="No. Telepon Siswa"
                            placeholder="Masukkan nomor telepon" icon="bx bx-phone" :value="old('no_hp', $siswa?->no_hp ?? '')"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57" />
                    </div>

                    <div class="col-md-3">
                        <x-input-field type="text" id="no_hp_ortu" name="no_hp_ortu" label="No. Telepon Orang Tua"
                            placeholder="Masukkan nomor telepon" icon="bx bx-phone" :value="old('no_hp_ortu', $siswa?->no_hp_ortu ?? '')"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57" required />
                    </div>

                    <div class="col-md-3">
                        <x-input-field type="email" id="email_bottom" name="email" label="Email"
                            placeholder="Masukkan email" icon="bx bx-envelope" :value="old('email', $siswa->user->email ?? '')" required />
                    </div>

                    <div class="col-md-3">
                        <x-input-field type="text" id="nama_ortu" name="nama_ortu" label="Nama Orang Tua"
                            placeholder="Masukkan nama orang tua" icon="bx bx-user" :value="old('nama_ortu', $siswa?->nama_ortu ?? '')" required />
                    </div>
                    <div class="col-md-3 mt-4">
                        <label for="status" class="form-label">Status <span
                                style="color: #dc3545 !important;">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="1" {{ old('status', $siswa?->status ?? '') == 1 ? 'selected' : '' }}>Aktif
                            </option>
                            <option value="0" {{ old('status', $siswa?->status ?? '') == 0 ? 'selected' : '' }}>Non
                                Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="alamat" class="form-label">Alamat <span
                                style="color: #dc3545 !important;">*</span></label>
                        <textarea name="alamat" id="alamat" class="form-control" rows="2" required>{{ old('alamat', $siswa->alamat ?? '') }}</textarea>
                    </div>

                    <div class="col-md-3">
                        <label for="image-dropzone" class="form-label">Upload Foto Siswa</label>
                        <div class="dropzone" id="image-dropzone"></div>
                        <input type="hidden" name="image" id="image-hidden"
                            value="{{ old('image', $siswa?->image ?? '') }}">
                        <small class="text-muted">Format: JPG, PNG | Max: 1MB</small>
                        <div>
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
                </div>

                <div class="row g-3 mt-4">
                    <h5 class="card-title mb-0 mt-3">Data Tambahan Siswa <span
                            style="color: #dc3545 !important;">*</span><small class="text-muted">(Wajib diisi)</small>
                    </h5>
                    <p class="text-muted">Masukkan data tambahan siswa</p>
                    <hr>
                    <!-- Colom - 1 -->
                    <div class="col-md-3">
                        <div class="mb-4">
                            <label for="unit_id" class="form-label">Unit <span
                                    style="color: #dc3545 !important;">*</span></label>
                            <select name="unit_id" id="unit_id" class="form-select" data-choices
                                data-choices-sorting-false required @if (isset($show) && $show) disabled @endif>
                                <option value="">-- Pilih Unit --</option>
                                @foreach ($units as $u)
                                    <option value="{{ $u->id }}"
                                        data-logo="{{ asset($u->image ?? 'images/default-logo.png') }}"
                                        {{ old('unit_id', $siswa->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-field type="text" id="va_siswa" name="va_siswa" label="VA Siswa"
                            placeholder="Masukkan VA Siswa" icon="bx bx-credit-card" :value="old('va_siswa', $siswa?->va_siswa ?? '')"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57" required />

                            <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran <span
                                    style="color: #dc3545 !important;">*</span></label>
                            <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" data-choices
                                data-choices-sorting-false required @if (isset($show) && $show) disabled @endif>
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach ($tahunajaran as $ta)
                                    <option value="{{ $ta->id }}"
                                        {{ old('tahun_ajaran_id', $siswa?->tahun_ajaran_id ?? '') == $ta->id ? 'selected' : '' }}>
                                        {{ $ta->tahun_ajaran }} {{ $ta->semester }}
                                    </option>
                                @endforeach
                            </select>
                    </div>
                    <!-- Colom - 2 -->
                    <div class="col-md-3">

                        <div class="mb-4">
                            <label for="kelas_id" class="form-label">Kelas <span
                                    style="color: #dc3545 !important;">*</span></label>

                            <select name="kelas_id" id="kelas_id" class="form-select" data-choices
                                data-choices-sorting-false required @if (isset($show) && $show) disabled @endif>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}"
                                        {{ old('kelas_id', $siswa?->kelas_id ?? '') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-field type="text" id="bank" name="bank" label="Bank"
                            placeholder="Masukkan nama bank" icon="bx bx-bank" :value="old('bank', $siswa?->bank ?? '')" />
                    </div>
                    <!-- Colom - 3 -->
                    <div class="col-md-3">
                        <x-input-field type="text" id="rfid_no" name="rfid_no" label="RFID"
                            placeholder="Masukkan RFID" :value="old('rfid_no', $siswa?->rfid_no ?? '')" />
                        <x-input-field type="text" name="no_rekening" label="No Rekening Siswa"
                            placeholder="Masukkan nomor rekening siswa" icon="bx bx-credit-card" :value="old('no_rekening', $siswa?->no_rekening ?? '')"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57" />
                    </div>
                    <!-- Colom - 4 -->
                    <div class="col-md-3">

                        <div class="mb-4">
                            <label for="jurusan" class="form-label">Jurusan</label>
                            @if (isset($jurusans))
                                <select name="jurusan_id" id="jurusan" class="form-select">
                                    <option value="">--Pilih Jurusan--</option>
                                    @foreach ($jurusans as $jurusan)
                                        <option value="{{ $jurusan->id }}"
                                            {{ old('jurusan_id', $siswa->jurusan_id ?? '') == $jurusan->id ? 'selected' : '' }}>
                                            {{ $jurusan->nama_jurusan }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="text-start">
                            <label class="form-label d-block">QR Code Siswa</label>
                            @if (isset($show) && $show)
                                @if (!empty($siswa->qrcode))
                                    <img src="{{ asset('storage/' . $siswa->qrcode) }}" alt="QR Siswa"
                                        class="img-fluid rounded border" width="150">
                                    <br>
                                    <a href="{{ asset('storage/' . $siswa->qrcode) }}" download
                                        class="btn btn-sm btn-primary mt-2">
                                        <i class="bx bx-download"></i> Download QR
                                    </a>
                                @else
                                    <p class="text-muted">QR belum tersedia</p>
                                @endif
                            @else
                                <div id="qrcode" style="width:150px; height:150px; border:1px solid #ddd;"></div>
                                <input type="hidden" name="qrcode" id="qrcode-text"
                                    value="{{ old('qrcode', $siswa?->qrcode ?? '') }}">

                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="button" class="btn btn-primary" id="downloadQrBtn" style="display:none;">
                        Download QR Code
                    </button>
                    <button type="submit" class="btn btn-success">
                        {{ isset($siswa) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ route('siswa.index') }}" class="btn btn-secondary">Batal</a>

                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>

    <script>
        @if (isset($show) && $show)
            document.addEventListener('DOMContentLoaded', function() {
                const formElements = document.querySelectorAll(
                    '#siswaForm input, #siswaForm textarea, #siswaForm select, #siswaForm button[type="submit"]'
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

        Dropzone.options.myDropzone = {
            paramName: "file",
            maxFilesize: 1,
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            init: function() {
                this.on("addedfile", function(file) {
                    file.previewElement.addEventListener("click", function() {
                        let imgSrc = file.dataURL;
                        document.getElementById("previewImage").src = imgSrc;
                        var modal = new bootstrap.Modal(document.getElementById("imageModal"));
                        modal.show();
                    });
                });
            }
        };

        let myDropzone = new Dropzone("#image-dropzone", {
            url: "{{ route('siswa.upload') }}",
            paramName: "file",
            maxFiles: 1,
            maxFilesize: 1,
            acceptedFiles: ".jpg,.jpeg,.png",
            addRemoveLinks: true,
            thumbnailWidth: 200,
            thumbnailHeight: 200,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function(file, response) {
                document.querySelector("#image-hidden").value = response.filepath;
            },
            removedfile: function(file) {
                file.previewElement.remove();
                document.querySelector("#image-hidden").value = "";
            }
        });

        @if (isset($siswa) && $siswa?->image)
            let mockFile = {
                name: "Current Image",
                size: 12345,
                type: 'image/jpeg',
                accepted: true
            };
            myDropzone.emit("addedfile", mockFile);
            myDropzone.emit("thumbnail", mockFile, "{{ asset($siswa->image) }}");
            myDropzone.emit("complete", mockFile);
            myDropzone.files.push(mockFile);
        @endif
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('siswaForm');

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
                        Swal.close();
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
        document.addEventListener('DOMContentLoaded', function() {
            const vaInput = document.querySelector('input[name="va_siswa"]');
            const qrContainer = document.getElementById('qrcode');
            const unitSelect = document.getElementById('unit_id');
            const downloadBtn = document.getElementById('downloadQrBtn');

            let defaultLogo = "{{ asset('images/default-logo.png') }}";
            vaInput.dispatchEvent(new Event('input'));

            console.log("QR container:", qrContainer);
            console.log("VA:", vaInput?.value);

            if (!qrContainer || !vaInput) {
                console.warn("❌ Tidak ditemukan elemen target atau input VA");
                return;
            }

            const qrCode = new QRCodeStyling({
                width: 150,
                height: 150,
                data: vaInput.value || " ",
                image: defaultLogo,
                dotsOptions: {
                    color: "#000000",
                    type: "square"
                },
                backgroundOptions: {
                    color: "#eeeeee"
                },
                imageOptions: {
                    crossOrigin: "anonymous",
                    margin: 4,
                    imageSize: 0.4,
                    hideBackgroundDots: true,
                    imageCornerRadius: 100
                }
            });

            console.log("QR instance:", qrCode);
            if (qrContainer) {
                qrCode.append(qrContainer);
                console.log("✅ QR appended successfully!");
            } else {
                console.error("❌ QR container not found in DOM!");
            }
            // 🟢 bagian penting: render QR ke halaman
            setTimeout(() => {
                try {
                    qrCode.append(qrContainer); // 👉 tempelkan QR ke <div id="qrcode">
                    qrCode.update(); // 👉 paksa render (penting di beberapa versi)
                    console.log("✅ QR appended successfully!");
                } catch (err) {
                    console.error("❌ QR append error:", err);
                }
            }, 200);



            qrCode.append(qrContainer);
            const qrTextInput = document.getElementById('qrcode-text');
            // Update QR code saat VA berubah
            vaInput.addEventListener('input', function() {
                const value = this.value || " ";
                qrCode.update({
                    data: value
                });
                qrTextInput.value = value; // simpan nilai QR ke input hidden
            });

            // Update QR code saat unit berubah
            function updateQr() {
                const selectedOption = unitSelect.options[unitSelect.selectedIndex];
                if (!selectedOption.value) {
                    // sembunyikan tombol kalau unit belum dipilih
                    downloadBtn.style.display = 'none';
                    qrCode.update({
                        image: defaultLogo
                    });
                    return;
                }
                const logo = selectedOption.getAttribute('data-logo') || defaultLogo;
                qrCode.update({
                    image: logo
                });

                // tampilkan tombol download
                downloadBtn.style.display = 'inline-block';
            }

            unitSelect.addEventListener('change', updateQr);

            // Jalankan saat load halaman untuk cek default unit
            updateQr();

            // Tombol Download
            downloadBtn.addEventListener('click', function() {
                const fileName = 'qr-' + (vaInput.value || 'code') + '.png';
                qrCode.download({
                    name: fileName,
                    extension: "png"
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nisInput = document.querySelector('input[name="nis"]');
            const nisnInput = document.querySelector('input[name="nisn"]');
            const vaInput = document.querySelector('input[name="va_siswa"]');

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

    @if (!isset($officer))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const emailBottom = document.querySelector('#email_bottom');
                const emailTop = document.querySelector('#email_top');

                if (emailBottom && emailTop) {
                    // update email atas saat mengetik di bawah
                    emailBottom.addEventListener('input', function() {
                        emailTop.value = this.value;
                    });

                    // set email atas saat load halaman
                    emailTop.value = emailBottom.value;
                }

                const nisnInput = document.getElementById('nisn');
                const usernameInput = document.getElementById('username');
                const passwordInput = document.getElementById('password');

                if (nisnInput && usernameInput && passwordInput) {
                    nisnInput.addEventListener('input', function() {
                        const nisnValue = this.value.trim();
                        usernameInput.value = nisnValue;
                        passwordInput.value = nisnValue;
                    });
                }
            });
        </script>
    @endif
@endpush
