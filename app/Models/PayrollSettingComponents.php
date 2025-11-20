<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSettingComponents extends Model
{
    protected $table = 'payroll_setting_components';

    public function component()
    {
        return $this->belongsTo(PayrollComponents::class, 'component_id');
    }

    public function payrollSetting()
    {
        return $this->belongsTo(PayrollSetting::class, 'payroll_setting_id');
    }

}
