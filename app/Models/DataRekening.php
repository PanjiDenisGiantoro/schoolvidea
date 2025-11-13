<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataRekening extends Model
{
    protected $table = 'data_rekening';
    protected $guarded = [];
    public function unit()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'unit_id');
    }
}
