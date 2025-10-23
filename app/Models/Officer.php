<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


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
    public function rolePetugas()
    {
        return $this->belongsTo(Roles::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
    public function tahunAjaran()
    {
        return $this->belongsTo(Tahun_ajaran::class);
    }
    public function position()
    {
        return $this->belongsTo(Positions::class);
    }



    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    public function scopeRole($query, $role)
    {
        return $query->where('name', $role);
    }
    public function payrollSettings()
    {
        return $this->hasMany(PayrollSetting::class, 'units_id');
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
