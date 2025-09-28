<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategoritagihan extends Model
{
    protected $table = 'kategoritagihans';
    protected $guarded = [];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function tahun_ajaran()
    {
        return $this->belongsTo(Tahun_ajaran::class);
    }
    public function items()
    {
        return $this->hasMany(Kategoritagihan::class);
    }

}
