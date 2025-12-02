<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranTagihanDetail extends Model
{
    protected $table = 'pembayaran_tagihan_detail';
    protected $guarded = [];

    protected $casts = [
        'jumlah_bayar_detail' => 'decimal:2',
    ];

    /**
     * Relasi ke Pembayarantagihan (Master)
     */
    public function pembayaran()
    {
        return $this->belongsTo(Pembayarantagihan::class, 'pembayaran_id');
    }

    /**
     * Relasi ke Tagihansiswa
     */
    public function tagihanSiswa()
    {
        return $this->belongsTo(Tagihansiswa::class, 'tagihan_siswa_id');
    }

    /**
     * Scope untuk filter by head_tagihan
     */
    public function scopeByHeadTagihan($query, $headTagihan)
    {
        return $query->where('head_tagihan', $headTagihan);
    }

    /**
     * Scope untuk filter by pembayaran_id
     */
    public function scopeByPembayaran($query, $pembayaranId)
    {
        return $query->where('pembayaran_id', $pembayaranId);
    }

    /**
     * Get formatted jumlah_bayar_detail
     */
    public function getFormattedJumlahBayarAttribute()
    {
        return number_format($this->jumlah_bayar_detail, 0, ',', '.');
    }
}
