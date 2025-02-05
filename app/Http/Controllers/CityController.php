<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;
use Illuminate\Http\Request;
use App\Helpers\Access;

class CityController extends Controller
{
    public function index()
    {
        $provinces = Province::all();
        return view('city.index', compact('provinces'));
    }

    public function dataTableJson()
    {
        $query = City::with('province','defaultDistrict')->orderBy('name', 'asc');

        $columnNames = ['name', 'province.name'];
        $searchable = ['name', 'province.name'];
        $bootstrap = 4;

        $actionButtons = [
            [
                'name' => 'Edit',
                'route' => 'city.edit',
                'id' => true,
            ],
            [
                'name' => 'Delete',
                'route' => 'city.destroy',
                'id' => true,
            ],
        ];
        // $actionButtons = [];

        // if (Access::can('edit', 'provinces')) {
        //     $edit = [
        //         'name' => 'Edit',
        //         'route' => 'city.edit',
        //         'id' => true,
        //     ];

        //     array_push($actionButtons, $edit);
        // }

        // if (Access::can('destroy', 'provinces')) {
        //     $destroy = [
        //         'name' => 'Delete',
        //         'route' => 'city.destroy',
        //         'id' => true,
        //     ];

        //     array_push($actionButtons, $destroy);
        // }

        $response = datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons, $searchable, $bootstrap);

        $data = $response->getData();
        
        return response()->json($data);
    }

    public function create()
    {
        $provinces = Province::all();
        return view('city.create', compact('provinces'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
        ]);

        City::create($request->only('name', 'province_id'));

        return redirect()->route('district.index')->with('store', true);
    }

    public function edit($id)
    {
        $city = City::findOrFail($id);
        $provinces = Province::all();
        $defaultDistrict = $city->districts()->get();

        return view('city.index', compact('city', 'provinces','defaultDistrict'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
        ]);

        $city = City::findOrFail($id);
        $city->update($request->only('name', 'province_id', 'default_district_id'));

        return redirect()->route('city.index')->with('update', true);

    }

    public function destroy($id)
    {
        $city = City::findOrFail($id);
        $city->delete();

        return redirect()->route('city.index')->with('delete', true);
    }

    public function select2(Request $request)
    {
        $query = $request->get('q');
        $provinceId = $request->get('province_id');
        if ($provinceId) 
        {
            $cities = City::where('province_id', $provinceId)
                ->where('name', 'like', "%$query%")
                ->limit(10)
                ->get();
        } else 
        {
            $cities = City::where('name', 'like', "%$query%")
                ->whereHas('province', fn($q) => $q->where('name', 'like', "%$query%"))
                ->limit(10)
                ->get();
        }

        return response()->json([
            'results' => $cities->map(fn($city) => ['id' => $city->id, 'text' => $city->full_name]),
        ]);
    }
}