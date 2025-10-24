<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $guarded = [];

    public function getStatusAttribute($value){
        return $value == '1' ? 'Aktif' : 'Tidak Aktif';
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(Tahun_ajaran::class);
    }
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }
    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }

}
