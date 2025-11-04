<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tahun_ajaran extends Model
{
    protected $table = 'tahun_ajarans';
    protected $guarded = [];

    public function scopeIsactive($query)
    {
        return $query->where('status', 1);
    }
    public function getStatusAttribute($value)
    {
        return $value == 1 ? 'Aktif' : 'Tidak Aktif';
    }
    public function getTanggalMulaiAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('M-Y');
    }

    public function getTanggalSelesaiAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('M-Y');
    }
    public function Officers()
    {
        return $this->hasMany(Officer::class, 'tahun_ajaran_id');
    }
    public function Siswas()
    {
        return $this->hasMany(Siswa::class, 'tahun_ajaran_id');
    }
}
