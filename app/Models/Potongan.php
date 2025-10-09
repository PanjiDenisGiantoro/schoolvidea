<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Potongan extends Model
{
    protected $table = 'potongan';
    protected $guarded = [];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function kategoriTagihan()
    {
        return $this->belongsTo(KategoriTagihan::class);
    }

    public function potonganSiswa()
    {
        return $this->hasMany(PotonganSiswa::class);
    }
}
