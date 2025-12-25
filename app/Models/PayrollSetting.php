<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'units_id',
        'officers_id',
        'teaching_hours',
        'teaching_hours_total',
        'salary',
        'transport_allowance',
        'meal_allowance',
        'staff_allowance',
        'other_allowance',
        'billing_period',
        'start_month',
        'start_year',
        'type',
        'status',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'units_id');
    }

    public function officer()
    {
        return $this->belongsTo(Officer::class, 'officers_id');
    }

    public function components()
    {
        return $this->belongsToMany(PayrollComponents::class, 'payroll_setting_components', 'payroll_setting_id', 'component_id')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function deductions()
    {
        return $this->belongsToMany(PayrollDeductions::class, 'payroll_setting_deductions', 'payroll_setting_id', 'deduction_id')
            ->withPivot('value', 'type')
            ->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(PayrollPayment::class);
    }
}