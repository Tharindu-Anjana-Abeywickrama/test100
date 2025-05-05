<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoItem extends Model
{
    protected $fillable = ['po_id', 'sku_id', 'quantity', 'unit_price', 'total_price','discounted_price','discount'];
    
    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }
    
    public function sku() {
        return $this->belongsTo(Sku::class);
    }
}
