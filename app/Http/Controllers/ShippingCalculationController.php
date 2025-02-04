<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShippingRate;
use App\Models\PostalCode;

class ShippingCalculationController extends Controller
{
    public function index()
    {
        return view('shipping_calculation.index');
    }

    public function searchRates(Request $request)
    {
        // Validasi Input
        $request->validate([
            'origin_id' => 'required|exists:postal_codes,id',
            'destination_id' => 'required|exists:postal_codes,id',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
        ]);

        $query = ShippingRate::where('origin_id', $request->origin_id)
            ->where('destination_id', $request->destination_id);

        // Filter hanya yang memiliki base_weight ≤ weight
        if ($request->filled('weight')) {
            $query->where('base_weight', '<=', $request->weight)
                ->orderBy('base_weight', 'asc');
        }

        if ($request->filled('volume')) {
            $query->orderBy('rate_per_cbm', 'asc');
        }
        $rates = $query->with(['provider', 'serviceType'])->get();
        
        // Hitung Estimasi Harga
        foreach ($rates as $rate) {
            $estimatedPrice = 0;
            $estimatedPrice = $this->calculateEstimatedPrice(
                $rate->base_weight, 
                $rate->base_price, 
                $rate->additional_weight, 
                $rate->additional_price, 
                $rate->rate_per_cbm, 
                $rate->factor_volumetric, 
                $request->weight, 
                $request->height, 
                $request->width, 
                $request->length
            );

            // Tambahkan estimasi harga ke dalam response
            $rate->height = $request->height;
            $rate->width = $request->width;
            $rate->length = $request->length;
            $rate->weight = $request->weight;
            $rate->estimated_price = $estimatedPrice;
        }
        $rates = $rates->sortBy('estimated_price');
        
        return response()->json(['rates' => $rates]);
    }

    protected function calculateEstimatedPrice($baseWeight,$basePrice,$additionalWeight,$additionalPrice,$ratePerCbm,$factorVolumetric, $weight = null, $height = null, $width = null, $length = null) 
    {   
        if ($weight) 
        {
            $weight = (int) $weight;

            if ($weight <= $baseWeight) {
                // Jika berat masih dalam batas berat dasar
                $estimatedPrice = $basePrice;
            } else {
                // Hitung harga tambahan jika berat lebih dari base_weight
                $extraWeight = ceil(($weight - $baseWeight) / $additionalWeight);
                $estimatedPrice = $basePrice + ($extraWeight * $additionalPrice);
            }
        }
        
        if($height && $width && $length)
        {
            $length = (int) $length;
            $height = (int) $height;
            $width = (int) $width;

            $weight = ($length * $width * $height) / $factorVolumetric;
            $weight = (float) $weight;
            if($ratePerCbm > 0)
            {
                $estimatedPrice = $weight * $ratePerCbm;
                $estimatedPrice = (int) $estimatedPrice;
            }else
            {
                $weight = ceil($weight);
                if ($weight <= $baseWeight) {
                    // Jika berat masih dalam batas berat dasar
                    $estimatedPrice = $basePrice;
                } else {
                    // Hitung harga tambahan jika berat lebih dari base_weight
                    $extraWeight = ceil(($weight - $baseWeight) / $additionalWeight);
                    $estimatedPrice = $basePrice + ($extraWeight * $additionalPrice);
                }   
            }
        }

        return $estimatedPrice ?? 0;
    }

    public function select2Origin(Request $request)
    {
        $search = $request->q;
        
        $origins = PostalCode::whereHas('shippingRatesAsOrigin', function ($query) {
            $query->whereNotNull('id'); // Pastikan shippingRatesAsOrigin ada
        })
        ->where(function ($query) use ($search) {
            $query->where('postal_code', 'LIKE', "%{$search}%")
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
        ->limit(10)
        ->get();


        return response()->json([
            'results' => $origins->map(fn($origin) => [
                'id' => $origin->id,
                'text' => "{$origin->full_name}"
            ])
        ]);
    }

    public function select2Destination(Request $request)
    {
        $search = $request->q;

        $destinations = PostalCode::whereHas('shippingRatesAsDestination', function ($query) {
            $query->whereNotNull('id'); // Memastikan ada shippingRatesAsDestination
        })
        ->where(function ($query) use ($search) {
            $query->where('postal_code', 'LIKE', "%{$search}%")
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
        ->limit(10)
        ->get();

        return response()->json([
            'results' => $destinations->map(fn($destination) => [
                'id' => $destination->id,
                'text' => "{$destination->full_name}"
            ])
        ]);
    }

    public function detail(Request $request)
    {
        $origin = PostalCode::with(['subdistrict.district.city.province'])
            ->where('id', $request->origin_id)
            ->first();

        $destination = PostalCode::with(['subdistrict.district.city.province'])
            ->where('id', $request->destination_id)
            ->first();
        
        $rate = ShippingRate::find($request->rate_id);
        $estimatedPrice = $this->calculateEstimatedPrice(
                $rate->base_weight, 
                $rate->base_price, 
                $rate->additional_weight, 
                $rate->additional_price, 
                $rate->rate_per_cbm, 
                $rate->factor_volumetric, 
                $request->weight, 
                $request->height, 
                $request->width, 
                $request->length
            );
        if($request->filled('weight'))
        {
            $weight = ceil($request->weight);
        }else
        {
            $weight = $request->length * $request->width * $request->height / $rate->factor_volumetric;
        }

        return response()->json([
            'origin' => $origin ? $origin->subdistrict->district->city->name : null,
            'destination' => $destination ? $destination->subdistrict->district->name : null,
            'weight' => $weight,
            'height' => $request->height,
            'width' => $request->width,
            'length' => $request->length,
            'estimatedPrice' => $estimatedPrice
        ]);
    }
}