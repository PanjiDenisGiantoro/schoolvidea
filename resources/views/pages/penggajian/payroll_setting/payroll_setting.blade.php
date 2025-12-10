@extends('layouts.app')
@section('title', isset($setting) ? (isset($show) && $show ? 'Lihat Setting' : 'Edit Setting') : 'Tambah Setting')

@section('content')
    @include('partials.page-title', [
        'title' => isset($setting) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Setting Gaji',
    ])

    <div class="card">
        <div class="card-body">
            <form id="settingPayrollForm"
                action="{{ isset($setting) ? route('payroll_settings.update', $setting->id) : route('payroll_settings.store') }}"
                method="POST">
                @csrf
                @if (isset($setting))
                    @method('PUT')
                @endif
                @php
                    $readonly = isset($show) && $show;
                @endphp

                {{-- ======================== UNIT & OFFICER ======================== --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Unit Pendidikan</label>
                        <select name="units_id" id="unit" class="form-select" required
                            {{ isset($show) ? 'disabled' : '' }}>
                            <option value="">-- Pilih Unit --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}"
                                    {{ isset($setting) && $setting->units_id == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Guru & Staff</label>
                        <select name="officers_id" id="officer" class="form-select" required
                            {{ isset($show) ? 'disabled' : '' }}>
                            <option value="">-- Pilih Guru & Staff --</option>
                            @foreach ($officers as $off)
                                <option value="{{ $off->id }}"
                                    {{ isset($setting) && $setting->officers_id == $off->id ? 'selected' : '' }}>
                                    {{ $off->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ======================== FORM DETAIL ======================== --}}


                {{-- hidden position --}}
                <div class="row mb-3" hidden>
                    <div class="col-md-4">
                        <x-input-field type="text" id="position_display" name="position_display" label="Jabatan" readonly
                            :disabled="true" value="" />
                        <input type="text" name="type" value="gaji">
                    </div>
                </div>

                {{-- GAJI DASAR --}}
                <div class="row mb-3">
                    <div class="col-md-1">
                        <x-input-field type="text" name="teaching_hours" label="JM/mgg" placeholder="Jumlah"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3"
                            :disabled="$readonly" :readonly="$readonly" value="{{ $setting->teaching_hours ?? '' }}" />
                    </div>
                    <div class="col-md-1">
                        <x-input-field type="text" name="teaching_hours_total" label="JM Tot/bln" placeholder="Jumlah"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxLength="3"
                            :disabled="$readonly" :readonly="$readonly" value="{{ $setting->teaching_hours_total ?? '' }}" />
                    </div>
                    <div class="col-md-2">
                        <x-input-field type="text" name="salary" label="Gaji / Honor" placeholder="Nominal"
                            :readonly="$readonly" :disabled="$readonly" inputmode="numeric"
                            value="{{ number_format($setting->salary ?? 0, 0, ',', '.') }}"
                            oninput="formatCurrencyInput(this)" />
                    </div>

                    @php
                        $allowances = [
                            'staff' => 'Kehadiran Staff',
                            'transport' => 'Transport',
                            'meal' => 'Makan',
                            'other' => 'Lainnya',
                        ];
                    @endphp

                    @foreach ($allowances as $key => $label)
                        <div class="col-md-2 mb-3">
                            <label for="{{ $key }}_toggle"
                                class="form-label fw-semibold d-flex align-items-center gap-2">
                                <span>{{ $label }}</span>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="{{ $key }}_toggle"
                                        {{ $readonly ? 'disabled' : '' }}
                                        onchange="toggleField('{{ $key }}_toggle', '{{ $key }}_allowance')"
                                        {{ isset($setting->{$key . '_allowance'}) && $setting->{$key . '_allowance'} > 0 ? 'checked' : '' }}>
                                </div>
                            </label>
                            <input type="text" name="{{ $key }}_allowance" id="{{ $key }}_allowance"
                                class="form-control" placeholder="Nonaktif" inputmode="numeric" disabled
                                style="font-size: 16px; padding: 10px 12px;"
                                value="{{ isset($setting->{$key . '_allowance'}) ? number_format($setting->{$key . '_allowance'}, 0, '.', '.') : '' }}"
                                oninput="formatCurrencyInput(this)" {{ $readonly ? 'readonly' : '' }} />
                        </div>
                    @endforeach
                </div>

                {{-- ======================== KOMPONEN GAJI ======================== --}}
                <hr class="my-4">
                <h5 class="fw-semibold mb-3">Komponen Gaji</h5>
                <div id="component-container">
                    @if (isset($setting) && $setting->components->count())
                        @foreach ($setting->components as $comp)
                            <div class="row g-3 component-row">
                                <div class="col-md-4">
                                    <x-input-field type="text" name="position" label="Jabatan" readonly :disabled="true"
                                        value="{{ $setting->officer?->position?->positions_name ?? '' }}" />
                                </div>
                                <div class="col-md-4">
                                    <label for="components_id" class="form-label">Nama Komponen <span
                                            class="text-danger">*</span></label>
                                    <select name="components_id[]" class="form-select component-select"
                                        {{ isset($show) ? 'disabled' : '' }}>
                                        @foreach ($components as $c)
                                            <option value="{{ $c->id }}"
                                                {{ $c->id == $comp->id ? 'selected' : '' }}
                                                data-value="{{ $c->price }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="component_value" class="form-label">Nilai Komponen <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="component_value[]"
                                        style="font-size: 16px; padding: 10px 12px;" class="form-control component-value"
                                        inputmode="numeric" {{ isset($show) ? 'disabled' : '' }} readonly
                                        value="{{ number_format($comp->pivot->value ?? 0, 0, '.', '.') }}"
                                        oninput="formatCurrencyInput(this)">
                                </div>

        @if (!$readonly)
        <div class="col-md-1 d-flex align-items-center">
            <button type="button" class="btn btn-danger remove-row">
                <i class="bx bx-trash"></i>
            </button>
        </div>
        @endif
                            </div>
                        @endforeach
                    @endif

                    @if (!$readonly)
                        <div class="col-md-3 d-flex align-items-end mt-3">
                            <button type="button" class="btn btn-success add-component w-100">
                                <i class="bx bx-plus"></i> Tambah Item Komponen Gaji
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Template Komponen Gaji --}}
                <template id="component-template">
                    <div class="row g-3 component-row">
                        <div class="col-md-4">
                            <x-input-field type="text" name="position" label="Jabatan" readonly :disabled="true"
                                value="" />
                        </div>
                        <div class="col-md-4">
                            <label for="components_id" class="form-label">Nama Komponen <span
                                    class="text-danger">*</span></label>
                            <select name="components_id[]" class="form-select component-select">
                                <option value="">-- Pilih Komponen --</option>
                                @foreach ($components as $c)
                                    <option value="{{ $c->id }}" data-value="{{ $c->price }}">
                                        {{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="component_value" class="form-label">Nilai Komponen <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="component_value[]" style="font-size: 16px; padding: 10px 12px;"
                                class="form-control component-value" oninput="formatCurrencyInput(this)" readonly>
                        </div>
                        <div class="col-md-1 d-flex align-items-center">
                            <button type="button" class="btn btn-danger remove-row">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                </template>

                {{-- ======================== POTONGAN ======================== --}}
                <hr class="my-4">
                <h5 class="fw-semibold mb-3">Potongan</h5>
                <div id="deduction-container">
                    @if (isset($setting) && $setting->deductions->count())
                        @foreach ($setting->deductions as $ded)
                            <div class="row g-3 deduction-row">
                                <div class="col-md-4">
                                    <x-input-field type="text" name="type_deduction" label="Tipe Potongan" readonly
                                        :disabled="isset($show)" value="{{ $ded->type }}" />
                                        {{-- <input type="hidden" id="deduction_type_update[]" name="deduction_type_update[]" class="deduction-type-update" value="{{ $ded->type }}">
--}}                                </div>
                                <div class="col-md-4">
                                    <label for="deductions_id" class="form-label">Nama Potongan <span
                                            class="text-danger">*</span></label>
                                    <select name="deductions_id[]" class="form-select deduction-select"
                                        {{ isset($show) ? 'disabled' : '' }}>
                                        @foreach ($deductions as $d)
                                            <option value="{{ $d->id }}"
                                                {{ $d->id == $ded->id ? 'selected' : '' }}
                                                data-type="{{ $d->type }}" data-value="{{ $d->price }}">
                                                {{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="deductions_value" class="form-label">Nilai Potongan <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="deduction_value[]" class="form-control deduction-value"
                                        value="{{ number_format($ded->pivot->value ?? 0, 0, ',', '.') }}"
                                        oninput="formatCurrencyInput(this)" inputmode="numeric"
                                        style="font-size: 16px; padding: 10px 12px;" readonly
                                        {{ isset($show) ? 'disabled' : '' }}>
                                </div>

        @if (!$readonly)
        <div class="col-md-1 d-flex align-items-center">
            <button type="button" class="btn btn-danger remove-row">
                <i class="bx bx-trash"></i>
            </button>
        </div>
        @endif
                            </div>
                        @endforeach
                    @endif

                    @if (!$readonly)
                        <div class="col-md-3 d-flex align-items-end mt-3">
                            <button type="button" class="btn btn-success add-deduction w-100">
                                <i class="bx bx-plus"></i> Tambah Item Potongan
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Template Potongan --}}
                <template id="deduction-template">
                    <div class="row g-3 deduction-row">
                        <div class="col-md-4">
                            <x-input-field type="text" name="type_deduction" label="Tipe Potongan" readonly
                                value="-" />
                                <input type="hidden" name="deduction_type[]" class="deduction-type" value="">
                        </div>
                        <div class="col-md-4">
                            <label for="deductions_id" class="form-label">Nama Potongan <span
                                    class="text-danger">*</span></label>
                            <select name="deductions_id[]" class="form-select deduction-select">
                                <option value="">-- Pilih Potongan --</option>
                                @foreach ($deductions as $d)
                                    <option value="{{ $d->id }}" data-type="{{ $d->type }}"
                                        data-value="{{ $d->price }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="deductions_value" class="form-label">Nilai Potongan <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="deduction_value[]" class="form-control deduction-value"
                                oninput="formatCurrencyInput(this)" style="font-size: 16px; padding: 10px 12px;" readonly>
                        </div>
                        <div class="col-md-1 d-flex align-items-center">
                            <button type="button" class="btn btn-danger remove-row">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                </template>

                {{-- ======================== PERIODE GAJI ======================== --}}
                <hr class="my-4">
                <h5 class="fw-semibold mb-3">Periode Gaji</h5>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Jumlah Periode Tagihan</label>
                        <select name="billing_period" class="form-select" {{ $readonly ? 'disabled' : '' }}>
                            <option value="">-- Pilih Periode --</option>
                            @foreach (range(1, 12) as $i)
                                <option {{ isset($setting) && $setting->billing_period == $i ? 'selected' : '' }}
                                    value="{{ $i }}">{{ $i }} Bulan</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bulan Mulai</label>
                        <select id="start_month" name="start_month" class="form-select" {{ $readonly ? 'disabled' : '' }}>
                            <option value="">-- Pilih Bulan Masuk --</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}"
                                    {{ isset($setting) && $setting->start_month == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $i, 1)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tahun Mulai</label>
                        <select id="start_year" name="start_year" class="form-select" {{ $readonly ? 'disabled' : '' }}>
                            @for ($y = date('Y'); $y <= date('Y') + 5; $y++)
                                <option value="{{ $y }}"
                                    {{ isset($setting) && $setting->start_year == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                @if (isset($show) && $show && !empty($period_details))
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="m-0">Detail Periode</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table-bordered mt-0 table">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Periode</th>
                                        <th>Bulan</th>
                                        <th>Tahun</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($period_details as $i => $periode)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>Periode {{ $i + 1 }}</td>
                                            <td>{{ $periode['bulan'] }}</td>
                                            <td>{{ $periode['tahun'] }}</td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>

                @endif

                {{-- ======================== BUTTON ======================== --}}
                <div class="mt-5 text-start">
                    @if (!$readonly)
                        <button type="submit" class="btn btn-primary px-4">
                            {{ isset($setting) ? 'Update' : 'Simpan' }}
                        </button>
                    @endif
                    <a href="{{ route('payroll_settings.index') }}" class="btn btn-secondary">Batal</a>
                </div>
        </div>
        </form>
    </div>
    </div>


@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const formSection = document.querySelector('#form-section');
        const unitSelect = document.querySelector('#unit');
        const officerSelect = document.querySelector('#officer');
        const readonly = @json($readonly);
        const startMonth = document.getElementById('start_month');
        const startYear = document.getElementById('start_year');
        const teachingHour = document.getElementById('teaching_hours');
        const teachingHourTotal = document.getElementById('teaching_hours_total');

        function checkLock() {
            if (teachingHour.value.trim() !== '') {
                teachingHourTotal.value = '';
                teachingHourTotal.disabled = true;
            } else {
                teachingHourTotal.disabled = false;
            }
            if (teachingHourTotal.value.trim() !== '') {
                teachingHour.value = '';
                teachingHour.disabled = true;
            } else {
                teachingHour.disabled = false;
            }
        }
        teachingHour.addEventListener('input', checkLock);
        teachingHourTotal.addEventListener('input', checkLock);
        checkLock();


        startMonth.addEventListener('change', function(data) {
            const value = data.target.value;
            console.log('start_month: ', value);
        });
        startYear.addEventListener('change', function(data) {
            const value = data.target.value;
            console.log('start_year', value);
        });

        // Toggle input enable/disable
        window.toggleField = function(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            if (toggle.checked) {
                input.disabled = false;
                input.readonly = false;
                input.placeholder = "Nominal";
            } else {
                input.readonly = true;
                input.disabled = false;
                input.value = "";
                input.placeholder = "Nonaktif";
            }
        };

        // Auto enable allowance input (mode edit)
        document.querySelectorAll('input[type="number"][id$="_allowance"]').forEach(input => {
            if (input.value && parseFloat(input.value) > 0) {
                input.disabled = false;
            }
        });

        // Filter Officer Berdasarkan Unit
        unitSelect.addEventListener('change', async () => {
            const unitId = unitSelect.value;
            officerSelect.innerHTML = '<option value="">-- Pilih Guru & Staff --</option>';
            officerSelect.disabled = true;

            if (!unitId) return;

            try {
                const res = await fetch(`/payroll-setting/officers/by-unit/${unitId}`);
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

                const data = await res.json();
                officerSelect.disabled = false;
                data.forEach(o => {
                    const opt = document.createElement('option');
                    opt.value = o.id;
                    opt.textContent = o.user.name;
                    officerSelect.appendChild(opt);
                });
            } catch (err) {
                console.error('Error fetching officers:', err);
            }
        });

        // Load komponen & potongan setelah guru dipilih
        officerSelect.addEventListener('change', async () => {
            const officerId = officerSelect.value;
            if (!officerId) return;

            // Reset allowance
            [
                'transport_allowance',
                'meal_allowance',
                'staff_allowance',
                'other_allowance'
            ].forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.value = '';
                    input.disabled = true;
                    input.placeholder = 'Nonaktif';

                    const toggle = document.getElementById(id.replace('_allowance', '_toggle'));
                    if (toggle) toggle.checked = false;
                }
            });

            try {
                const res = await fetch(`/payroll-setting/fetch/${officerId}`);
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

                const data = await res.json();
                console.log("Data dari server:", data);

                if (data.status === 'error') {
                    console.error('Error from server:', data.message);
                    return;
                }

                // Position
                document.getElementById('position_display').value = data.position_name || data.positions_name || '-';

                let hiddenInput = document.querySelector('[name="position_id"]');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'position_id';
                    officerSelect.closest('form').appendChild(hiddenInput);
                }
                hiddenInput.value = data.position_id || '';

                // Komponen
                const compSelects = document.querySelectorAll('.component-select');
                compSelects.forEach(select => {
                    select.innerHTML = '<option value="">-- Pilih Komponen --</option>';
                    if (data.components) {
                        data.components.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.dataset.value = c.value;
                            opt.textContent = c.name;
                            select.appendChild(opt);
                        });
                    }
                });

                // Deduction
                const dedSelects = document.querySelectorAll('.deduction-select');
                dedSelects.forEach(select => {
                    select.innerHTML = '<option value="">-- Pilih Potongan --</option>';
                    if (data.deductions) {
                        data.deductions.forEach(d => {
                            const opt = document.createElement('option');
                            opt.value = d.id;
                            opt.dataset.value = d.value;
                            opt.dataset.type = d.type || '-';
                            opt.textContent = d.name;
                            select.appendChild(opt);
                        });
                    }
                });

                // Update posisi di row komponen yang sudah ada
                document.querySelectorAll('.component-row [name="position"]').forEach(input => {
                    input.value = data.position_name || '-';
                });

            } catch (err) {
                console.error('Error fetching officer data:', err);
            }
        });

        // Tambah row dinamis
        document.addEventListener('click', e => {
            if (e.target.closest('.remove-row')) {
                const row = e.target.closest('.component-row, .deduction-row');
                if (row) row.remove();
            }

            if (e.target.closest('.add-component')) {
                const container = document.querySelector('#component-container');
                const template = document.querySelector('#component-template');
                const clone = template.content.cloneNode(true);

                const positionValue = document.getElementById('position_display').value;
                clone.querySelector('[name="position"]').value = positionValue;

                const addBtn = container.querySelector('.add-component').closest('.col-md-3');
                container.insertBefore(clone, addBtn);
            }

            if (e.target.closest('.add-deduction')) {
                const container = document.querySelector('#deduction-container');
                const template = document.querySelector('#deduction-template');
                const clone = template.content.cloneNode(true);

                const addBtn = container.querySelector('.add-deduction').closest('.col-md-3');
                container.insertBefore(clone, addBtn);
            }
        });

        // Auto isi nilai komponen
        document.addEventListener('change', e => {
            if (e.target.classList.contains('component-select')) {
                const val = e.target.selectedOptions[0].dataset.value || '';
                const input = e.target.closest('.component-row').querySelector('.component-value');
                input.value = val;
                formatCurrencyInput(input);
            }
            if (e.target.classList.contains('deduction-select')) {
                const opt = e.target.selectedOptions[0];

                const val = opt.dataset.value || '';
                const type = opt.dataset.type || '-';

                const row = e.target.closest('.deduction-row');
                row.querySelector('.deduction-value').value = val;
                row.querySelector('.deduction-type').value = type;
                row.querySelector('input[name="type_deduction"]').value = type;

                formatCurrencyInput(row.querySelector('.deduction-value'));
            }
        });
    });

    function formatCurrencyInput(input) {
        let value = input.value.replace(/[^\d]/g, '');
        if (value === '') {
            input.value = '';
            return;
        }
        input.value = new Intl.NumberFormat('id-ID').format(value);
    }

    // ==========================================
    // 🔥 KONFIRMASI ALERT sebelum submit form
    // ==========================================
    document.addEventListener('submit', function (e) {
        e.preventDefault(); // batal submit dulu

        Swal.fire({
            title: "Konfirmasi",
            text: "Apakah Anda yakin ingin menyimpan data payroll?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, simpan",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                // Hapus titik pemisah angka sebelum dikirim
                const inputs = document.querySelectorAll(
                    '.component-value, .deduction-value, .deduction-type, .deduction-type-update, [id$="_allowance"], [name="salary"]'
                );
                inputs.forEach(input => {
                    input.value = input.value.replace(/\./g, '');
                });

                e.target.submit(); // lanjut submit
            }
        });
    });
</script>
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ $errors->first() }}',
                showConfirmButton: true,
            });
        </script>
    @endif
@endpush
