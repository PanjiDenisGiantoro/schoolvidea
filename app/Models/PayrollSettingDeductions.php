<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSettingDeductions extends Model
{
     protected $table = 'payroll_setting_deductions';
            public function deduction()
    {
        return $this->belongsTo(PayrollDeductions::class, 'component_id');
    }

        public function payrollSetting()
    {
        return $this->belongsTo(PayrollSetting::class, 'payroll_setting_id');
    }
}
