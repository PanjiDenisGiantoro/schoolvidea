<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantProduct extends Model
{
    protected $table = 'merchant_products';

    protected $fillable = [
        'merchant_id',
        'product_name',
        'product_category',
        'product_unit',
        'number_of_product',
        'purchase_price',
        'selling_price',
        'image',
        'status',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'number_of_product' => 'integer',
    ];

    public function merchant()
    {
        return $this->belongsTo(\App\Models\Merchants::class, 'merchant_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
