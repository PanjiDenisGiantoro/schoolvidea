@extends('layouts.app')
@section('title', isset($payroll_deductions) ? (isset($show) && $show ? 'Lihat Potongan' : 'Edit Potongan') : 'Tambah Potongan')
@section('content')
    @include('partials.page-title', [
        'title' => isset($payroll_deductions) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Potongan'
    ])
    <div class="card">
        <div class="card-body">
            <form id="deductionForm" method="POST"
                  action="{{ isset($payroll_deductions) ? route('payroll_deductions.update', $payroll_deductions->id) : route('payroll_deductions.store') }}">
                @csrf
                @if (isset($payroll_deductions))
                    @method('PUT')
                @endif
                <div class="row p-4">
                    <div class="col-md-6">
                        <x-input-field type='text' name='name' label='Nama potongan'
                                       placeholder='Masukkan Nama potongan' icon='bx bx-unit'
                                       :value="old('name', $payroll_deductions->name ?? '')" required/>
                    </div>
                    <div class="col-md-6">
                        <x-input-field type='number' name='price' label='Nilai potongan'
                                       placeholder='Masukkan Nilai potongan' icon='bx bx-unit'
                                       :value="old('price', $payroll_deductions->price ?? '')" required/>
                    </div>
                    <div class="col-md-6">
                        <label for="type" class="form-label">Jenis Potongan <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select">
                            <option value="">-- Pilih Jenis Potongan --</option>
                            <option value="nominal" {{ old('type', $payroll_deductions->type ?? '') == 'nominal' ? 'selected' : ''}}>Nominal</option>
                            <option value="persen" {{ old('type', $payroll_deductions->type ?? '') == 'persen' ? 'selected' : ''}}>Persen</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select">
                            <option value="1" {{ old('status', $payroll_deductions->status ?? '') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status', $payroll_deductions->status ?? '') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label for="description" class="form-label">Keterangan</label>
                        <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $payroll_deductions->description ?? '') }}</textarea>
                    </div>
                </div>
                <div class="m-4 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($payroll_deductions) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ route('payroll_deductions.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('deductionForm');

            // ✅ Mode Lihat Data
            @if (isset($show) && $show)
            document.querySelectorAll('#deductionForm input, #deductionForm select, #deductionForm textarea, #deductionForm button[type="submit"]').forEach(el => {
                el.disabled = true;
                if (el.type === 'submit') el.style.display = 'none';
            });
            @endif

            // ✅ Konfirmasi sebelum submit
            if (form) {
                form.addEventListener('submit', e => {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Apakah data sudah benar?',
                        text: 'Pastikan semua data sudah diisi dengan benar sebelum menyimpan.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Simpan!',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d'
                    }).then(result => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Menyimpan...',
                                text: 'Harap tunggu sebentar.',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                            setTimeout(() => form.submit(), 800);
                        }
                    });
                });
            }
        });
    </script>

    {{-- ✅ Alert Sukses --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        </script>
    @endif

    {{-- ❌ Alert Error --}}
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: `
            <ul style="text-align:left;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
                    </ul>
`,
                    confirmButtonColor: '#d33'
                });
            });
        </script>
    @endif
@endpush
