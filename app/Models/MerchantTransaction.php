<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantTransaction extends Model
{
    protected $table = 'merchant_transactions';

    protected $fillable = [
        'merchant_id',
        'reference_id',
        'amount',
        'type',
        'balance_after',
        'description',
    ];

    // protected $guarded = [];

    public function merchant()
    {
        return $this->belongsTo(Merchants::class, 'merchant_id');
    }

    public function withdrawal()
    {
        return $this->belongsTo(MerchantWithdrawal::class);
    }
}
