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

    // Accessor untuk mendapatkan kode_tagihan dari tagihan_siswa->tagihan
    public function getKodeTagihanAttribute()
    {
        return $this->tagihanSiswa?->tagihan?->id ?? null;
    }

    // Accessor untuk mendapatkan nama_tagihan dari tagihan_siswa->tagihan
    public function getNamaTagihanAttribute()
    {
        return $this->tagihanSiswa?->tagihan?->jenis_tagihan ?? null;
    }

    // Accessor untuk mendapatkan kategori_tagihan dari tagihan_siswa->tagihanItem->kategori
    public function getKategoriTagihanAttribute()
    {
        return $this->tagihanSiswa?->tagihanItem?->kategori?->nama_kategori ?? null;
    }

    // Accessor untuk mendapatkan data_rekenings dari tagihan_siswa->tagihan->rekening
    public function getDataRekeningAttribute()
    {
        return $this->tagihanSiswa?->tagihan?->rekening ?? null;
    }

    /**
     * Relasi ke PembayaranTagihanDetail (detail pembayaran multiple)
     */
    public function pembayaranDetail()
    {
        return $this->hasMany(PembayaranTagihanDetail::class, 'pembayaran_id');
    }

    /**
     * Relasi ke pembayaran master jika ini adalah pembayaran detail
     */
    public function parentPembayaran()
    {
        return $this->belongsTo(Pembayarantagihan::class, 'parent_pembayaran_id');
    }

    /**
     * Relasi ke pembayaran detail jika ini adalah pembayaran master
     */
    public function childPembayaran()
    {
        return $this->hasMany(Pembayarantagihan::class, 'parent_pembayaran_id');
    }

    /**
     * Scope untuk filter by head_tagihan
     */
    public function scopeByHeadTagihan($query, $headTagihan)
    {
        return $query->where('head_tagihan', $headTagihan);
    }

    /**
     * Scope untuk filter pembayaran master saja
     */
    public function scopeMaster($query)
    {
        return $query->where('is_master', true);
    }

    /**
     * Scope untuk filter pembayaran detail saja
     */
    public function scopeDetail($query)
    {
        return $query->where('is_master', false);
    }
}
