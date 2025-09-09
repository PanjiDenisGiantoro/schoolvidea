<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'units';
    protected $guarded = [];

    public function yayasan(){
        return $this->belongsTo(Yayasan::class);
    }
    public function getStatusAttribute($value){
        return $value == 1 ? 'Aktif' : 'Tidak Aktif';
    }
}
