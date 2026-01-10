<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PinOtorisasiUnit extends Model
{
    protected $table = 'pin_otorisasi_unit';

    protected $fillable = [
        'unit_id',
        'type',
        'pin_hash',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
