<?php

namespace App\Http\Controllers;

use App\Models\WarehouseType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WarehouseTypeController extends Controller
{
    public function index()
    {
        $warehouse_types = WarehouseType::paginate(10);
        return view('warehouse_types.index', compact('warehouse_types'));
    }

    public function create()
    {
        return view('warehouse_types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:warehouse_types|max:255'
        ]);

        WarehouseType::create([
            'id' => Str::uuid(),
            'name' => $request->name
        ]);

        return redirect()->route('warehouse_types.index')->with('success', 'Warehouse Type Created');
    }

    public function show(WarehouseType $warehouse_type)
    {
        return view('warehouse_types.show', compact('warehouse_type'));
    }

    public function edit(WarehouseType $warehouse_type)
    {
        return view('warehouse_types.edit', compact('warehouse_type'));
    }

    public function update(Request $request, WarehouseType $warehouse_type)
    {
        $request->validate([
            'name' => 'required|unique:warehouse_types,name,' . $warehouse_type->id . '|max:255'
        ]);

        $warehouse_type->update(['name' => $request->name]);

        return redirect()->route('warehouse_types.index')->with('success', 'Warehouse Type Updated');
    }

    public function destroy(WarehouseType $warehouse_type)
    {
        $warehouse_type->delete();
        return redirect()->route('warehouse_types.index')->with('success', 'Warehouse Type Deleted');
    }
}
