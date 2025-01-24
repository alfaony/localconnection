<?php

namespace App\Http\Controllers;

use App\Models\Subdistrict;
use App\Models\District;
use Illuminate\Http\Request;
use App\Helpers\Access;

class SubdistrictController extends Controller
{
    public function index()
    {
        $districts = District::all();
        return view('subdistrict.index', compact('districts'));
    }

    public function dataTableJson()
    {
        $query = Subdistrict::with('district','district.city','district.city.province')->orderBy('created_at', 'desc');

        $columnNames = ['name', 'created_at'];
        $searchable = ['name', 'district.name'];
        $bootstrap = 4;

        $actionButtons = [];
        
        if (Access::can('edit', 'subdistricts')) {
            $edit = [
                'name' => 'Edit',
                'route' => 'subdistrict.edit',
                'id' => true,
            ];
            array_push($actionButtons, $edit);
        }
        if (Access::can('destroy', 'subdistricts')) {
            $delete = [
                'name' => 'Delete',
                'route' => 'subdistrict.destroy',
                'id' => true,
            ];
            array_push($actionButtons, $delete);
        }


        $response = datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons, $searchable, $bootstrap);

        $data = $response->getData();
        foreach ($data->data as $item) {
            $item->full_location = $item->district->name . ' - ' . $item->district->city->name . ' - ' . $item->district->city->province->name;
        }

        return response()->json($data);
    }

    public function create()
    {
        $districts = District::all();
        return view('subdistrict.create', compact('districts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
        ]);

        Subdistrict::create($validated);
        return redirect()->route('subdistrict.index')->with('store',true);
    }

    public function edit(Subdistrict $subdistrict)
    {
        $districts = District::all();
        return view('subdistrict.index', compact('subdistrict', 'districts'));
    }

    public function update(Request $request, Subdistrict $subdistrict)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
        ]);

        $subdistrict->update($validated);
        return redirect()->route('subdistrict.index')->with('update',true);
    }

    public function destroy(Subdistrict $subdistrict)
    {
        $subdistrict->delete();
        return redirect()->route('subdistrict.index')->with('delete',true);
    }
}