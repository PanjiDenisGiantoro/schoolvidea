@extends('layouts.app')
@section('title', isset($officer) ? (isset($show) && $show ? 'Lihat User' : 'Edit User') : 'Tambah User')

@section('content')
    @include('partials.page-title', [
        'title' => isset($officer) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Petugas'
    ])

    <div class="card">
        <div class="card-body">
            <form id="userForm" action="{{ isset($officer) ? route('officer.update', $officer->id) : route('officer.store') }}"
                  method="POST">
                @csrf
                @if(isset($officer))
                    @method('PUT')
                @endif

                {{-- Data Akses --}}
                <h5 class="card-title mb-0 mt-3">Data Akses Petugas</h5>
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
                <h5 class="card-title mb-0 mt-4">Data Lengkap Petugas</h5>
                <p class="text-muted">Masukkan informasi detail petugas</p>
                <hr>
                <div class="row g-3">
                    <div class="col-md-3">
                        <x-input-field type="text" name="nip" label="NIP"
                                       placeholder="Masukkan NIP" icon="bx bx-id-card"
                                       :value="old('nip', $officer->officer->nip ?? '')" />

                        <x-input-field type="text" name="nuptk" label="NUPTK"
                                       placeholder="Masukkan NUPTK" icon="bx bx-id-card"
                                       :value="old('nuptk', $officer->officer->nuptk ?? '')" />

                        <x-input-field type="text" name="nik" label="NIK"
                                       placeholder="Masukkan NIK" icon="bx bx-id-card"
                                       :value="old('nik', $officer->officer->nik ?? '')" />

                        <x-input-field type="text" name="tempat_lahir" label="Tempat Lahir"
                                       placeholder="Masukkan tempat lahir" icon="bx bx-map"
                                       :value="old('tempat_lahir', $officer->officer->tempat_lahir ?? '')" />

                        <x-input-field type="date" name="tanggal_lahir" label="Tanggal Lahir"
                                       icon="bx bx-calendar"
                                       :value="old('tanggal_lahir', $officer->officer->tanggal_lahir ?? '')" />

                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin', $officer->officer->jenis_kelamin ?? '') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin', $officer->officer->jenis_kelamin ?? '') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <x-input-field type="text" name="agama" label="Agama"
                                       placeholder="Masukkan agama" icon="bx bx-book"
                                       :value="old('agama', $officer->officer->agama ?? '')" />
                    </div>

                    <div class="col-md-3">
                        <x-input-field type="text" name="no_hp" label="No. HP"
                                       placeholder="Masukkan nomor HP" icon="bx bx-phone"
                                       :value="old('no_hp', $officer->officer->no_hp ?? '')" />

                        <x-input-field type="text" name="bank" label="Bank"
                                       placeholder="Masukkan nama bank" icon="bx bx-bank"
                                       :value="old('bank', $officer->officer->bank ?? '')" />

                        <x-input-field type="text" name="no_rekening" label="No Rekening"
                                       placeholder="Masukkan nomor rekening" icon="bx bx-credit-card"
                                       :value="old('no_rekening', $officer->officer->no_rekening ?? '')" />

                        <x-input-field type="text" name="no_kartu_rfid" label="No Kartu RFID"
                                       placeholder="Masukkan nomor kartu RFID" icon="bx bx-barcode"
                                       :value="old('no_kartu_rfid', $officer->officer->no_kartu_rfid ?? '')" />

                        <x-input-field type="text" name="qr_code" label="QR Code"
                                       placeholder="Masukkan QR Code" icon="bx bx-qr"
                                       :value="old('qr_code', $officer->officer->qr_code ?? '')" />

                        <x-input-field type="text" name="va_guru" label="VA Petugas"
                                       placeholder="Masukkan VA Petugas" icon="bx bx-credit-card"
                                       :value="old('va_guru', $officer->officer->va_guru ?? '')" />
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Role Petugas</label>
                            <select name="role_id" class="form-select" data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}" {{ ($officer->officer->role_id ?? '') == $r->id ? 'selected' : '' }}>
                                        {{ $r->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select name="unit_id" class="form-select" data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Unit --</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}" {{ old('unit_id', $officer->officer->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
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

                        <div class="mb-3">
                            <label for="jurusan" class="form-label">Jurusan</label>
                            <select name="jurusan[]" id="jurusan" class="form-select" multiple>
                                @foreach ($jurusans as $jurusan)
                                    <option value="{{ $jurusan->id }}"
                                        {{ in_array($jurusan->id, old('jurusan', $officer->jurusan ?? [])) ? 'selected' : '' }}>
                                        {{ $jurusan->nama_jurusan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamat" class="form-control" rows="7">{{ old('alamat', $officer->officer->alamat ?? '') }}</textarea>
                        </div>
                        <x-input-field type="text" name="image" label="Foto (URL/Path)"
                                       placeholder="Masukkan URL gambar" icon="bx bx-image"
                                       :value="old('image', $officer->officer->image ?? '')" />
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($officer) ? 'Update' : 'Simpan' }}
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
