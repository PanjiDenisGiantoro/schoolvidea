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
                  method="POST">
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

                    </div>

                    <div class="col-md-3">
                        <x-input-field type="text" name="no_hp" label="No. HP"
                                       placeholder="Masukkan nomor HP"
                                       icon="bx bx-phone"
                                       :value="old('no_hp', $siswa->no_hp ?? '')" />

                        <x-input-field type="text" name="image" label="Foto (URL/Path)"
                                       placeholder="Masukkan URL gambar"
                                       icon="bx bx-image"
                                       :value="old('image', $siswa->image ?? '')" />
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

                            <x-input-field type="text" name="rfid_no" label="RFID"
                                           placeholder="Masukkan tanggal lahir"
                                           :value="old('rfid_no', $siswa->rfid_no ?? '')" />
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
@endpush
