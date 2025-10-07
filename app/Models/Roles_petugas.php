<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Models\Permission; // Import Permission model

class Roles_petugas extends SpatieRole
{
    protected $table = 'roles';

    // Guarded properties
    protected $guarded = [];

    // Relasi many-to-many ke Permission
    public function permission()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }
}
