<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    protected $table = 'akuns';
    protected $guarded = [];
    public function parent()
    {
        return $this->belongsTo(Akun::class, 'parent_id');
    }

    public function unit()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'unit_id');
    }

}
