<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mpdf\Tag\P;

class Keuangan_transaksi extends Model
{
    protected $table = 'keuangan_transaksis';
    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs()
    {
        return $this->hasMany(Keuangan_transaksi_logs::class, 'transaksi_id');
    }

    public function jurnals()
    {
        return $this->hasMany(Jurnals::class, 'transaksi_id');
    }
    public function jurnal()
    {
        return $this->hasMany(Jurnals::class, 'transaksi_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }
    public function penerima()
    {
        return $this->morphTo(__FUNCTION__, 'penerima_tipe', 'penerima_id');
    }

    public function pembayaranTagihan()
    {
        return $this->belongsTo(Pembayarantagihan::class, 'referensi_tagihan_id');
    }
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeHidePendingWithoutBukti($query)
    {
        return $query->where(function ($q) {
            $q->where('status_verifikasi', '!=', 'pending')

              ->orWhere(function ($sub) {
                  $sub->where('status_verifikasi', 'pending')
                      ->whereNotIn('jenis_transaksi', [
                          'setoran_tabungan',
                          'pembayaran',
                          'tagihan'
                      ]);
              })

              ->orWhere(function ($sub) {
                  $sub->where('status_verifikasi', 'pending')
                      ->whereIn('jenis_transaksi', [
                          'setoran_tabungan',
                          'pembayaran',
                          'tagihan'
                      ])
                      ->whereRaw("bukti_transfer IS NOT NULL")
                      ->whereRaw("TRIM(bukti_transfer) != ''")
                      ->whereRaw("bukti_transfer NOT IN ('null', '[]')");
              });
        });
    }
    public function scopeHidePending($query) {
        return $query->where(function ($q) {
            $q->where('status_verifikasi', '!=', 'pending');
        });
    }
}
