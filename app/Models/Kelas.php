<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $guarded = [];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }
}
