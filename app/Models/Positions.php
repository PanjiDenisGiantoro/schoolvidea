<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Positions extends Model
{
    protected $table = 'positions';
    protected $fillable = [
        'positions_name'
    ];
    protected $guarded = [];
    public function officers()
    {
        return $this->hasMany(Officer::class, 'position_id');
    }
}
