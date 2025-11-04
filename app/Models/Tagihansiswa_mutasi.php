<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihansiswa_mutasi extends Model
{
    protected $table = 'tagihan_siswa_mutasi';
    protected $guarded = [];

    protected $casts = [
        'nominal_sebelum' => 'decimal:2',
        'nominal_perubahan' => 'decimal:2',
        'nominal_sesudah' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function tagihanSiswa()
    {
        return $this->belongsTo(Tagihansiswa::class, 'tagihan_siswa_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
