<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPayment extends Model
{
    use HasFactory;

    protected $table = 'payroll_payments';

    /**
     * Hanya field yang benar-benar perlu di-mass assignment
     */
    protected $fillable = [
        'unit_id',
        'officer_id',
        'payroll_setting_id',
        'total_earnings',
        'total_deductions',
        'net_payment',
        'payment_month',
        'notes',
        'status',
        'type',
    ];

    /**
     * Casting hanya untuk field yang perlu processing
     */
    protected $casts = [
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_payment' => 'decimal:2',
        // Jangan cast timestamps jika tidak perlu
    ];

    protected $attributes = [
        'status' => 'draft',
        'total_earnings' => 0,
        'total_deductions' => 0,
        'net_payment' => 0,
    ];

    /**
     * Eager loading default untuk optimize query
     */
    protected $with = []; // Kosongkan, lazy loading lebih efisien untuk data besar

    /**
     * RELASI - Gunakan constraints yang tepat
     */

    /**
     * Unit relation dengan select specific columns
     */
    public function setting()
    {
        return $this->belongsTo(PayrollSetting::class, 'payroll_setting_id');
    }

    public function officer()
    {
        return $this->belongsTo(Officer::class, 'officer_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function component()
    {
        return $this->belongsTo(PayrollComponents::class, 'component_id');
    }

    public function payrollType()
    {
        return $this->belongsTo(PayrollSetting::class, 'type');
    }
}
