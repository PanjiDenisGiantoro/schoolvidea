<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Yayasan extends Model
{
    protected $table = 'yayasans';
    protected $guarded = [];

    public function getStatusAttribute($value){
            return $value == 1 ? 'Aktif' : 'Tidak Aktif';
    }
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }
    public function units()
    {
        return $this->hasMany(Unit::class, 'yayasan_id', 'id');
    }
}
