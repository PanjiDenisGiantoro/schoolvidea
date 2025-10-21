<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Potongansiswa extends Model
{
    protected $table = 'potongan_siswa';
    protected $guarded = [];
    public function potongan()
    {
        return $this->belongsTo(Potongan::class);
    }

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function tagihanSiswa()
    {
        return $this->belongsTo(TagihanSiswa::class);
    }

}
