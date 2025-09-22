<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurnals extends Model
{
    protected $table = 'jurnals';
    protected $guarded = [];


    public function transaksi()
    {
        return $this->belongsTo(Keuangan_transaksi::class, 'transaksi_id');
    }

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'akun_id');
    }

}
