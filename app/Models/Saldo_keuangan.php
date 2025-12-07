<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saldo_keuangan extends Model
{
    protected $table = 'saldo_keuangan';
    protected $guarded = [];
    public function getStatusTtextAttribute($value)
    {
        return $value == 1 ? 'Aktif' : 'Tidak Aktif';
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'user_id', 'user_id');
    }
}
