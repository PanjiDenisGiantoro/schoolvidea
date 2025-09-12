@extends('layouts.app')
@section('title', isset($kelas) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data')

@section('content')
    @include('partials.page-title', [
        'title' => isset($kelas) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Petugas'
    ])

    <div class="card">
        <div class="card-body">
            <form id="userForm" action=""
{{--            {{ isset($kelas) ? route('officer.update', $kelas->id) : route('officer.store') }}"--}}
                  method="POST">
                @csrf
                @if(isset($kelas))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <h5 class="card-title mb-0 mt-3">Data access Petugas</h5>
                    <p class="text-muted">Masukkan data Petugas yang terkait dengan unit ini</p>
                    <hr>

                    <div class="col-md-3">
                        <x-input-field type="text" name="name" label="Nama Lengkap"
                                       placeholder="Masukkan nama lengkap" icon="bx bx-user"
                                       :value="old('name', $kelas->name ?? '')" required />


                    </div>

                    <div class="col-md-4">

                        <x-input-field type="email" name="email" label="Email"
                                       placeholder="Masukkan email" icon="bx bx-envelope"
                                       :value="old('email', $kelas->email ?? '')" required
                        />
                    </div>

                    <div class="col-md-4">

                        <x-input-field type="password" name="password" label="Password"
                                       @if(empty($kelas))
                                           placeholder="Masukkan Password"
                                       @else
                                           placeholder="Kosongkan jika tidak ingin mengubah password"
                                       @endif
                                            icon="bx bx-lock"
                                       :value="old('name')" required />
                    </div>
                </div>
                <div class="row g-3 mt-4">
                    <h5 class="card-title mb-0 mt-3">Data Lengkap Petugas</h5>
                    <p class="text-muted">Masukkan data Lengkap Petugas</p>
                    <hr>

                    <div class="col-md-3">
                        <x-input-field type="text" name="nip" label="NIP"
                                       placeholder="Masukkan NIP" icon="bx bx-id-card"
                                       :value="old('nip', $kelas->officer->nip ?? '')" />

                        <x-input-field type="text" name="tempat_lahir" label="Tempat Lahir"
                                       placeholder="Masukkan tempat lahir" icon="bx bx-map"
                                       :value="old('tempat_lahir', $kelas->officer->tempat_lahir ?? '')" />

                        <div class="mb-3">
                            <label for="role_id" class="form-label">Role Petugas</label>
                            <select name="role_id" id="choices-single-no-sorting" data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}"
                                        {{ ($kelas->role_id ?? '') == $r->id ? 'selected' : '' }}>
                                        {{ $r->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="col-md-4">
                        <x-input-field type="text" name="no_hp" label="No. HP"
                                       placeholder="Masukkan nomor HP" icon="bx bx-phone"
                                       :value="old('no_hp', $kelas->officer->no_hp ?? '')" />

                        <x-input-field type="text" name="image" label="Foto (URL/Path)"
                                       placeholder="Masukkan URL gambar" icon="bx bx-image"
                                       :value="old('image', $kelas->officer->iamge ?? '')" />


                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select name="unit_id" class="form-select"  data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Unit --</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('unit_id', $kelas->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                            <select name="tahun_ajaran_id"  id="choices-single-no-sorting"  data-choices data-choices-sorting-false">
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach($tahun_ajaran as $t)
                                    <option value="{{ $t->id }}"
                                        {{ old('tahun_ajaran_id', $kelas->tahun_ajaran_id ?? ($tahun_ajaran_selected->id ?? '')) == $t->id ? 'selected' : '' }}>
                                        {{ $t->tahun_ajaran ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($kelas) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ url('user/') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
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
@endpush
