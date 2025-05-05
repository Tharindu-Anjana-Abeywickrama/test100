<?php

namespace App\Http\Controllers;

use App\Models\FreeIssue;
use App\Models\Sku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class FreeIssueController extends Controller
{
    public function index()
    {
        $issues = FreeIssue::with(['purchesProduct', 'freeProduct'])->get();
        $skus = Sku::all();
        return Inertia::render('FreeIssues/Index', [
            'issues' => $issues, 
            'skus' => $skus
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lable_name' => 'required|string|max:255',
            'free_issue_type' => 'required|in:Flat,Multiple',
            'purches_product_id' => 'required|exists:skus,id',
            'free_product_id' => 'required|exists:skus,id',
            'purches_qty' => 'required|integer',
            'free_qty' => 'required|integer',
        ]);
    
        // Check if a free issue already exists for this product
        if (FreeIssue::where('purches_product_id', $validated['purches_product_id'])->exists()) {
            return response()->json(['error' => 'Free Issue already exists for this product'], 422);
        }
    
        $validated['created_by'] = Auth::id();
        FreeIssue::create($validated);
        
        $issues = FreeIssue::with(['purchesProduct', 'freeProduct'])->get();
    
        return response()->json(['success' => 'Free Issue Created Successfully', 'issues' => $issues]);
    }
    
    public function update(Request $request, $id)
    {
        $freeIssue = FreeIssue::findOrFail($id);
        
        $validated = $request->validate([
            'lable_name' => 'required|string|max:255',
            'free_issue_type' => 'required|in:Flat,Multiple',
            'purches_product_id' => 'required|exists:skus,id',
            'free_product_id' => 'required|exists:skus,id',
            'purches_qty' => 'required|integer',
            'free_qty' => 'required|integer',
        ]);
        
        // Check if a free issue already exists for this product (excluding current record)
        if (FreeIssue::where('purches_product_id', $validated['purches_product_id'])
            ->where('id', '!=', $id)
            ->exists()) {
            return response()->json(['error' => 'Free Issue already exists for this product'], 422);
        }
        
        $freeIssue->update($validated);

        $issues = FreeIssue::with(['purchesProduct', 'freeProduct'])->get();
        
        return response()->json(['issues' => $issues]);
    }
    
    public function destroy(Request $request, $id)
    {
        $freeIssue = FreeIssue::findOrFail($id);
        $freeIssue->delete();
        
        $issues = FreeIssue::with(['purchesProduct', 'freeProduct'])->get();
        
        return response()->json(['issues' => $issues]);
    }
}