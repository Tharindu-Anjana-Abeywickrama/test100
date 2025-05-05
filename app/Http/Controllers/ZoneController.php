<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $zones = Zone::all();
        return Inertia::render('Zones/Index', [
            'zones' => $zones,
        ]);
    } 

    public function getZones()
    {
    return response()->json(Zone::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:zones',
            'long_description' => 'required',
            'short_description' => 'required',
        ]);
        
        $zone = Zone::create($request->all());
        return response()->json($zone, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $zone = Zone::findOrFail($id);
        return response()->json($zone);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $zone = Zone::findOrFail($id);
        
        $request->validate([
            'code' => 'required|unique:zones,code,' . $id,
            'long_description' => 'required',
            'short_description' => 'required',
        ]);

        $zone->update($request->all());
        return response()->json($zone);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $zone = Zone::findOrFail($id);
        $zone->delete();
        return response()->json(null, 204);
    }
}
