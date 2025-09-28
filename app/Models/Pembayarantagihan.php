<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayarantagihan extends Model
{
    protected $table = 'pembayaran_tagihan';
    protected $guarded = [];
    public function tagihanSiswa()
    {
        return $this->belongsTo(Tagihansiswa::class, 'tagihan_siswa_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'create_by');

    }

}
