<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keuangan_transaksi_logs extends Model
{
    protected $table = 'keuangan_transaksi_logs';
    protected $guarded = [];

    public function pelaku()
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }

    public function transaksi()
    {
        return $this->belongsTo(Keuangan_transaksi::class, 'transaksi_id');
    }
}
