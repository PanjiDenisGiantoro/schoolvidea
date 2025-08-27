<div class="mb-4">
    <label class="form-label" for="{{ $id ?? $name }}">
        {{ \Illuminate\Support\Str::title($label) }}
    </label>
    <div class="position-relative w-100">
        <input
            type="{{ $type ?? 'text' }}"
            class="form-control form-control-lg rounded"
            id="{{ $id ?? $name }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder ?? '' }}"
            value="{{ old($name, $value ?? '') }}"
            {{ $attributes }}
        >

        @if(($type ?? 'text') === 'password')
            <button type="button"
                    class="btn text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2 toggle-password"
                    data-target="{{ $id ?? $name }}">
                <i class="bx bx-show fs-20 mt-1 text-muted"></i>
            </button>
        @endif

        {{-- Jika ada icon custom --}}
        @isset($icon)
            <p class="text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2 mb-0">
                <i class="{{ $icon }} fs-20 mt-1 text-muted"></i>
            </p>
        @endisset
    </div>
    @error($name)
    <small class="text-danger">{{ $message }}</small>
    @enderror
</div>
