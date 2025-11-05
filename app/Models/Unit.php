<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Unit extends Model
{
    protected $table = 'units';
    protected $guarded = [];

    protected static function booted()
    {
        static::addGlobalScope('user_units', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();
                // cek apakah user punya officer & unit_id
                if ($user->officer && $user->officer->unit_id) {
                    $builder->where('id', $user->officer->unit_id);
                }
            }
        });
    }
    //$allUnits = Unit::withoutGlobalScope('user_units')->get();


    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class, 'yayasan_id', 'id');
    }
    //    public function getStatusAttribute($value)
    //    {
    //        return $value == 1 ? 'Aktif' : 'Tidak Aktif';
    //    }
    public function scopeIsactive($query)
    {
        return $query->where('status', '1');
    }
    public function tipe_unit()
    {
        return $this->belongsTo(Tipeunit::class, 'tipe_unit_id', 'id');
    }
    public function payrollSettings()
    {
        return $this->hasMany(PayrollSetting::class, 'units_id');
    }
    public function users()
    {
        return $this->hasMany(User::class, 'unit_id', 'id');
    }
}
