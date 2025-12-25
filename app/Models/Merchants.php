<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Merchants extends Authenticatable
{
    protected $table = 'merchants';

    protected $fillable = [
        'nama_merchant',
        'kode_merchant',
        'no_hp',
        'unit_id',
        'status',
        'status',
        'image',
        'created_by',
        'password',
        'jenis',
        'pemilik',
        'saldo_aktif',
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'saldo_aktif' => 'decimal:2',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transaction()
    {
        return $this->hasMany(MerchantTransaction::class);
    }

    public function withdrawal()
    {
        return $this->belongsTo(MerchantWithdrawal::class);
    }

    public function getAuthIdentifierName()
    {
        return 'no_hp';
    }
}
