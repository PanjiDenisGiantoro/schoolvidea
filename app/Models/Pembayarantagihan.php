<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayarantagihan extends Model
{
    protected $table = 'pembayaran_tagihan';
    protected $guarded = [];
    public function tagihanSiswa()
    {
        return $this->belongsTo(TagihanSiswa::class, 'tagihan_siswa_id');
    }
}
