<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreeIssue extends Model
{
    protected $fillable = [
        'lable_name',
        'free_issue_type',
        'purches_product_id',
        'free_product_id',
        'purches_qty',
        'free_qty',
        'created_by'
    ];

    public function purchesProduct()
    {
        return $this->belongsTo(Sku::class, 'purches_product_id');
    }

    public function freeProduct()
    {
        return $this->belongsTo(Sku::class, 'free_product_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}