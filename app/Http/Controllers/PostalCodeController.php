<?php

namespace App\Http\Controllers;

use App\Models\PostalCode;
use App\Models\Subdistrict;
use Illuminate\Http\Request;

class PostalCodeController extends Controller
{
    
    public function index(Request $request)
    {
        // Ambil parameter pencarian
        $search = $request->get('search', '');
        $subdistricts = Subdistrict::doesntHave('postalCodes')->get();

        // Query Postal Codes dengan relasi
        $postalCodes = PostalCode::with(['subdistrict.district.city.province'])
            ->when($search, function ($query, $search) {
                return $query->where('postal_code', 'like', "%{$search}%")
                    ->orWhereHas('subdistrict', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhereHas('district', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                    ->orWhereHas('city', function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%")
                                            ->orWhereHas('province', function ($q) use ($search) {
                                                $q->where('name', 'like', "%{$search}%");
                                            });
                                    });
                            });
                    });
            })
            ->paginate(10);

        return view('postal_code.index', compact('postalCodes', 'search','subdistricts'));
    }

    // public function dataTableJson()
    // {
    //     $query = PostalCode::with('subdistrict','subdistrict.district','subdistrict.district.city')->orderBy('created_at', 'desc');

    //     $columnNames = ['postal_code','created_at'];
    //     $searchable = ['postal_code','created_at'];
    //     $bootstrap = 4;

    //     $actionButtons = [
    //         [
    //             'name' => 'Edit',
    //             'route' => 'postal-code.edit',
    //             'id' => true,
    //         ],
    //         [
    //             'name' => 'Delete',
    //             'route' => 'postal-code.destroy',
    //             'id' => true,
    //         ],
    //     ];

    //     $response = datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons, $searchable, $bootstrap);

    //     $data = $response->getData();

    //     return response()->json($data);
    // }

    public function create()
    {
        $subdistricts = Subdistrict::all();
        return view('postal_code.index', compact('subdistricts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'postal_code' => 'required|string|max:10',
            'subdistrict_id' => 'required|exists:subdistricts,id',
        ]);

        PostalCode::create($validated);
        return redirect()->route('postal-code.index')->with('store', true);
    }

    public function edit($id)
    {
        $postalCode = PostalCode::with(['subdistrict.district.city.province'])->find($id);
        $subdistricts = Subdistrict::whereDoesntHave('postalCodes')
            ->orWhere('id', $postalCode->subdistrict_id)
            ->get();

        $search = "";

        // Query Postal Codes dengan relasi
        $postalCodes = PostalCode::with(['subdistrict.district.city.province'])
            ->when($search, function ($query, $search) {
                return $query->where('postal_code', 'like', "%{$search}%")
                    ->orWhereHas('subdistrict', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhereHas('district', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                    ->orWhereHas('city', function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%")
                                            ->orWhereHas('province', function ($q) use ($search) {
                                                $q->where('name', 'like', "%{$search}%");
                                            });
                                    });
                            });
                    });
            })
            ->paginate(10);

        return view('postal_code.index', compact('postalCode', 'subdistricts', 'postalCodes'));
    }

    public function update(Request $request, PostalCode $postalcode)
    {
        $validated = $request->validate([
            'postal_code' => 'required|string|max:10',
            'subdistrict_id' => 'required|exists:subdistricts,id',
        ]);

        $postalcode->update($validated);
        return redirect()->route('postal-code.index')->with('update', true);
    }

    public function destroy(PostalCode $postalcode)
    {
        $postalcode->delete();
        return redirect()->route('postal-code.index')->with('delete', true);
    }
}