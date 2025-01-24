<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\Country;

use Illuminate\Http\Request;
use App\Helpers\Access;

class ProvinceController extends Controller
{
    public function index()
    {
        $countries = Country::all();
        return view('province.index', compact('countries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Province::create($request->only('name','country_id'));
        return redirect()->route('city.index')->with('success', true);
    }

    public function edit(Province $province)
    {
        $countries = Country::all();
        $defaultCity = $province->cities()->get();
        return view('province.index', compact('province', 'countries','defaultCity'));
    }

    public function update(Request $request, Province $province)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $province->update($request->only('name','default_city_id'));
        return redirect()->route('province.index')->with('update', true);

    }

    public function destroy(Province $province)
    {
        $province->delete();
        return redirect()->route('province.index')->with('delete', true);
    }

    public function dataTableJson()
    {
        // Query untuk DataTable
        $query = Province::query()->with('defaultCity'); // Ganti model dengan yang sesuai
        $query->orderBy('created_at', 'desc');

        // Map kolom untuk DataTable (disesuaikan dengan struktur tabel)
        $columnNames = ['name', 'created_at'];

        // Kolom yang dapat dicari
        $searchable = [
            'name',
            'defaultCity.name'
        ];

        // Versi bootstrap (4 atau 5)
        $bootstrap = 4;

        // Action buttons untuk setiap row
        $actionButtons = [];

        if (Access::can('edit', 'provinces')) {
            $edit = [
                'name' => 'Edit',
                'route' => 'province.edit',
                'id' => true,
            ];

            array_push($actionButtons, $edit);
        }

        if (Access::can('destroy', 'provinces')) {
            $destroy = [
                'name' => 'Delete',
                'route' => 'province.destroy',
                'id' => true,
            ];

            array_push($actionButtons, $destroy);
        }

        // Format DataTable dengan pencarian relasi
        $response = datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons, $searchable, $bootstrap);

        // Format tambahan pada setiap row
        $data = $response->getData();
        foreach ($data->data as $item) {
            $item->created_at = date('d-m-Y H:i:s', strtotime($item->created_at));
            $item->updated_at = date('d-m-Y H:i:s', strtotime($item->updated_at));
        }

        // Kembalikan data sebagai JSON
        return response()->json($data);
    }

    public function select2(Request $request)
    {
        $query = $request->input('q'); // Query pencarian
        $page = $request->input('page', 1); // Halaman pagination
        $perPage = 5; // Jumlah item per halaman

        // Query pencarian
        $provinces = Province::query()
            ->where('name', 'LIKE', "%$query%")
            ->paginate($perPage, ['*'], 'page', $page);

        // Format hasil untuk Select2
        $results = $provinces->getCollection()->map(function ($province) {
            return [
                'id' => $province->id,
                'text' => $province->name,
            ];
        });

        return response()->json([
            'items' => $results,
            'pagination' => ['more' => $provinces->hasMorePages()],
        ]);
    }
}