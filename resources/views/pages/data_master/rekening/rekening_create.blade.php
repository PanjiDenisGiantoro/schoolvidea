@extends('layouts.app')
@section('title', isset($rekening) ? (isset($show) && $show ? 'Lihat Rekening' : 'Edit Rekening') : 'Tambah Rekening')

@section('content')
    @include('partials.page-title', [
        'title' => isset($rekening) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Rekening'
    ])

    <div class="card">
        <div class="card-body">
            <form id="rekeningForm"
                  action="{{ isset($rekening) ? route('rekening.update', $rekening->id) : route('rekening.store') }}"
                  method="POST">
                @csrf
                @if(isset($rekening))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-4">
                        <x-input-field type="text" name="type_rekening" label="Tipe Rekening"
                                       placeholder="Tabungan / Giro / dll"
                                       :value="old('type_rekening', $rekening->type_rekening ?? '')" />
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="nama_rekening" label="Nama Rekening"
                                       placeholder="Atas Nama"
                                       :value="old('nama_rekening', $rekening->nama_rekening ?? '')" />
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="no_rekening" label="Nomor Rekening"
                                       placeholder="Masukkan Nomor Rekening"
                                       :value="old('no_rekening', $rekening->no_rekening ?? '')" />
                    </div>

                    <div class="col-md-4">
                        <label for="user_id" class="form-label">User</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">-- Pilih User --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ old('user_id', $rekening->user_id ?? '') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="unit_id" class="form-label">Unit</label>
                        <select name="unit_id" id="unit_id" class="form-select">
                            <option value="">-- Pilih Unit --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id', $rekening->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="bank" label="Bank"
                                       placeholder="Nama Bank"
                                       :value="old('bank', $rekening->bank ?? '')" />
                    </div>

                    <div class="col-md-4">
                        <x-input-field type="text" name="KCP" label="KCP"
                                       placeholder="KCP Cabang"
                                       :value="old('KCP', $rekening->KCP ?? '')" />
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="1" {{ old('status', $rekening->status ?? '') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status', $rekening->status ?? '') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($rekening) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ route('rekening.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        @if(isset($show) && $show)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#rekeningForm input, #rekeningForm select, #rekeningForm button[type="submit"]')
                .forEach(el => {
                    el.disabled = true;
                    if (el.type === 'submit') el.style.display = 'none';
                });
        });
        @endif
    </script>
@endpush
