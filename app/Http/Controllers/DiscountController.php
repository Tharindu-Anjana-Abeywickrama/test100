<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Discount;
use App\Models\Sku;
use Inertia\Inertia;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::with('sku')->get();
        $skus = Sku::select('id', 'sku_name')->get();
        return Inertia::render('Discounts/Index', [
            'discounts' => $discounts,
            'skus' => $skus
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label_name' => 'required|string|max:255',
            'purches_product_id' => 'required|exists:skus,id',
            'discount' => 'required|numeric|min:0|max:100'
        ]);

        $discount = Discount::create($validated);

        // Return the newly created discount with its relationship
        $discount->load('sku');
        return response()->json(['success' => true, 'discount' => $discount]);
    }
    
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'label_name' => 'required|string|max:255',
            'purches_product_id' => 'required|exists:skus,id',
            'discount' => 'required|numeric|min:0|max:100'
        ]);
        
        $discount = Discount::findOrFail($id);
        $discount->update($validated);
        
        // Return the updated discount with its relationship
        $discount->load('sku');
        return response()->json(['success' => true, 'discount' => $discount]);
    }
    
    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);
        $discount->delete();
        
        return response()->json(['success' => true, 'id' => $id]);
    }
    
    public function getDiscounts()
    {
        $discounts = Discount::with('sku')->get();
        return response()->json($discounts);
    }
}