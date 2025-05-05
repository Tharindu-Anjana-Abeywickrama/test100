<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['user_id', 'territory_id', 'po_number', 'date', 'remark', 'total_amount','created_by'];
    
    public function items() {
        return $this->hasMany(PoItem::class,'po_id');
    } 

    // public function poItems()
    // {
    // return $this->hasMany(PoItem::class, 'po_id');
    // }
    
    public function distributor() {
        return $this->belongsTo(User::class, 'user_id');
    } 

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function territory() {
        return $this->belongsTo(Territory::class, 'territory_id');
    }

    public function region() {
        return $this->belongsTo(Region::class, 'region_id');
    }
}
