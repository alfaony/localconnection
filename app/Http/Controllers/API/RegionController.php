<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function getCountries()
    {
        return response()->json(Country::orderBy('name')->get(['id', 'name']));
    }

    public function getProvinces(Request $request)
    {
        $query = Province::orderBy('name');
        if ($request->country_id) {
            $query->where('country_id', $request->country_id);
        }
        return response()->json($query->get(['id', 'name']));
    }

    public function getCities(Request $request)
    {
        $query = City::orderBy('name');
        if ($request->province_id) {
            $query->where('province_id', $request->province_id);
        }
        return response()->json($query->get(['id', 'name']));
    }

    public function getDistricts(Request $request)
    {
        $query = District::orderBy('name');
        if ($request->city_id) {
            $query->where('city_id', $request->city_id);
        }
        return response()->json($query->get(['id', 'name']));
    }

    public function getSubdistricts(Request $request)
    {
        $query = Subdistrict::orderBy('name');
        if ($request->district_id) {
            $query->where('district_id', $request->district_id);
        }
        return response()->json($query->get(['id', 'name']));
    }
}
