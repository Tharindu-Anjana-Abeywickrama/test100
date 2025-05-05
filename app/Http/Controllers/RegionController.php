<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 

use App\Models\Region;
use Inertia\Inertia;

class RegionController extends Controller
{
 // GET: /regions
 public function index()
 {
     $regions = Region::with('zone')->latest()->get();
     return Inertia::render('Regions/Index', [
            'regions' => $regions,
     ]);
 }

 // POST: /regions
 public function store(Request $request)
 {
     $request->validate([
         'zone_id' => 'required|exists:zones,id',
         'region_code' => 'required|string|max:50',
         'region_name' => 'required|string|max:255',
     ]);

     Region::create([
         'zone_id' => $request->zone_id,
         'code' => $request->region_code,
         'name' => $request->region_name,
     ]);

     return response()->json(['message' => 'Region saved successfully']);
 }

 // GET: /regions/{id}/edit
 public function edit($id)
 {
     $region = Region::findOrFail($id);
     return response()->json($region);
 }

 // PUT: /regions/{id}
 public function update(Request $request, $id)
 {
     $request->validate([
         'zone_id' => 'required|exists:zones,id',
         'region_code' => 'required|string|max:50',
         'region_name' => 'required|string|max:255',
     ]);

     $region = Region::findOrFail($id);
     $region->update([
         'zone_id' => $request->zone_id,
         'region_code' => $request->region_code,
         'region_name' => $request->region_name,
     ]);

     return response()->json(['message' => 'Region updated successfully']);
 }

 // DELETE: /regions/{id}
 public function destroy($id)
 {
     $region = Region::findOrFail($id);
     $region->delete();

     return response()->json(['message' => 'Region deleted successfully']);
 }
}
