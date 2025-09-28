<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihanitem extends Model
{
    protected $table = 'tagihan_items';

    protected $guarded = [];
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategoritagihan::class, 'kategori_id');
    }

}
