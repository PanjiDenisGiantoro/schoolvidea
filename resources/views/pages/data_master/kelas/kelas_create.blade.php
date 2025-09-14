@extends('layouts.app')
@section('title', isset($kelas) ? (isset($show) && $show ? 'Lihat Kelas' : 'Edit Kelas') : 'Tambah Kelas')

@section('content')
    @include('partials.page-title', [
        'title' => isset($kelas) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Kelas'
    ])

    <div class="card">
        <div class="card-body">
            <form id="kelasForm"
                  action="{{ isset($kelas) ? route('kelas.update', $kelas->id) : route('kelas.store') }}"
                  method="POST">
                @csrf
                @if(isset($kelas))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Nama Unit</label>
                            <select name="unit_id" id="unit_id" class="form-select" data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Unit --</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('unit_id', $kelas->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="nama_kelas" label="Nama Kelas"
                                       placeholder="Masukkan Nama Kelas" icon="bx bx-book"
                                       :value="old('nama_kelas', $kelas->nama_kelas ?? '')" required />
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="officer_id" class="form-label">Wali Kelas</label>
                            <select name="officer_id" id="officer_id" class="form-select" data-choices data-choices-sorting-false required>
                                <option value="">-- Pilih Wali Kelas --</option>
                                @foreach($wali as $w)
                                    <option value="{{ $w->id }}" {{ old('officer_id', $kelas->officer_id ?? '') == $w->id ? 'selected' : '' }}>
                                        {{ $w->user->name ?? 'Tanpa Nama' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">-- Pilih Status --</option>
                                <option value="1" {{ old('status', $kelas->status ?? '') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status', $kelas->status ?? '') == 'Tidak Aktif' ? 'selected' : '' }}>Non Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                            <select name="tahun_ajaran_id"  id="choices-single-no-sorting"  data-choices data-choices-sorting-false>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach($tahun_ajaran as $t)
                                <option value="{{ $t->id }}"
                                    {{ old('tahun_ajaran_id', $officer->tahun_ajaran_id ?? ($tahun_ajaran_selected->id ?? '')) == $t->id ? 'selected' : '' }}>
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
                    <a href="{{ url('kelas/') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        @if(isset($show) && $show)
        document.addEventListener('DOMContentLoaded', function() {
            const formElements = document.querySelectorAll('#kelasForm input, #kelasForm textarea, #kelasForm select, #kelasForm button[type="submit"]');
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
