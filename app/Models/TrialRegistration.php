<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrialRegistration extends Model
{
    protected $table = 'trial_registrations';
    protected $guarded = [];
    public function tipeUnit()
    {
        return $this->belongsTo(TipeUnit::class, 'tipe_unit_id');
    }

    // Relasi ke tabel yayasan
    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class, 'yayasan_id');
    }
}
