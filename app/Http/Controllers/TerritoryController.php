<?php

namespace App\Http\Controllers;

use App\Models\Territory;
use App\Models\Zone;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Region;

class TerritoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Territories/Index', [
            'territories' => Territory::with(['zone', 'region'])->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'region_id' => 'required|exists:regions,id',
            'territory_code' => 'required|string|max:50|unique:territories,code',
            'territory_name' => 'required|string|max:255'
        ]);

        Territory::create([
            'zone_id' => $validated['zone_id'],
            'region_id' => $validated['region_id'],
            'code' => $validated['territory_code'],
            'name' => $validated['territory_name']
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Territory $territory)
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'region_id' => 'required|exists:regions,id',
            'territory_code' => 'required|string|max:50|unique:territories,code,' . $territory->id,
            'territory_name' => 'required|string|max:255'
        ]);

        $territory->update([
            'zone_id' => $validated['zone_id'],
            'region_id' => $validated['region_id'],
            'code' => $validated['territory_code'],
            'name' => $validated['territory_name']
        ]);

        return redirect()->back();
    }

    public function destroy(Territory $territory)
    {
        $territory->delete();
        return redirect()->back();
    }

    public function getRegionsByZone($zoneId)
    {
        $zone = Zone::findOrFail($zoneId);
        return $zone->regions;
    }

    public function getTerritoriesByRegion($regionId)
    {
        $region = Region::findOrFail($regionId);
        return $region->territories;
    }
}