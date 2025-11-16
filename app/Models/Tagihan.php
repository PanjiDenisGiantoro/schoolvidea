<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tagihan';
    protected $guarded = [];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Relasi ke Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    // Relasi ke Item Tagihan


    // Relasi ke Siswa (via pivot)
    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'tagihan_siswa', 'tagihan_id', 'siswa_id')
            ->withTimestamps();
    }
    public function items()
    {
        return $this->hasMany(Tagihanitem::class)->with('kategori');
    }
    public function tagihanSiswa()
    {
        return $this->hasMany(Tagihansiswa::class);
    }

    // Relasi ke Rekening (Many to Many)
    public function rekeningen()
    {
        return $this->belongsToMany(DataRekening::class, 'tagihan_rekening', 'tagihan_id', 'rekening_id')
            ->withTimestamps();
    }
}
