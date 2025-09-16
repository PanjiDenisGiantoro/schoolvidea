<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keuangan_transaksi extends Model
{
    protected $table = 'keuangan_transaksis';
    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function jurnals()
    {
        return $this->hasMany(Jurnals::class, 'transaksi_id');
    }
}
