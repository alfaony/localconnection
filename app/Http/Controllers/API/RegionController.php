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
    /**
     * Get all countries with search
     */
    public function getCountries(Request $request)
    {
        $query = Country::query();

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $countries = $query->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }

    /**
     * Get provinces by country_id with search
     */
    public function getProvinces(Request $request)
    {
        $query = Province::query();

        // Filter by country_id
        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $provinces = $query->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => $provinces
        ]);
    }

    /**
     * Get cities by province_id with search
     */
    public function getCities(Request $request)
    {
        $query = City::query();

        // Filter by province_id
        if ($request->has('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $cities = $query->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    /**
     * Get districts by city_id with search
     */
    public function getDistricts(Request $request)
    {
        $query = District::query();

        // Filter by city_id
        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $districts = $query->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => $districts
        ]);
    }

    /**
     * Get subdistricts by district_id with search
     */
    public function getSubdistricts(Request $request)
    {
        $query = Subdistrict::query();

        // Filter by district_id
        if ($request->has('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $subdistricts = $query->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => $subdistricts
        ]);
    }
}