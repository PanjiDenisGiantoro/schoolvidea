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

        public function settings()
    {
        return $this->belongsToMany(
            PayrollSetting::class,
            'payroll_setting_deductions',
            'deduction_id',
            'payroll_setting_id'
        );
    }
    public function deduction()
    {
        return $this->hasOne(PayrollDeductions::class, 'id', 'id');
    }
}
