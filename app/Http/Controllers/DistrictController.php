<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;

use Illuminate\Http\Request;
use App\Helpers\Access;

class DistrictController extends Controller
{
    public function index()
    {
        return view('district.index');
    }

    public function dataTableJson()
    {
        $query = District::with(['city.province', 'defaultSubdistrict']);

        $searchable = ['name', 'city.name', 'city.province.name'];
        $columnNames = ['name', 'city.name', 'city.province.name'];
        $bootstrap = 4;

        $actionButtons = [];

        if (Access::can('edit', 'districts')) 
        {
            $edit = [
                'name' => 'Edit',
                'route' => 'district.edit',
                'id' => true,
            ];

            array_push($actionButtons, $edit);
        }

        if (Access::can('destroy', 'districts')) 
        {
            $delete = [
                'name' => 'Delete',
                'route' => 'district.destroy',
                'id' => true,
            ];

            array_push($actionButtons, $delete);
        }

        $response = datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons, $searchable, $bootstrap);

        $data = $response->getData();
        foreach ($data->data as $item) {
            $item->city_province = $item->city->name . ' - ' . $item->city->province->name;
        }

        return response()->json($data);
    }

    public function create()
    {
        $cities = City::all();
        return view('district.index', compact('cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
        ]);

        District::create($validated);
        return redirect()->route('subdistrict.index')->with('store', true);
    }

    public function edit(District $district)
    {
        $listSubdistrict = $district->subdistricts()->get();

        return view('district.index', compact('district','listSubdistrict'));
    }

    public function update(Request $request, District $district)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'default_subdistrict_id' => 'nullable|exists:subdistricts,id',
        ]);

        $district->update($validated);
        return redirect()->route('district.index')->with('update', true);
    }

    public function destroy(District $district)
    {
        $district->delete();
        return redirect()->route('district.index')->with('delete', true);
    }

    public function select2(Request $request)
    {
        $query = $request->get('q');
        $cityId = $request->get('city_id');
        if ($cityId) {
            $districts = District::where('city_id', $cityId)
                ->where('name', 'like', "%$query%")
                ->limit(10)
                ->get();
        } else {
            $districts = District::where('name', 'like', "%$query%")
                ->whereHas('city', function ($q) use ($query) {
                    $q->whereHas('province', function ($p) use ($query) {
                        $p->where('name', 'like', "%$query%");
                    });
                })
                ->limit(10)
                ->get();
        }

        return response()->json([
            'results' => $districts->map(fn($district) => ['id' => $district->id, 'text' => $district->full_name]),
        ]);
    }
}