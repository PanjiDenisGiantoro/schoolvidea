<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Unit;

class Officer extends Model
{
    protected $table = 'officers';
    protected $guarded = [];
    protected $casts = [
        'jurusan' => 'array',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }


    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    public function scopeRole($query, $role)
    {
        return $query->where('name', $role);
    }

    public function scopeWali($query)
    {
        return $query->with(['user.userRoles'])
        ->whereHas('user', function ($q) {
            $q->whereHas('userRoles', function ($r) {
                $r->where('name', 'walikelas');
            });
        });
    }



}
