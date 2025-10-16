@extends('layouts.app')
@section('title', isset($setting) ? (isset($show) && $show ? 'Lihat Setting' : 'Edit Setting') : 'Tambah Setting')

@section('content')
@include('partials.page-title', [
    'title' => isset($setting) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
    'subTitle' => 'Setting Gaji'
])

<div class="card">
    <div class="card-body">
<form id="settingPayrollForm"
      action="{{ isset($setting) ? route('payroll_settings.update', $setting->id) : route('payroll_settings.store') }}"
      method="POST">
    @csrf
    @if(isset($setting))
        @method('PUT')
    @endif
    @php 
        $readonly = isset($show) && $show;
    @endphp

    {{-- ======================== UNIT & OFFICER ======================== --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Unit Pendidikan</label>
            <select name="units_id" id="unit" class="form-select" required {{ isset($show) ? 'disabled' : '' }}>
                <option value="">-- Pilih Unit --</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ (isset($setting) && $setting->units_id == $unit->id) ? 'selected' : '' }}>
                        {{ $unit->nama_unit }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">Guru & Staff</label>
            <select name="officers_id" id="officer" class="form-select" required {{ isset($show) ? 'disabled' : '' }}>
                <option value="">-- Pilih Guru & Staff --</option>
                @foreach($officers as $off)
                    <option value="{{ $off->id }}" {{ (isset($setting) && $setting->officers_id == $off->id) ? 'selected' : '' }}>
                        {{ $off->user->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ======================== FORM DETAIL ======================== --}}
    <div id="form-section" style="{{ isset($setting) ? 'display: block;' : 'display: none;' }}">

        {{-- GAJI DASAR --}}
        <div class="row mb-3">
            <div class="col-md-2">
                <x-input-field type="number" name="teaching_hours" label="Jml Jam Ajar (jam)" 
                    placeholder="Jumlah" :disabled="$readonly" :readonly="$readonly" value="{{ $setting->teaching_hours ?? '' }}"/>
            </div>
            <div class="col-md-2">
                <x-input-field type="text" name="salary" label="Gaji / Honor" 
                    placeholder="Nominal" :readonly="$readonly" :disabled="$readonly" inputmode="numeric"
                    value="{{ number_format($setting->salary ?? 0, 0, ',', '.',) }}"
                    oninput="formatCurrencyInput(this)"/>
            </div>

            @php
                $allowances = [
                    'transport' => 'Transport',
                    'meal' => 'Uang Makan',
                    'communication' => 'Uang Komunikasi',
                    'other' => 'Lainnya',
                ];
            @endphp

            @foreach($allowances as $key => $label)
            <div class="col-md-2 mb-3">
                <label for="{{ $key }}_toggle" class="form-label fw-semibold d-flex align-items-center gap-2">
                    <span>{{ $label }}</span>
                    <div class="form-check form-switch m-0">
                        <input 
                            class="form-check-input"
                            type="checkbox"
                            id="{{ $key }}_toggle"
                            {{ $readonly ? 'disabled' : '' }}
                            onchange="toggleField('{{ $key }}_toggle', '{{ $key }}_allowance')"
                            {{ isset($setting->{$key.'_allowance'}) && $setting->{$key.'_allowance'} > 0 ? 'checked' : '' }}
                        >
                    </div>
                </label>
                <input 
                    type="text"
                    name="{{ $key }}_allowance"
                    id="{{ $key }}_allowance"
                    class="form-control"
                    placeholder="Nonaktif"
                    inputmode="numeric"
                    disabled
                    style="font-size: 16px; padding: 10px 12px;"
                    value="{{ isset($setting->{$key.'_allowance'}) ? number_format($setting->{$key.'_allowance'}, 0, '.', '.') : '' }}"
                    oninput="formatCurrencyInput(this)"
                    {{ $readonly ? 'readonly' : '' }}
                />
            </div>
            @endforeach
        </div>

        {{-- ======================== KOMONEN GAJI ======================== --}}
        <hr class="my-4">
        <h5 class="fw-semibold mb-3">Komponen Gaji</h5>
        <div id="component-container">
            @if(isset($setting) && $setting->components->count())
                @foreach($setting->components as $comp)
                    <div class="row g-3 component-row">
                        <div class="col-md-4">
                            <x-input-field  type="text" name="position"
                            label="Jabatan" readonly :disabled="isset($show)"  value="{{ $setting->officer->position->name ?? '-' }}" />
                        </div>
                        <div class="col-md-4">
                            <label for="components_id" class="form-label">Nama Komponen <span class="text-danger">*</span></label>
                            <select name="components_id[]" class="form-select component-select" {{ isset($show) ? 'disabled' : '' }}>
                                @foreach($components as $c)
                                    <option value="{{ $c->id }}" {{ $c->id == $comp->id ? 'selected' : '' }} data-value="{{ $c->value }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="component_value" class="form-label">Nilai Komponen <span class="text-danger">*</span></label>
                            <input type="text" name="component_value[]" 
                            style="font-size: 16px; padding: 10px 12px;"
                            class="form-control component-value" inputmode="numeric"
                            {{ isset($show) ? 'disabled' : '' }} readonly
                            value="{{ number_format($comp->pivot->value ?? 0, 0, '.', '.') }}"
                            oninput="formatCurrencyInput(this)"
                            >
                        </div>
                    </div>
                @endforeach
            @endif

            @if(!$readonly)
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
                    <x-input-field readonly :disabled="isset($show)" type="text" name="position" label="Jabatan" readonly value="-" />
                </div>
                <div class="col-md-4">
                    <label for="components_id" class="form-label">Nama Komponen <span class="text-danger">*</span></label>
                    <select name="components_id[]" class="form-select component-select">
                        <option value="">-- Pilih Komponen --</option>
                        @foreach($components as $c)
                            <option value="{{ $c->id }}" data-value="{{ $c->price }}" >{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="component_value" class="form-label">Nilai Komponen <span class="text-danger">*</span></label>
                    <input type="text" name="component_value[]"
                    style="font-size: 16px; padding: 10px 12px;"
                    class="form-control component-value" 
                    oninput="formatCurrencyInput(this)"
                    readonly>
                </div>
            </div>
        </template>

        {{-- ======================== POTONGAN ======================== --}}
        <hr class="my-4">
        <h5 class="fw-semibold mb-3">Potongan</h5>
        <div id="deduction-container">
            @if(isset($setting) && $setting->deductions->count())
                @foreach($setting->deductions as $ded)
                    <div class="row g-3 deduction-row">
                        <div class="col-md-4">
                            <x-input-field type="text" name="type_deduction" label="Tipe Potongan" readonly :disabled="isset($show)" value="{{ $ded->type }}" />
                        </div>
                        <div class="col-md-4">
                            <label for="deductions_id" class="form-label">Nama Potongan <span class="text-danger">*</span></label>
                            <select name="deductions_id[]" class="form-select deduction-select" {{ isset($show) ? 'disabled' : '' }}>
                                @foreach($deductions as $d)
                                    <option value="{{ $d->id }}" {{ $d->id == $ded->id ? 'selected' : '' }} data-type="{{ $d->type }}" data-value="{{ $d->price }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="deductions_value" class="form-label">Nilai Potongan <span class="text-danger">*</span></label>
                            <input type="text" name="deduction_value[]" class="form-control deduction-value" 
                            value="{{ number_format($ded->pivot->value ?? 0, 0, ',', '.') }}" 
                            oninput="formatCurrencyInput(this)" inputmode="numeric" style="font-size: 16px; padding: 10px 12px;"
                            readonly {{ isset($show) ? 'disabled' : '' }}>
                        </div>
                    </div>
                @endforeach
            @endif

            @if(!$readonly)
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
                    <x-input-field type="text" name="type_deduction" label="Tipe Potongan" readonly value="-" />
                </div>
                <div class="col-md-4">
                    <label for="deductions_id" class="form-label">Nama Potongan <span class="text-danger">*</span></label>
                    <select name="deductions_id[]" class="form-select deduction-select">
                        <option value="">-- Pilih Potongan --</option>
                        @foreach($deductions as $d)
                            <option value="{{ $d->id }}" data-type="{{ $d->type }}" data-value="{{ $d->price }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="deductions_value" class="form-label">Nilai Potongan <span class="text-danger">*</span></label>
                    <input type="text" name="deduction_value[]" class="form-control deduction-value" 
                    oninput="formatCurrencyInput(this)" style="font-size: 16px; padding: 10px 12px;"
                    readonly>
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
                                @foreach(range(1,12) as $i)
                                    <option {{ isset($setting) && $setting->billing_period == $i ? 'selected' : '' }} value="{{ $i }}">{{ $i }} Bulan</option>
                                @endforeach
                    
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Bulan Mulai</label>
                <select name="start_month" class="form-select" {{ $readonly ? 'disabled' : '' }}>
                <option value="">-- Pilih Bulan Masuk --</option>
                    @for($i=1;$i<=12;$i++)
                        <option value="{{ $i }}" {{ isset($setting) && $setting->start_month == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate(null, $i, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tahun Mulai</label>
                <select name="start_year" class="form-select" {{ $readonly ? 'disabled' : '' }}>
                    @for($y = date('Y'); $y <= date('Y')+5; $y++)
                        <option value="{{ $y }}" {{ isset($setting) && $setting->start_year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
        </div>

        {{-- ======================== BUTTON ======================== --}}
        <div class="text-start mt-5">
            @if(!$readonly)
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

{{-- ======================== SCRIPT ======================== --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const formSection = document.querySelector('#form-section');
    const unitSelect = document.querySelector('#unit');
    const officerSelect = document.querySelector('#officer');
    const readonly = @json($readonly);

    // Toggle input enable/disable
    window.toggleField = function(toggleId, inputId) {
        const toggle = document.getElementById(toggleId);
        const input = document.getElementById(inputId);
        if (toggle.checked) {
            input.disabled = false;
            input.placeholder = "Nominal";
        } else {
            input.disabled = true;
            input.value = "";
            input.placeholder = "Nonaktif";
        }
    }

    // Auto enable allowance input jika ada value (mode edit)
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
        formSection.style.display = 'none';
        if (!unitId) return;

        try {
            const res = await fetch(`/payroll-setting/officers/by-unit/${unitId}`);
            const data = await res.json();
            officerSelect.disabled = false;
            data.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.id;
                opt.textContent = o.user.name;
                officerSelect.appendChild(opt);
            });
        } catch (err) {
            console.error(err);
        }
    });

    // Load komponen & potongan setelah guru dipilih
    officerSelect.addEventListener('change', async () => {
        const officerId = officerSelect.value;
        if (!officerId) {
            formSection.style.display = 'none';
            return;
        }
        formSection.style.display = 'block';

        // Reset allowance
        ['transport_allowance', 'meal_allowance', 'communication_allowance', 'other_allowance'].forEach(id => {
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
            const data = await res.json();

            document.querySelector('[name="position"]').value = data.position_name || '-';

            // Isi komponen gaji
            const compSelects = document.querySelectorAll('.component-select');
            compSelects.forEach(select => {
                select.innerHTML = '<option value="">-- Pilih Komponen --</option>';
                data.components.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.dataset.value = c.value;
                    opt.textContent = c.name;
                    select.appendChild(opt);
                });
            });

            // Isi potongan
            const dedSelects = document.querySelectorAll('.deduction-select');
            dedSelects.forEach(select => {
                select.innerHTML = '<option value="">-- Pilih Potongan --</option>';
                data.deductions.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.dataset.value = d.value;
                    opt.textContent = d.name;
                    select.appendChild(opt);
                });
            });
        } catch (err) {
            console.error(err);
        }
    });

    // Tambah row dinamis dari template
    document.addEventListener('click', e => {
        if (e.target.closest('.add-component')) {
            const container = document.querySelector('#component-container');
            const template = document.querySelector('#component-template');
            const clone = template.content.cloneNode(true);
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

    // Auto Isi Nilai Komponen / Potongan
    document.addEventListener('change', e => {
    if (e.target.classList.contains('component-select')) {
        const val = e.target.selectedOptions[0].dataset.value || '';
        const input = e.target.closest('.component-row').querySelector('.component-value');
        input.value = val;
        formatCurrencyInput(input);
    }
    if (e.target.classList.contains('deduction-select')) {
        const val = e.target.selectedOptions[0].dataset.value || '';
        const input = e.target.closest('.deduction-row').querySelector('.deduction-value');
        input.value = val;
        formatCurrencyInput(input);
    }
    });

});
</script>
<script>
function formatCurrencyInput(input) {
    // Ambil hanya angka
    let value = input.value.replace(/[^\d]/g, '');
    if (value === '') {
        input.value = '';
        return;
    }

    // Format ribuan
    input.value = new Intl.NumberFormat('id-ID').format(value);
}

// Saat dropdown berubah → isi & format otomatis
document.addEventListener('change', e => {
    if (e.target.classList.contains('component-select')) {
        const val = e.target.selectedOptions[0].dataset.value || '';
        const input = e.target.closest('.component-row').querySelector('.component-value');
        input.value = val;
        formatCurrencyInput(input);
    }
    if (e.target.classList.contains('deduction-select')) {
        const selectedOption = e.target.selectedOptions[0];
        const val = selectedOption.dataset.value || '';
        const type = selectedOption.dataset.type || '-';

        const row = e.target.closest('.deduction-row');
        const inputValue = row.querySelector('.deduction-value');
        const inputType = row.querySelector('input[name="type_deduction"]');

        // Isi tipe potongan & nilai potongan otomatis
        inputType.value = type;
        inputValue.value = val;
        formatCurrencyInput(inputValue);
    }
});

// Sebelum submit → hapus semua titik agar dikirim sebagai angka murni
document.addEventListener('submit', function (e) {
    const inputs = document.querySelectorAll(
        '.component-value, .deduction-value, [id$="_allowance"], [name="salary"]'
    );
    inputs.forEach(input => {
        input.value = input.value.replace(/\./g, '');
    });
});
</script>

@endpush
@endsection
