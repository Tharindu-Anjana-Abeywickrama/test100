<?php

namespace App\Http\Controllers;

use App\Models\Sku;
use Illuminate\Http\Request;

class SkusController extends Controller
{
    public function fetchSkus()
    {
        $skus = Sku::select('id', 'sku_code', 'sku_name', 'mrp', 'distributor_price', 'weight_volume', 'weight_unit')
            ->orderBy('sku_name')
            ->get();
        return response()->json($skus);
    }
}