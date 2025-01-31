<?php

namespace App\Http\Controllers;

use App\Models\ShippingRate;
use App\Models\Provider;
use App\Models\ServiceType;
use App\Models\District;
use App\Models\City;
use App\Models\Province;
use App\Models\Subdistrict;
use App\Models\ImportProgress;

use App\Jobs\ImportShippingRatesJob;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

use App\Helpers\Access;
use Validator;


class ShippingRateController extends Controller
{
    public function index(Request $request)
    {
        $providers = Provider::all();
        $serviceTypes = ServiceType::all();
        $districts = District::all();

        $search = $request->input('search');


        $shippingRates = ShippingRate::with([
            'provider',
            'serviceType',
            'origin.subdistrict.district.city.province',
            'destination.subdistrict.district.city.province'
        ])
        ->when($search, function ($query) use ($search) {
            $query->whereHas('provider', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })
            ->orWhereHas('serviceType', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })
            ->orWhereHas('origin.subdistrict.district.city', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })
            ->orWhereHas('origin.subdistrict.district.city.province', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })
            ->orWhereHas('destination.subdistrict.district.city', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })
            ->orWhereHas('destination.subdistrict.district.city.province', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })
            ->orWhere('base_price', 'LIKE', "%$search%");
        })
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        // dd($this->findPostalCodeId("","TELAGA","GORONTALO","GORONTALO"));
        return view('shipping_rate.index', compact('providers', 'serviceTypes', 'districts','shippingRates'));
    }

    public function create()
    {

        return view('shipping_rate.createOrEdit', compact('providers', 'serviceTypes', 'districts'));
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        try {
            $shippingRate = ShippingRate::create($request->all());

            return response()->json(['message' => 'Tarif pengiriman berhasil ditambahkan!', 'data' => $shippingRate], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan data.'], 500);
        }
    }

    private function validateRequest(Request $request, $id = null)
    {
        $uniqueOriginDestination = $id
            ? "unique:shipping_rates,origin_id,{$id},id,destination_id,{$request->destination_id}"
            : 'unique:shipping_rates,origin_id,NULL,id,destination_id,' . $request->destination_id;

        $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'service_type_id' => 'required|exists:service_types,id',
            'origin_id' => 'required|exists:postal_codes,id',
            'destination_id' => ['required', 'exists:postal_codes,id', $uniqueOriginDestination],
            'base_weight' => 'required|numeric|min:1',
            'base_price' => 'required|numeric|min:1',
            'additional_weight' => 'nullable|numeric|min:1',
            'additional_price' => 'nullable|numeric|min:1',
            'rate_per_cbm' => 'nullable|numeric|min:1',
            'delivery_time' => 'nullable|string|max:255',
        ]);
    }

    public function edit(ShippingRate $shippingRate)
    {
        $providers = Provider::all();
        $serviceTypes = ServiceType::all();
        $districts = District::all();

        return view('shipping_rate.edit', compact('shippingRate', 'providers', 'serviceTypes', 'districts'));
    }

    public function update(Request $request, ShippingRate $shippingRate)
    {
        $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'service_type_id' => 'required|exists:service_types,id',
            'origin_id' => 'required|exists:postal_codes,id',
            'destination_id' => 'required|exists:postal_codes,id|different:origin_id',
            'base_weight' => 'required|numeric|min:0',
            'base_price' => 'required|numeric|min:0',
            'additional_weight' => 'nullable|numeric|min:0',
            'additional_price' => 'nullable|numeric|min:0',
            'rate_per_cbm' => 'nullable|numeric|min:0',
            'delivery_time' => 'nullable|string|max:255',
        ]);

        $shippingRate->update($request->all());

        return redirect()->route('shipping-rate.index')->with('update', true);
    }

    public function destroy(ShippingRate $shippingRate)
    {
        $shippingRate->delete();

        return redirect()->route('shipping-rate.index')->with('success', 'Data berhasil dihapus.');
    }

    public function dataTableJson()
    {
        // Fetch data with relationships
        $query = ShippingRate::with(['provider', 'serviceType', 'origin.subdistrict.district.city.province','destination.subdistrict.district.city.province'])->orderBy('created_at', 'desc');;

        // Map column indexes to column names
        $columnNames = ['base_price'];

        // Define searchable columns
        $searchable = [
            'provider.name',
            'serviceType.name',
            'origin.postal_code',
            'destination.postal_code',
            // 'origin.na',
            // 'destination.subdistrict.name',
            // 'origin.subdistrict.district.name',
            // 'destination.subdistrict.district.name',
            // 'origin.subdistrict.district.city.name',
            // 'destination.subdistrict.district.city.name',
            // 'origin.subdistrict.district.city.province.name',
            // 'destination.subdistrict.district.city.province.name',
        ];

        // Define bootstrap version (4 or 5)
        $bootstrap = 4;

        // Add action buttons to each row
        $actionButtons = [
            [
                'name' => 'Edit',
                'route' => 'shipping-rate.edit',
                'id' => true,
            ],
            [
                'name' => 'Delete',
                'route' => 'shipping-rate.destroy',
                'id' => true,
            ],
        ];

        $actionButtons = [];

        if(Access::can('edit','shipping_rates'))
        {
            $edit = [
                'name' => 'Edit',
                'route' => 'shipping-rate.edit',
                'id' => true,
            ];

            array_push($actionButtons,$edit);
        }

        if(Access::can('destroy','shipping_rates'))
        {
            $destroy = [
                'name' => 'Delete',
                'route' => 'shipping-rate.destroy',
                'id' => true,
            ];

            array_push($actionButtons,$destroy);
        }
        // Create a DataTable response
        $dataTable = datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons, $searchable, $bootstrap);


        // Format additional fields
        $data = $dataTable->getData();
        foreach ($data->data as $item) {
            $item->base_price = 'Rp ' . number_format($item->base_price, 0, ',', '.');
            $item->additional_price = $item->additional_price
                ? 'Rp ' . number_format($item->additional_price, 0, ',', '.')
                : '-';
            $item->origin = $item->origin->postal_code . ' - ' . $item->origin->subdistrict->name . ' - ' . $item->origin->subdistrict->district->name . ' - ' . $item->origin->subdistrict->district->city->name . ' - ' . $item->origin->subdistrict->district->city->province->name;
            $item->destination = $item->destination->postal_code . ' - ' . $item->destination->subdistrict->name . ' - ' . $item->destination->subdistrict->district->name . ' - ' . $item->destination->subdistrict->district->city->name . ' - ' . $item->destination->subdistrict->district->city->province->name;
        }

        return response()->json($data);
    }

    public function checkDuplicate(Request $request, $id = null)
    {
        $validated = $request->validate([
            'origin_id' => 'required|exists:postal_codes,id',
            'destination_id' => 'required|exists:postal_codes,id',
            'provider_id' => 'required|exists:providers,id',
        ]);

        // Query untuk memeriksa kombinasi
        $exists = ShippingRate::where('origin_id', $validated['origin_id'])
            ->where('destination_id', $validated['destination_id'])
            ->where('provider_id', $validated['provider_id']);

        // Kecualikan jika sedang mengedit data
        if ($id) {
            $exists->where('id', '!=', $id);
        }

        return response()->json(['exists' => $exists->exists()]);
    }

    public function validateCsv(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        // Baca file CSV
        $file = $request->file('import_file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));

        $headers = array_map('trim', $rows[0]); // Ambil header
        unset($rows[0]); // Hapus header dari data

        // Validasi header
        $requiredHeaders = [
            'Provinsi Asal', 'Kabupaten/Kota Asal', 'Kecamatan Asal','Kode Pos Asal', 
            'Provinsi Tujuan', 'Kabupaten/Kota Tujuan','Kecamatan Tujuan','Kode Pos Tujuan',
            'Berat Dasar', 'Harga Berat Dasar', 'Berat Selanjutnya', 
            'Harga Berat Selanjutnya', 'Harga Per Volume', 'Waktu Pengiriman'
        ];
        
        $missingHeaders = array_diff($requiredHeaders, $headers);
        if ($missingHeaders) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format CSV tidak sesuai. Kolom "' . implode('", "', $missingHeaders) . '" tidak ditemukan.',
            ], 422);
        }

        // Validasi baris maksimal
        if (count($rows) > 10000) {
            return response()->json([
                'status' => 'error',
                'message' => 'File terlalu besar. Maksimal 1.000 baris diperbolehkan.',
            ], 422);
        }

        $errors = [];
        foreach ($rows as $line => $row) {
            $data = array_combine($headers, $row);

            // Validasi setiap baris
            $validator = Validator::make($data, 
            [
                'Provinsi Asal' => 'required|string',
                'Kabupaten/Kota Asal' => 'required|string',
                'Kecamatan Asal' => 'nullable|string',
                'Kode Pos Asal' => 'nullable|string',
                'Provinsi Tujuan' => 'required|string',
                'Kabupaten/Kota Tujuan' => 'required|string',
                'Kecamatan Tujuan' => 'nullable|string',
                'Kode Pos Tujuan' => 'nullable|string',
                'Berat Dasar' => 'required|numeric|min:1',
                'Harga Berat Dasar' => 'required|numeric|min:0',
                'Berat Selanjutnya' => 'required|numeric|min:1',
                'Harga Berat Selanjutnya' => 'required|numeric|min:1',
                'Harga Per Volume' => 'nullable|numeric|min:0',
                'Waktu Pengiriman' => 'required|string',
            ],
            [
                'required' => ':attribute harus diisi.',
                'string' => ':attribute harus berupa teks.',
                'numeric' => ':attribute harus berupa angka.',
                'min' => ':attribute minimal :min.',
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'line' => $line + 2, // Menyesuaikan dengan baris sebenarnya (header + offset)
                    'errors' => $validator->errors()->all(),
                ];
            }
        }

        // Jika ada kesalahan, kembalikan pesan kesalahan
        if (!empty($errors)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terdapat kesalahan dalam file yang diunggah.',
                'errors' => $errors,
            ], 422);
        }

        // Jika validasi berhasil
        return response()->json([
            'status' => 'success',
            'message' => 'File berhasil divalidasi.',
        ]);
    }
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:10240', // Max 10 MB
            'provider_id' => 'required|exists:providers,id',
            'service_type_id' => 'required|exists:service_types,id',
        ]);

        $file = $request->file('import_file');
        $batchId = Str::uuid();

        // Parse CSV
        $data = array_map('str_getcsv', file($file->getRealPath()));
        $headers = array_map('trim', $data[0]);
        unset($data[0]);

        $parsedData = [];
        foreach ($data as $row) {
            $parsedData[] = array_combine($headers, $row);
        }

        $chunks = array_chunk($parsedData, 100);
        // Store progress
        ImportProgress::create([
            'batch_id' => $batchId,
            'total' => count($parsedData),
            'processed' => 0,
        ]);

        foreach ($chunks as $chunk) {
            ImportShippingRatesJob::dispatch($chunk, $batchId, $request->provider_id, $request->service_type_id);
        }

        return response()->json(['message' => 'File is being processed', 'batch_id' => $batchId]);
    }

    public function progress($batchId)
    {
        $progress = ImportProgress::where('batch_id', $batchId)->firstOrFail();
        return response()->json([
            'errors' => json_decode($progress->errors, true) ?? [],
            'processed' => $progress->processed,
            'total' => $progress->total,
            'progress' => ($progress->total > 0) ? ($progress->processed / $progress->total) * 100 : 0,
        ]);
    }

    private function findPostalCodeId($postalCode, $district, $city, $province)
    {
        // Pastikan semua input dalam uppercase
        $postalCode = $postalCode ? strtoupper($postalCode) : null;
        $district = $district ? strtoupper($district) : null;
        $city = $city ? strtoupper($city) : null;
        $province = $province ? strtoupper($province) : null;

        try {
            // Jika Postal Code disediakan, cari langsung
            if ($postalCode) 
            {
                $postal = PostalCode::whereRaw('UPPER(postal_code) LIKE ?', ["%$postalCode%"])->first();
                if ($postal) {
                    return $postal->id;
                }
            }

            if($province && $city && $district)
            {
                return $this->proviceCityDistrict($province, $city, $district);
            }

            if($province && $city && !$district)
            {
                return $this->proviceCity($province, $city);
            }
        } catch (\Throwable $th) {
            // Debug jika terjadi masalah
            throw $th;
        }
    }

    private function proviceCityDistrict($province, $city, $district)
    {
        $checkDistrict = District::where('name', '=', strtoupper($district))->first();        
        // city && District
        if($checkDistrict)
        {
            if ($province && $city && $district) 
            {
                $postal = District::whereRaw('UPPER(name) = ?', [strtoupper($district)])
                    ->whereHas('city', function ($q) use ($city, $province) {
                        $q->whereRaw('UPPER(name) LIKE ?', ["%$city%"])
                        ->whereHas('province', function ($q) use ($province) {
                            $q->whereRaw('UPPER(name) LIKE ?', ["%$province%"]);
                        });
                    })
                    ->with(['defaultSubdistrict.defaultPostalCode'])
                    ->first();
                if (
                    $postal &&
                    $postal->defaultSubdistrict 
                ) {
                    return $postal->defaultSubdistrict->defaultPostalCode->id ?? null; 
                }
            }
             // if ($province && $city && $district) 
            // {
            //     $postal = District::whereRaw('UPPER(name) LIKE ?', ["%$district%"])
            //     ->whereHas('city', function ($q) use ($city) {
            //         $q->whereRaw('UPPER(name) LIKE ?', ["%$city%"]);
            //     })
            //     ->first();
            //         if 
            //         (
            //             $postal && $postal->defaultSubdistrict
            //         ) 
            //         {
            //         return $postal->defaultSubdistrict->defaultPostalCode->id ?? null;
            //     }
            // }
            // // Prioritas 1: Cari berdasarkan Province, City, dan District
            // if ($province && $city && $district) 
            // {
            //     $postal = Province::whereHas('cities', function ($q) use ($city, $district) {
            //         $q->whereRaw('UPPER(name) LIKE ?', ["%$city%"])
            //         ->whereHas('districts', function ($q) use ($district) {
            //             $q->whereRaw('UPPER(name) LIKE ?', ["%$district%"]);
            //         });
            //     })
            //     ->with('defaultCity.defaultDistrict.defaultSubdistrict.defaultPostalCode')
            //     ->whereRaw('UPPER(name) LIKE ?', ["%$province%"])
            //     ->first();
                
            //     if (
            //         $postal &&
            //         $postal->defaultCity &&
            //         $postal->defaultCity->defaultDistrict &&
            //         $postal->defaultCity->defaultDistrict->defaultSubdistrict
            //         // && $postal->defaultCity->defaultDistrict->defaultSubdistrict->defaultPostalCode
            //     ) {
            //         // dd("postal 1 : ", $postal);
            //         return $postal->defaultCity->defaultDistrict->defaultSubdistrict->defaultPostalCode->id ?? null;
            //     }
            // }
    
            // // Prioritas 2: Cari berdasarkan Province dan City
            // if ($province && $city && $district) 
            // {
            //     $postal = District::whereRaw('UPPER(name) LIKE ?', ["%$district%"])
            //         ->with(['defaultSubdistrict.defaultPostalCode'])
            //         ->first();
                    
            //         if (
            //             $postal &&
            //             $postal->defaultSubdistrict 
            //             // &&
            //             // $postal->defaultSubdistrict->defaultPostalCode
            //         ) {
            //         // dd("postal 3 : ", $postal);
            //         return $postal->defaultSubdistrict->defaultPostalCode->id;
            //     }
            // }

        }

        return null;
    }

    private function proviceCity($province, $city)
    {
        // Prioritas 1: Cari berdasarkan City saja
        if ($province && $city) 
        {
            $postal = City::whereRaw('UPPER(name) LIKE ?', ["%$city%"])
                ->whereHas('province', function ($q) use ($province) {
                    $q->whereRaw('UPPER(name) LIKE ?', ["%$province%"]);
                })
                ->with(['defaultDistrict.defaultSubdistrict.defaultPostalCode'])
                ->first();
            if (
                $postal &&
                $postal->defaultDistrict &&
                $postal->defaultDistrict->defaultSubdistrict &&
                $postal->defaultDistrict->defaultSubdistrict->defaultPostalCode
            ) {
                return $postal->defaultDistrict->defaultSubdistrict->defaultPostalCode->id;
            }
        }


        if ($province && $city) 
        {
            $postal = City::whereRaw('UPPER(name) LIKE ?', ["%$city%"])
                ->with(['defaultDistrict.defaultSubdistrict.defaultPostalCode'])
                ->first();
            if (
                $postal &&
                $postal->defaultDistrict &&
                $postal->defaultDistrict->defaultSubdistrict &&
                $postal->defaultDistrict->defaultSubdistrict->defaultPostalCode
            ) {
                return $postal->defaultDistrict->defaultSubdistrict->defaultPostalCode->id;
            }
        }

        // Prioritas 2: Cari berdasarkan Province saja
        // if ($province) {
        //     $postal = Province::whereRaw('UPPER(name) LIKE ?', ["%$province%"])
        //         ->with(['defaultCity.defaultDistrict.defaultSubdistrict.defaultPostalCode'])
        //         ->first();

        //     if (
        //         $postal &&
        //         $postal->defaultCity &&
        //         $postal->defaultCity->defaultDistrict &&
        //         $postal->defaultCity->defaultDistrict->defaultSubdistrict &&
        //         $postal->defaultCity->defaultDistrict->defaultSubdistrict->defaultPostalCode
        //     ) {
        //         return $postal->defaultCity->defaultDistrict->defaultSubdistrict->defaultPostalCode->id;
        //     }
        // }

        return null;
    }
}