<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    protected $fillable = ['sku_code', 'sku_name', 'mrp', 'distributor_price', 'weight_volume','weight_unit'];
    
    public function poItems() {
        return $this->hasMany(PoItem::class);
    }
}
