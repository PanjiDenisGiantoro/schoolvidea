<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as SpatiePermission;
use App\Models\Roles_petugas; // Pastikan Anda mengimport model Roles_petugas

class Permission extends SpatiePermission
{
    // Relasi many-to-many ke Roles_petugas
    public function role()
    {
        return $this->belongsToMany(Roles_petugas::class, 'role_has_permissions');
    }
}
