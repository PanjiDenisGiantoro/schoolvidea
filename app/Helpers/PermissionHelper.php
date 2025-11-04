<?php

if (!function_exists('hasPermission')) {
    /**
     * Check if user has specific permission
     *
     * @param string|array $permission
     * @return bool
     */
    function hasPermission($permission)
    {
        if (!auth()->check()) {
            return false;
        }

        if (is_array($permission)) {
            return auth()->user()->hasAnyPermission($permission);
        }

        return auth()->user()->hasPermissionTo($permission);
    }
}

if (!function_exists('hasRole')) {
    /**
     * Check if user has specific role
     *
     * @param string|array $role
     * @return bool
     */
    function hasRole($role)
    {
        if (!auth()->check()) {
            return false;
        }

        if (is_array($role)) {
            return auth()->user()->hasAnyRole($role);
        }

        return auth()->user()->hasRole($role);
    }
}

if (!function_exists('hasAnyPermission')) {
    /**
     * Check if user has any of the given permissions
     *
     * @param array $permissions
     * @return bool
     */
    function hasAnyPermission(array $permissions)
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->hasAnyPermission($permissions);
    }
}

if (!function_exists('hasAllPermissions')) {
    /**
     * Check if user has all of the given permissions
     *
     * @param array $permissions
     * @return bool
     */
    function hasAllPermissions(array $permissions)
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->hasAllPermissions($permissions);
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * Check if user is superadmin
     *
     * @return bool
     */
    function isSuperAdmin()
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->hasRole('superadmin');
    }
}

if (!function_exists('canViewMenu')) {
    /**
     * Check if user can view a menu based on permissions
     *
     * @param string|array $permissions
     * @return bool
     */
    function canViewMenu($permissions)
    {
        if (!auth()->check()) {
            return false;
        }

        // Super admin can see all menus
        if (isSuperAdmin()) {
            return true;
        }

        // Check if user has any of the permissions
        if (is_array($permissions)) {
            return hasAnyPermission($permissions);
        }

        return hasPermission($permissions);
    }
}
