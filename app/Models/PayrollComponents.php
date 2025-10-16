<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollComponents extends Model
{
    //
    use HasFactory;

    protected $table = 'payroll_components';
    protected $fillable = [
        'name',
        'price',
        'status',
        'description',
    ];
}
