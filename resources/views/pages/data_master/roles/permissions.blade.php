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
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Permissions for Role: {{ $role->name }}</h5>
                            <div>
                                <button type="button" class="btn btn-sm btn-success" id="select-all-permissions">
                                    <i class="ri-check-double-line"></i> Select All
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" id="deselect-all-permissions">
                                    <i class="ri-close-circle-line"></i> Deselect All
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            @foreach ($permissionGroups as $groupName => $subGroups)
                                <div class="mb-5">
                                    <h5 class="mb-3 text-primary">{{ $groupName }}</h5>

                                    @foreach ($subGroups as $subGroupName => $permissions)
                                        @if($permissions->isNotEmpty())
                                            <div class="mb-4">
                                                <h6 class="mb-2">{{ is_numeric($subGroupName) ? '' : $subGroupName }}</h6>
                                                <table class="table table-bordered table-striped table-sm">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="50">
                                                                <input type="checkbox" class="select-all-checkbox form-check-input"
                                                                    data-module="{{ Str::slug($groupName . '-' . $subGroupName) }}"
                                                                    id="select-all-{{ Str::slug($groupName . '-' . $subGroupName) }}">
                                                            </th>
                                                            <th>Permission</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($permissions as $permission)
                                                            <tr>
                                                                <td class="text-center">
                                                                    <input class="form-check-input permission-checkbox"
                                                                        type="checkbox"
                                                                        name="permissions[]"
                                                                        value="{{ $permission->name }}"
                                                                        @if (in_array($permission->name, $rolePermissions)) checked @endif
                                                                        data-module="{{ Str::slug($groupName . '-' . $subGroupName) }}"
                                                                        id="permission-{{ $permission->id }}">
                                                                </td>
                                                                <td>
                                                                    <label for="permission-{{ $permission->id }}" class="mb-0">
                                                                        {{ $permission->name }}
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    @endforeach
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
        // Select all permissions
        document.getElementById('select-all-permissions').addEventListener('click', function() {
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
            document.querySelectorAll('.select-all-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
        });

        // Deselect all permissions
        document.getElementById('deselect-all-permissions').addEventListener('click', function() {
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.querySelectorAll('.select-all-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
        });

        // Select/Deselect per module
        document.querySelectorAll('.select-all-checkbox').forEach(selectAllCheckbox => {
            selectAllCheckbox.addEventListener('change', function() {
                const module = this.getAttribute('data-module');
                const checkboxes = document.querySelectorAll(
                    `.permission-checkbox[data-module="${module}"]`);

                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        });

        // Update module checkbox when individual permission changes
        document.querySelectorAll('.permission-checkbox').forEach(permCheckbox => {
            permCheckbox.addEventListener('change', function() {
                const module = this.getAttribute('data-module');
                const moduleCheckboxes = document.querySelectorAll(
                    `.permission-checkbox[data-module="${module}"]`);
                const selectAllCheckbox = document.getElementById(`select-all-${module}`);

                // Check if all checkboxes in this module are checked
                const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked;
                }
            });
        });
    </script>
@endpush
