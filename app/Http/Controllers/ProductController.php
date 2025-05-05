<?php

namespace App\Http\Controllers;

use App\Models\Sku;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => Sku::all()
        ]);
    }

    public function store(Request $request)
    {  
        $validated = $request->validate([
            'sku_code' => 'required|string|unique:skus',
            'sku_name' => 'required|string',
            'mrp' => 'required|numeric',
            'distributor_price' => 'required|numeric',
            'weight_volume' => 'required|numeric',
            'weight_unit' => 'required|string'
        ]);

        Sku::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Sku $product)
    {
        $validated = $request->validate([
            'sku_code' => 'required|string|unique:skus,sku_code,' . $product->id,
            'sku_name' => 'required|string',
            'mrp' => 'required|numeric',
            'distributor_price' => 'required|numeric',
            'weight_volume' => 'required|numeric',
            'weight_unit' => 'required|string'
        ]);

        $product->update($validated);

        return redirect()->back();
    }

    public function destroy(Sku $product)
    {
        $product->delete();
        return redirect()->back();
    }
}