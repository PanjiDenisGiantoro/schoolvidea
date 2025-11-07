<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class setting_akun extends Model
{
    protected $table = 'setting_akuns';
    protected $guarded = [];

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'akun_id');
    }

    // Relasi untuk akun debit (double-entry accounting)
    public function akunDebit()
    {
        return $this->belongsTo(Akun::class, 'akun_debit_id');
    }

    // Relasi untuk akun kredit (double-entry accounting)
    public function akunKredit()
    {
        return $this->belongsTo(Akun::class, 'akun_kredit_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
