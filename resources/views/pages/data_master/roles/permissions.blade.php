@extends('layouts.app')

@section('title', 'Role Permissions')

@section('content')

    @include('partials.page-title', [
        'title' => 'Role Permissions',
        'subTitle' => $role->name,
    ])

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('roles.updatePermissions', $role->id) }}">
                @csrf
                <div class="row g-5">
                    <div class="col-lg-12">
                        <h5 class="card-title mb-3">Permissions for Role: {{ $role->name }}</h5>

                        <div class="table-responsive">
                            @foreach ($modules as $module => $permissions)
                                <div class="mb-4">
                                    <h6 class="mb-3">{{ ucfirst($module) }} Permissions</h6>
                                    <table class="table-bordered table-striped table">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" class="select-all-checkbox"
                                                        data-module="{{ $module }}"
                                                        id="select-all-{{ $module }}">
                                                    <label for="select-all-{{ $module }}" class="ml-2">Select
                                                        All</label>
                                                </th>
                                                <th>Permission Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($permissions as $permission)
                                                <tr>
                                                    <td>
                                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                                            name="permissions[]" value="{{ $permission->id }}"
                                                            @if (in_array($permission->id, $rolePermissions)) checked @endif
                                                            data-module="{{ $module }}"
                                                            id="permission-{{ $permission->id }}">
                                                    </td>
                                                    <td>{{ $permission->name }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-end mt-3 gap-1">
                            <button type="submit" class="btn btn-primary">Save Permissions</button>
                            <button type="button" class="btn btn-secondary" onclick="history.back()">Kembali</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Add "Select All" functionality for each module
        document.querySelectorAll('.select-all-checkbox').forEach(selectAllCheckbox => {
            selectAllCheckbox.addEventListener('change', function() {
                const module = this.getAttribute('data-module');
                const checkboxes = document.querySelectorAll(
                    `.permission-checkbox[data-module="${module}"]`);

                // Check or uncheck all checkboxes based on the "Select All" checkbox
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        });
    </script>
@endpush
