@extends('layouts.app')
@section('title', isset($lembagaunit) ? (isset($show) && $show ? 'Lihat Lembaga Unit' : 'Edit Lembaga Unit') : 'Tambah Lembaga Unit')

@section('content')
    @include('partials.page-title', [
        'title' => isset($lembagaunit) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Lembaga Unit'
    ])
    <div class="card">
        <div class="card-body">
            <form id="lembagaForm" action="{{ isset($lembagaunit) ? route('lembagaunit.update', $lembagaunit->id) : route('lembagaunit.store') }}"
                  method="POST">
                @csrf
                @if(isset($lembagaunit))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-4">
                        <x-input-field type="text" name="nama_yayasan" label="Nama Yayasan"
                                       placeholder="Masukkan nama yayasan" icon="bx bx-building"
                                       :value="old('nama_yayasan', $lembagaunit->nama_yayasan ?? '')" required />

                        <x-input-field type="text" name="central_code" label="Central Code"
                                       placeholder="Kosongkan untuk auto generate"
                                       icon="bx bx-code"
                                       :value="old('central_code', $lembagaunit->central_code ?? '')"
                        />


                        <x-input-field type="text" name="image" label="Image (URL/Path)"
                                       placeholder="Masukkan URL gambar" icon="bx bx-image"
                                       :value="old('image', $lembagaunit->image ?? '')" />
                        <div class="mb-2">
                            <label for="status" class="form-label">Status Unit </label>

                            <select name="status" id="status" class="form-select">
                                <option value="1" {{ old('status', $lembagaunit->status ?? '') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status', $lembagaunit->status ?? '') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="no_hp" label="No. HP"
                                       placeholder="Masukkan nomor HP" icon="bx bx-phone"
                                       :value="old('no_hp', $lembagaunit->no_hp ?? '')" />

                        <x-input-field type="email" name="email" label="Email"
                                       placeholder="Masukkan email" icon="bx bx-envelope"
                                       :value="old('email', $lembagaunit->email ?? '')"  />

                        <x-input-field type="text" name="website" label="Website"
                                       placeholder="Masukkan website" icon="bx bx-globe"
                                       :value="old('website', $lembagaunit->website ?? '')" />


                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamat" class="form-control" rows="6"
                                      placeholder="Masukkan alamat">{{ old('alamat', $lembagaunit->alamat ?? '') }}</textarea>
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

<script>
    @if(isset($show) && $show)
    document.addEventListener('DOMContentLoaded', function() {
        const formElements = document.querySelectorAll('#lembagaForm input, #lembagaForm textarea, #lembagaForm select, #lembagaForm button[type="submit"]');

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
