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
            // if ($request->filled('weight')) 
            // {
            //     if ($request->weight <= $rate->base_weight) {
            //         // Jika berat masih dalam batas berat dasar
            //         $estimatedPrice = $rate->base_price;
            //     } else {
            //         // Hitung harga tambahan jika berat lebih dari base_weight
            //         $extraWeight = ceil(($request->weight - $rate->base_weight) / $rate->additional_weight);
            //         $estimatedPrice = $rate->base_price + ($extraWeight * $rate->additional_price);
            //     }
            // }

            // if ($request->filled('width') && $request->filled('height') && $request->filled('length')) {
            //     $volume = ($request->length * $request->width * $request->height) / $rate->factor_volumetric;
            //     $estimatedPrice = $request->volume * $rate->rate_per_cbm;
            // }
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
            $rate->estimated_price = $estimatedPrice;
        }

        return response()->json(['rates' => $rates]);
    }

    protected function calculateEstimatedPrice($baseWeight,$basePrice,$additionalWeight,$additionalPrice,$ratePerCbm,$factorVolumetric, $weight = null, $height = null, $width = null, $length = null) 
    {   
        if ($weight) 
        {
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
            $weight = ($length * $width * $height) / $factorVolumetric;
            if($ratePerCbm > 0)
            {
                $estimatedPrice = $weight * $ratePerCbm;
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

        return response()->json([
            'origin' => $origin ? $origin->subdistrict->district->city->name : null,
            'destination' => $destination ? $destination->subdistrict->district->name : null,
            'weight' => $request->weight,
            'volume' => $request->volume,
            'height' => $request->height,
            'width' => $request->width,
            'length' => $request->length
        ]);
    }
}