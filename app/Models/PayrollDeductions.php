<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollDeductions extends Model
{
    //
    use HasFactory;

    protected $table = 'payroll_deductions';
    protected $fillable = [
        'name',
        'price',
        'type',
        'status',
        'description',
    ];
}
