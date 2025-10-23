@extends('layouts.app')
@section('title', isset($payroll_components) ? (isset($show) && $show ? 'Lihat Komponen' : 'Edit Komponen') : 'Tambah Komponen')
@section('content')
    @include('partials.page-title', [
        'title' => isset($payroll_components) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Komponen Gaji'
    ])
    <div class="card">
        <div class="card-body">
            <form id="componentForm" method="POST"
                  action="{{ isset($payroll_components) ? route('payroll_components.update', $payroll_components->id) : route('payroll_components.store') }}">
                @csrf
                @if (isset($payroll_components))
                    @method('PUT')
                @endif
                <div class="row p-4">
                    <div class="col-md-6">
                        <x-input-field type='text' name='name' label='Nama Komponen'
                                       placeholder='Masukkan Nama Komponen' icon='bx bx-unit'
                                       :value="old('name', $payroll_components->name ?? '')" required/>
                    </div>
                    <div class="col-md-6">
                        <x-input-field
                            type="text"
                            name="price_display"
                            label="Nilai Komponen"
                            placeholder="Masukkan Nilai Komponen"
                            icon="bx bx-unit"
                            value="{{ old('price', isset($payroll_components) ? number_format($payroll_components->price, 0, ',', '.') : '') }}"
                            required
                            oninput="formatNumberInput(this)"
                        />
                        <input type="hidden" name="price" id="price_hidden" value="{{ old('price', $payroll_components->price ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select">
                            <option value="1" {{ old('status', $payroll_components->status ?? '') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status', $payroll_components->status ?? '') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="description" class="form-label">Keterangan</label>
                        <textarea name="description" id="description"
                                  style=" height: 45px; font-size: 0.95rem;"
                                  class="form-control" rows="2">{{ old('description', $payroll_components->description ?? '') }}</textarea>
                    </div>
                </div>
                <div class="m-4 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($payroll_components) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ route('payroll_components.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('componentForm');

            // ✅ Mode Lihat Data
            @if (isset($show) && $show)
            document.querySelectorAll('#componentForm input, #componentForm select, #componentForm textarea, #componentForm button[type="submit"]').forEach(el => {
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
    <script>
        function formatNumberInput(el) {
            // Ambil angka tanpa karakter non-digit
            let numericValue = el.value.replace(/\D/g, '');

            // Format jadi ribuan pakai titik
            el.value = new Intl.NumberFormat('id-ID').format(numericValue);

            // Masukkan ke input hidden tanpa format
            document.getElementById('price_hidden').value = numericValue;
        }
    </script>

@endpush
