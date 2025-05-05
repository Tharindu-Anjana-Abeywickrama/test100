<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'purchase_order_id', 
        'invoice_number', 
        'date', 
        'total_amount', 
        'status',
        'remarks',
        'created_by'
    ];
    
    /**
     * Get the purchase order associated with the invoice.
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }
    
    /**
     * Get the user who created the invoice.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}