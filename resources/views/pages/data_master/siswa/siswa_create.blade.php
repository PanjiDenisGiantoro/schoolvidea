@extends('layouts.app')
@section('title', isset($siswa) ? (isset($show) && $show ? 'Lihat Siswa' : 'Edit Siswa') : 'Tambah Siswa')

@section('content')
    @include('partials.page-title', [
        'title' => isset($siswa) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Siswa'
    ])

    <div class="card">
        <div class="card-body">
            <form id="siswaForm"
                  action="{{ isset($siswa) ? route('siswa.update', $siswa->id) : route('siswa.store') }}"
                  method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($siswa))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <h5 class="card-title mb-0 mt-3">Data Akun Siswa</h5>
                    <p class="text-muted">Masukkan data login untuk siswa</p>
                    <hr>

                    <div class="col-md-4">
                        <x-input-field type="text" name="name" label="Nama Lengkap"
                                       placeholder="Masukkan nama lengkap"
                                       icon="bx bx-user"
                                       :value="old('name', $siswa->user->name ?? '')"
                                       required />
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="email" name="email" label="Email"
                                       placeholder="Masukkan email"
                                       icon="bx bx-envelope"
                                       :value="old('email', $siswa->user->email ?? '')"
                                       required />
                    </div>

                    <div class="col-md-4">
                        <label for="password" class="form-label">Password</label>
                        <x-input-field type="password" name="password" label="Password"
                                       :placeholder="isset($siswa) ? 'Kosongkan jika tidak ingin mengubah password' : 'Masukkan Password'"
                                       icon="bx bx-lock"
                                       :value="''"
                            {{ isset($siswa) ? '' : 'required' }} />
                        @if(isset($siswa))
                            <input type="password" name="password" class="form-control mt-2"
                                   placeholder="Konfirmasi Password (kosongkan jika tidak ganti)">
                        @else
                            <input type="password" name="password" class="form-control mt-2"
                                   placeholder="Konfirmasi Password" required>
                        @endif
                    </div>
                </div>
                <div class="row g-3 mt-4">
                    <h5 class="card-title mb-0 mt-3">Data Lengkap Siswa</h5>
                    <p class="text-muted">Masukkan data lengkap siswa</p>
                    <hr>

                    <div class="col-md-3">
                        <x-input-field type="text" name="nisn" label="NISN"
                                       placeholder="Masukkan NISN"
                                       icon="bx bx-id-card"
                                       :value="old('nisn', $siswa->nisn ?? '')" required />

                        <x-input-field type="text" name="tempat_lahir" label="Tempat Lahir"
                                       placeholder="Masukkan tempat lahir"
                                       icon="bx bx-map"
                                       :value="old('tempat_lahir', $siswa->tempat_lahir ?? '')" />

                        <div class="mb-3">
                            <label for="image-dropzone" class="form-label">Upload Gambar</label>
                            <div class="dropzone" id="image-dropzone"></div>
                            <input type="hidden" name="image" id="image-hidden"
                                   value="{{ old('image', $siswa->image ?? '') }}">
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

                    <div class="col-md-3">
                        <x-input-field type="text" name="no_hp" label="No. HP"
                                       placeholder="Masukkan nomor HP"
                                       icon="bx bx-phone"
                                       :value="old('no_hp', $siswa->no_hp ?? '')" />

                        <x-input-field type="text" name="va_siswa" label="VA Siswa"
                                       placeholder="Masukkan VA Petugas" icon="bx bx-credit-card"
                                       :value="old('va_siswa', $siswa->va_siswa ?? '')" />
                        <x-input-field type="text" name="rfid_no" label="RFID"
                                       placeholder="Masukkan tanggal lahir"
                                       :value="old('rfid_no', $siswa->rfid_no ?? '')" />
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="kelas_id" class="form-label">Kelas</label>
                            <select name="kelas_id" id="kelas_id" class="form-select" data-choices data-choices-sorting-false required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}"
                                        {{ old('kelas_id', $siswa->kelas_id ?? '') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>

                        <x-input-field type="date" name="tanggal_lahir" label="Tanggal Lahir"
                                       placeholder="Masukkan tanggal lahir"
                                       :value="old('tanggal_lahir', $siswa->tanggal_lahir ?? '')" />

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="1" {{ old('status', $siswa->status ?? '') == 1 ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ old('status', $siswa->status ?? '') == 0 ? 'selected' : '' }}>Non Aktif</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-select" data-choices data-choices-sorting-false required>
                                <option value="">-- Pilih Unit --</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('unit_id', $siswa->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>

                            <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                            <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" data-choices data-choices-sorting-false required>
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach($tahun_ajaran as $t)
                                    <option value="{{ $t->id }}"
                                        {{ old('tahun_ajaran_id', $siswa->tahun_ajaran_id ?? ($tahun_ajaran_selected->id ?? '')) == $t->id ? 'selected' : '' }}>
                                        {{ $t->tahun_ajaran }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                    </div>
                </div>
                <div class="row g-3 mt-4">
                    <h5 class="card-title mb-0 mt-3">Data Tambahan Siswa</h5>
                    <p class="text-muted">Masukkan data tambahan siswa</p>
                    <hr>

                    <div class="col-md-3">
                        <x-input-field type="text" name="nis" label="NIS"
                                       placeholder="Masukkan NIS"
                                       icon="bx bx-id-card"
                                       :value="old('nis', $siswa->nis ?? '')" />

                        <x-input-field type="text" name="nik" label="NIK"
                                       placeholder="Masukkan NIK"
                                       icon="bx bx-id-card"
                                       :value="old('nik', $siswa->nik ?? '')" />
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="agama" class="form-label">Agama</label>
                            <select name="agama" id="agama" class="form-select" required>
                                <option value="">-- Pilih Agama --</option>
                                <option value="Islam" {{ old('agama', $siswa->agama ?? '') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('agama', $siswa->agama ?? '') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="Katolik" {{ old('agama', $siswa->agama ?? '') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                <option value="Hindu" {{ old('agama', $siswa->agama ?? '') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('agama', $siswa->agama ?? '') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="Konghucu" {{ old('agama', $siswa->agama ?? '') == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-input-field type="text" name="no_hp_ortu" label="No. HP Orang Tua"
                                       placeholder="Masukkan nomor HP orang tua"
                                       icon="bx bx-phone"
                                       :value="old('no_hp_ortu', $siswa->no_hp_ortu ?? '')" />

                        <x-input-field type="text" name="nama_ortu" label="Nama Orang Tua"
                                       placeholder="Masukkan nama orang tua"
                                       icon="bx bx-user"
                                       :value="old('nama_ortu', $siswa->nama_ortu ?? '')" />
                    </div>

                    <div class="col-md-3">
                        <x-input-field type="text" name="bank" label="Bank"
                                       placeholder="Masukkan nama bank"
                                       icon="bx bx-bank"
                                       :value="old('bank', $siswa->bank ?? '')" />

                        <x-input-field type="text" name="no_rekening" label="No Rekening"
                                       placeholder="Masukkan nomor rekening"
                                       icon="bx bx-credit-card"
                                       :value="old('no_rekening', $siswa->no_rekening ?? '')" />

                        <x-input-field type="text" name="qrcode" label="QR Code"
                                       placeholder="Masukkan kode / path QR"
                                       icon="bx bx-qr"
                                       :value="old('qrcode', $siswa->qrcode ?? '')" />
                    </div>
                </div>

                <div class="mt-3 text-end">
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
    <script>
        @if(isset($show) && $show)
        document.addEventListener('DOMContentLoaded', function() {
            const formElements = document.querySelectorAll('#siswaForm input, #siswaForm textarea, #siswaForm select, #siswaForm button[type="submit"]');
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
            url: "{{ route('siswa.upload') }}",
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
        @if(isset($siswa) && $siswa->image)

        let mockFile = {
            name: "Current Image",
            size: 12345,
            type: 'image/jpeg', // bisa disesuaikan
            accepted: true
        };

        myDropzone.emit("addedfile", mockFile);
        myDropzone.emit("thumbnail", mockFile, "{{ asset($siswa->image) }}");
        myDropzone.emit("complete", mockFile);
        myDropzone.files.push(mockFile);
        @endif

    </script>
@endpush
