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

    public function settings()
    {
        return $this->belongsToMany(
            PayrollSetting::class,
            'payroll_setting_components',
            'component_id',
            'payroll_setting_id'
        );
    }
    public function component()
    {
        return $this->hasOne(PayrollComponents::class, 'id', 'id');
    }


}
