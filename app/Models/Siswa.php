<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswas';

    protected $fillable = [
        'nisn', 'nama', 'user_id', 'unit_id', 'kelas_id',
        'status', 'va_siswa', 'rfid_no', 'nik', 'jenis_kelamin',
        'agama', 'no_hp_ortu', 'nama_ortu', 'bank', 'no_rekening',
        'nis', 'qrcode', 'qrcode_image', 'username', 'password'
    ];

    // Cast JSON kolom 'va_siswa' menjadi array
    protected $casts = [
        'va_siswa' => 'array',
    ];
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
        return $this->hasMany(Tagihansiswa::class, 'siswa_id');
    }
    public function pembayaranTagihan()
    {
        // Melalui TagihanSiswa
        return $this->hasManyThrough(
            Pembayarantagihan::class,  // Model target
            Tagihansiswa::class,       // Model perantara
            'siswa_id',                // Foreign key di TagihanSiswa yang mengarah ke Siswa
            'tagihan_siswa_id',        // Foreign key di PembayaranTagihan yang mengarah ke TagihanSiswa
            'id',                      // Local key di Siswa
            'id'                       // Local key di TagihanSiswa
        );
    }
}
