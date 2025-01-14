<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\Warehouse;
use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZoneController extends Controller
{
    public function index()
    {
        $zones = Zone::byCompany(Auth::user()->company_id)->with(['warehouse', 'sensors'])->paginate(10);
        $warehouses = Warehouse::byCompany(Auth::user()->company_id)->get();
        $sensors = Sensor::byCompany(Auth::user()->company_id)->get();

        return view('zone.index', compact('warehouses', 'sensors', 'zones'));
    }

    public function create()
    {
        $warehouses = Warehouse::byCompany(Auth::user()->company_id)->get();
        $sensors = Sensor::byCompany(Auth::user()->company_id)->get();

        return view('zone.index', compact('warehouses', 'sensors'));
    }

    public function store(Request $request)
    {
        dd($request->all());
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'sensors' => 'array',
        ]);

        $zone = Zone::create([
            'warehouse_id' => $request->warehouse_id,
            'name' => $request->name,
        ]);

        $zone->sensors()->sync($request->sensors);

        return redirect()->route('zone.index')->with('store', true);
    }

    public function edit(Zone $zone)
    {
        $zones = Zone::with(['warehouse', 'sensors'])->paginate(10);
        $warehouses = Warehouse::all();
        $sensors = Sensor::all();
        return view('zone.index', compact('zones', 'zone', 'warehouses', 'sensors'));
    }

    public function update(Request $request, Zone $zone)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'sensors' => 'array',
        ]);

        $zone->update($request->all());

        $zone->sensors()->sync($request->sensors);

        return redirect()->route('zone.index')->with('update', true);
    }

    public function destroy(Zone $zone)
    {
        $zone->delete();
        return redirect()->route('zone.index')->with('delete', true);
    }
}