@extends('layouts.app')
@section('title', isset($unit) ? (isset($show) && $show ? 'Lihat Lembaga Unit' : 'Edit Lembaga Unit') : 'Tambah Lembaga Unit')

@section('content')
    @include('partials.page-title', [
        'title' => isset($unit) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Unit'
    ])
    <div class="card">
        <div class="card-body">
            <form id="unitForm" action="{{ isset($unit) ? route('unit.update', $unit->id) : route('unit.store') }}"
                  method="POST">
                @csrf
                @if(isset($unit))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-12" >
{{--                        title--}}
                        <h5 class="card-title mb-0">Pilih Yayasan</h5>
                        <p class="text-muted">Pilih yayasan yang terkait dengan unit ini</p>
                        <hr>
                        <div class="mb-3">
                            <label for="yayasan_id" class="form-label">Yayasan</label>
                            <select name="yayasan_id"  id="choices-single-no-sorting"  data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Yayasan --</option>
                                @foreach($yayasan as $y)
                                    <option value="{{ $y->id }}"
                                        {{ old('yayasan_id', $unit->yayasan_id ?? '') == $y->id ? 'selected' : '' }}>
                                        {{ $y->nama_yayasan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <h5 class="card-title mb-0 mt-3">Data Unit</h5>
                    <p class="text-muted">Masukkan data unit</p>
                    <hr>
                    <div class="col-md-4">

                        <x-input-field type="text" name="nama_unit" label="Nama Unit"
                                       placeholder="Masukkan nama unit" icon="bx bx-building"
                                       :value="old('nama_unit', $unit->nama_unit ?? '')" required />

                        <x-input-field type="text" name="code" label="Code"
                                       placeholder="Kosongkan untuk auto generate" icon="bx bx-code"
{{--                                       style="background-color: #e9ecef; color: #6c757d;"--}}
                                       :value="old('code', $unit->code ?? '')"   />

                        <div class="mb-3">
                            <label for="image-dropzone" class="form-label">Upload Gambar</label>
                            <div class="dropzone" id="image-dropzone"></div>
                            <input type="hidden" name="image" id="image-hidden"
                                   value="{{ old('image', $unit->image ?? '') }}">
                            <small class="text-muted">Format: JPG, PNG, GIF | Max: 2MB</small>
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
                        <div class="mb-2">
                            <label for="status" class="form-label">Status Unit </label>

                            <select name="status" id="status" class="form-select">
                                <option value="1" {{ old('status', $unit->status ?? '') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status', $unit->status ?? '') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="no_hp" label="No. HP"
                                       placeholder="Masukkan nomor HP" icon="bx bx-phone"
                                       :value="old('no_hp', $unit->no_hp ?? '')" />

                        <x-input-field type="email" name="email" label="Email"
                                       placeholder="Masukkan email" icon="bx bx-envelope"
                                       :value="old('email', $unit->email ?? '')"  />

                        <x-input-field type="text" name="website" label="Website"
                                       placeholder="Masukkan website" icon="bx bx-globe"
                                       :value="old('website', $unit->website ?? '')" />

                        <label for="tipe_unit_id" class="form-label">Tipe Unit</label>
                        <select name="tipe_unit_id"  id="choices-single-no-sorting"  data-choices data-choices-sorting-false>
                            <option value="">-- Pilih Tipe Unit --</option>
                            @foreach($tipeunit as $tipe)
                                <option value="{{ $tipe->id }}"
                                    {{ old('tipe_unit_id', $unit->tipe_unit_id ?? '') == $tipe->id ? 'selected' : '' }}>
                                    {{ $tipe->nama_tipe_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamat" class="form-control" rows="6"
                                      placeholder="Masukkan alamat">{{ old('alamat', $unit->alamat ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($unit) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ url('lembagaunit/') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')

<script>
    @if(isset($show) && $show)
    document.addEventListener('DOMContentLoaded', function() {
        const formElements = document.querySelectorAll('#unitForm input, #unitForm textarea, #unitForm select, #unitForm button[type="submit"]');

        formElements.forEach(el => {
            el.disabled = true;
            if(el.type === 'submit'){
                el.style.display = 'none';
            }
        });
    });
    @endif
</script> <script>
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
        url: "{{ route('unit.upload') }}",
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
    @if(isset($unit) && $unit->image)

    let mockFile = {
        name: "Current Image",
        size: 12345,
        type: 'image/jpeg', // bisa disesuaikan
        accepted: true
    };

    myDropzone.emit("addedfile", mockFile);
    myDropzone.emit("thumbnail", mockFile, "{{ asset($unit->image) }}");
    myDropzone.emit("complete", mockFile);
    myDropzone.files.push(mockFile);
    @endif

</script>

@endpush
