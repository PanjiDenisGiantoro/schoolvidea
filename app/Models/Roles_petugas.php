<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Roles_petugas extends SpatieRole
{
    // Relasi many-to-many ke Permission
    public function permission()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }
}
