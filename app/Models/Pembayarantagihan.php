<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayarantagihan extends Model
{
    protected $table = 'pembayaran_tagihan';
    protected $guarded = [];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'approved_at' => 'datetime',
    ];

    public function tagihanSiswa()
    {
        return $this->belongsTo(Tagihansiswa::class, 'tagihan_siswa_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'create_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function keuanganTransaksi()
    {
        return $this->hasOne(Keuangan_transaksi::class, 'referensi_tagihan_id');
    }
}
