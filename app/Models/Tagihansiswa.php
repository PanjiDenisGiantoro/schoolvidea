<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihansiswa extends Model
{
    protected $table = 'tagihan_siswa';
    protected $guarded = [];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
    public function pembayaranTagihan()
    {
        return $this->hasMany(Pembayarantagihan::class, 'tagihan_siswa_id');
    }
    public function potonganSiswa()
    {
        return $this->hasMany(PotonganSiswa::class, 'tagihan_siswa_id', 'id');
    }

}
