<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswas';
    protected $guarded = [];
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function tahun_ajaran()
    {
        return $this->belongsTo(Tahun_ajaran::class);
    }
    public function saldo()
    {
        return $this->hasOne(Saldo_keuangan::class, 'user_id');
    }
    public function pembayaran_tagihan()
    {
        return $this->hasMany(TagihanSiswa::class, 'siswa_id');
    }
    public function pembayaranTagihan()
    {
        // Melalui TagihanSiswa
        return $this->hasManyThrough(
            PembayaranTagihan::class,  // Model target
            TagihanSiswa::class,       // Model perantara
            'siswa_id',                // Foreign key di TagihanSiswa yang mengarah ke Siswa
            'tagihan_siswa_id',        // Foreign key di PembayaranTagihan yang mengarah ke TagihanSiswa
            'id',                      // Local key di Siswa
            'id'                       // Local key di TagihanSiswa
        );
    }


}
