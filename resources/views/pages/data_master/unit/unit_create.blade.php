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
                                       placeholder="Auto Generate" icon="bx bx-code"
                                       style="background-color: #e9ecef; color: #6c757d;"
                                       :value="old('code', $unit->code ?? '')" readonly  />

                        <x-input-field type="text" name="image" label="Image (URL/Path)"
                                       placeholder="Masukkan URL gambar" icon="bx bx-image"
                                       :value="old('image', $unit->image ?? '')" />

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
</script>
@endpush
