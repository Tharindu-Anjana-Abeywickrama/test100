<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = ['label_name', 'purches_product_id', 'discount'];

    public function sku()
    {
        return $this->belongsTo(Sku::class, 'purches_product_id');
    }
}