<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::byCompany(Auth::user()->company_id)->with('warehouseType')->paginate(10);
        $warehouse_types = WarehouseType::all();

        return view('warehouse.index', compact('warehouses', 'warehouse_types'));
    }

    public function create()
    {
        $warehouse_types = WarehouseType::all();
        return view('warehouse.create', compact('warehouse_types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'location' => 'required',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'warehouse_type_id' => 'required|exists:warehouse_types,id'
        ]);

        Warehouse::create([
            'name' => $request->name,
            'location' => $request->location,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'warehouse_type_id' => $request->warehouse_type_id,
            'user_id' => auth()->user()->id
        ]);

        return redirect()->route('warehouse.index')->with('store', true);
    }

    public function show(Warehouse $warehouse)
    {
        return view('warehouse.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse)
    {
        $warehouse_types = WarehouseType::all();
        $warehouses = Warehouse::byCompany(Auth::user()->company_id)->with('warehouseType')->paginate(10);
        return view('warehouse.index', compact('warehouse', 'warehouse_types','warehouses'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'name' => 'required|max:255',
            'location' => 'required',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'warehouse_type_id' => 'required|exists:warehouse_types,id'
        ]);

        $warehouse->update([
            'name' => $request->name,
            'location' => $request->location,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'warehouse_type_id' => $request->warehouse_type_id
        ]);

        return redirect()->route('warehouse.index')->with('update', true);
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()->route('warehouse.index')->with('delete', true);
    }
}