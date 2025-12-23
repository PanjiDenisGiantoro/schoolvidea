<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantWithdrawal extends Model
{
    use HasFactory;

    protected $table = 'merchant_withdrawals';

    protected $fillable = [
        'merchant_id',
        'reference_id',
        'amount',
        'bank_name',
        'account_number',
        'account_name',
        'status',
        'requested_at', 'processed_at',
        'processed_by',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public function merchant()
    {
        return $this->belongsTo(Merchants::class);
    }

    public function transaction()
    {
        return $this->hasOne(MerchantTransaction::class, 'withdrawal_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
