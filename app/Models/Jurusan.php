<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    protected $table = 'jurusans';
    protected $guarded = [];
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function tahun_ajaran()
    {
        return $this->belongsTo(Tahun_ajaran::class);
    }
//    public function getStatusAttribute($value){
//        return $value == 1 ? 'Aktif' : 'Tidak Aktif';
//    }
}
