@extends('layouts.app')
@section('title', isset($roles) ? (isset($show) && $show ? 'Lihat Roles' : 'Edit Roles') : 'Tambah Roles')

@section('content')
    @include('partials.page-title', [
        'title' => isset($roles) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Roles'
    ])
    <div class="card">
        <div class="card-body">
            <form id="tahunAjaranForm"
                  action="{{ isset($roles) ? route('roles.update', $roles->id) : route('roles.store') }}"
                  method="POST">
                @csrf
                @if(isset($roles))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-field type="text" name="name" label="Roles"
                                       placeholder="Masukkan Roles" icon="bx bx-user "
                                       :value="old('name', $roles->name ?? '')" required />
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($roles) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ url('roles/') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        @if(isset($show) && $show)
        document.addEventListener('DOMContentLoaded', function() {
            const formElements = document.querySelectorAll('#tahunAjaranForm input, #tahunAjaranForm select, #tahunAjaranForm button[type="submit"]');
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
