<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use App\Models\PostalCode;

class WilayahController extends Controller
{
    
    public function select2(Request $request)
    {
        $query = $request->input('q'); // Query dari input pencarian
        $page = $request->input('page', 1); // Halaman pagination
        $perPage = 5; // Default jumlah item per halaman

        // Pencarian pada setiap level hierarki
        $postalCodes = PostalCode::query()
        ->with(['subdistrict.district.city.province']) // Load semua relasi
        ->where(function ($queryBuilder) use ($query) {
            $queryBuilder
                ->whereHas('subdistrict.district.city', function ($qb) use ($query) {
                    $qb->where('name', 'LIKE', "%$query%");
                })
                ->orWhereHas('subdistrict.district.city.province', function ($qb) use ($query) {
                    $qb->where('name', 'LIKE', "%$query%");
                })
                ->orWhereHas('subdistrict.district', function ($qb) use ($query) {
                    $qb->where('name', 'LIKE', "%$query%");
                })
                ->orWhereHas('subdistrict', function ($qb) use ($query) {
                    $qb->where('name', 'LIKE', "%$query%");
                });
        })
        ->orWhere('postal_code', 'LIKE', "%$query%")
        ->select('postal_codes.*')
        ->addSelect(\DB::raw("
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM cities 
                    INNER JOIN districts ON districts.city_id = cities.id
                    INNER JOIN subdistricts ON subdistricts.district_id = districts.id
                    WHERE postal_codes.subdistrict_id = subdistricts.id
                    AND cities.name LIKE '%$query%'
                ) THEN 1
                WHEN EXISTS (
                    SELECT 1 FROM provinces 
                    INNER JOIN cities ON cities.province_id = provinces.id
                    INNER JOIN districts ON districts.city_id = cities.id
                    INNER JOIN subdistricts ON subdistricts.district_id = districts.id
                    WHERE postal_codes.subdistrict_id = subdistricts.id
                    AND provinces.name LIKE '%$query%'
                ) THEN 4
                WHEN EXISTS (
                    SELECT 1 FROM districts 
                    INNER JOIN subdistricts ON subdistricts.district_id = districts.id
                    WHERE postal_codes.subdistrict_id = subdistricts.id
                    AND districts.name LIKE '%$query%'
                ) THEN 3
                WHEN EXISTS (
                    SELECT 1 FROM subdistricts 
                    WHERE postal_codes.subdistrict_id = subdistricts.id
                    AND subdistricts.name LIKE '%$query%'
                ) THEN 2
                ELSE 5
            END AS priority
        "))
        ->orderBy('priority')
        ->paginate($perPage, ['*'], 'page', $page);

        // Format hasil untuk Select2
        $results = $postalCodes->getCollection()->map(function ($postalCode) {
            $subDistrict = $postalCode->subDistrict;
            $district = $subDistrict->district;
            $city = $district->city;
            $province = $city->province;

            return [
                'id' => $postalCode->id,
                'text' => sprintf(
                    '%s - %s, %s, %s, %s',
                    $province->name,
                    $city->name,
                    $district->name,
                    $subDistrict->name,
                    $postalCode->postal_code,
                ),
            ];
        });

        return response()->json([
            'items' => $results,
            'pagination' => ['more' => $postalCodes->hasMorePages()],
        ]);
    }

    
}