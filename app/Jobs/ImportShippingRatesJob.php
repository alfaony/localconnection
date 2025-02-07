<?php

namespace App\Jobs;

use App\Models\ImportProgress;
use App\Models\ShippingRate;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use App\Models\PostalCode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportShippingRatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chunk;
    protected $batchId;
    protected $providerId;
    protected $serviceTypeId;
    protected $startingRow;

    public function __construct(array $chunk, string $batchId, $providerId, $serviceTypeId, $startingRow)
    {
        $this->chunk = $chunk;
        $this->batchId = $batchId;
        $this->providerId = $providerId;
        $this->serviceTypeId = $serviceTypeId;
        $this->startingRow = $startingRow;
    }

    public function handle()
    {
        // $count = 1;
        foreach ($this->chunk as $index => $row) {
            try {
                $count = $this->startingRow + $index + 1; // Hitung row berdasarkan chunk

                $originId = $this->findPostalCodeId($row['Kode Pos Asal'], $row['Kecamatan Asal'], $row['Kabupaten/Kota Asal'], $row['Provinsi Asal']);
                $destinationId = $this->findPostalCodeId($row['Kode Pos Tujuan'], $row['Kecamatan Tujuan'], $row['Kabupaten/Kota Tujuan'], $row['Provinsi Tujuan']);
                
                // Row
                // $count = $count + 1;

                if ($originId && $destinationId) 
                {
                    $check = ShippingRate::where([
                        'provider_id' => $this->providerId,
                        'service_type_id' => $this->serviceTypeId,
                        'origin_id' => $originId,
                        'destination_id' => $destinationId,
                    ])->first();

                    if ($check) 
                    {
                        // $progress = ImportProgress::where('batch_id', $this->batchId)->first();
                        // if ($progress) 
                        // {
                        //     // Ambil error yang sudah ada
                        //     $currentErrors = json_decode($progress->errors, true) ?? [];

                        //     // Tambahkan error baru
                        //     $currentErrors[] = 
                        //     [
                        //         'row' => $count, // Nomor baris
                        //         "error" => "Duplikasi District " . $row['Kecamatan Tujuan'] . ", City: " . $row['Kabupaten/Kota Tujuan']. ", Province: " . $row['Provinsi Tujuan'] . " Save District: ".$check->destination->subdistrict->district->name . ", City: " . $check->destination->subdistrict->district->city->name . ", Province: " . $check->destination->subdistrict->district->city->province->name,
                        //     ];

                        //     // Simpan kembali ke kolom `errors`
                        //     $progress->errors = json_encode($currentErrors);
                        //     $progress->save();
                        // }

                        $check->update([
                            'base_weight' => $row['Berat Dasar'],
                            'base_price' => $row['Harga Berat Dasar'],
                            'additional_weight' => $row['Berat Selanjutnya'],
                            'additional_price' => $row['Harga Berat Selanjutnya'],
                            'rate_per_cbm' => !empty($row['Harga Per Volume']) ? $row['Harga Per Volume'] : null,
                            'delivery_time' => !empty($row['Waktu Pengiriman']) ? $row['Waktu Pengiriman'] : null,
                        ]);
                    } else {
                        ImportProgress::where('batch_id', $this->batchId)->increment('total_import');

                        ShippingRate::create([
                            'provider_id' => $this->providerId,
                            'service_type_id' => $this->serviceTypeId,
                            'origin_id' => $originId,
                            'destination_id' => $destinationId,
                            'base_weight' => $row['Berat Dasar'],
                            'base_price' => $row['Harga Berat Dasar'],
                            'additional_weight' => $row['Berat Selanjutnya'],
                            'additional_price' => $row['Harga Berat Selanjutnya'],
                            'rate_per_cbm' => !empty($row['Harga Per Volume']) ? $row['Harga Per Volume'] : null,
                            'delivery_time' => !empty($row['Waktu Pengiriman']) ? $row['Waktu Pengiriman'] : null,
                        ]);
                    }
                } else {
                    $progress = ImportProgress::where('batch_id', $this->batchId)->first();
                    if ($progress) {
                        // Ambil error yang sudah ada
                        $currentErrors = json_decode($progress->errors, true) ?? [];

                        // Tambahkan error baru
                        if(!$originId || !$destinationId)
                        {        
                            if(!$originId)
                            {
                                $currentErrors[] = [
                                    'row' => $count,
                                    'error' => 'Origin tidak ditemukan Data District: '.$row['Kecamatan Asal'] . ", City: " . $row['Kabupaten/Kota Asal']. ", Province: " . $row['Provinsi Asal'],
                                ];
                            }
        
                            if(!$destinationId){
                                $currentErrors[] = 
                                [
                                    'row' => $count,
                                    'error' => 'Destination tidak ditemukan Data District: '.$row['Kecamatan Tujuan'] . ", City: " . $row['Kabupaten/Kota Tujuan']. ", Province: " . $row['Provinsi Tujuan'],
                                ];
                            }
                        }

                        // Simpan kembali ke kolom `errors`
                        $progress->errors = json_encode($currentErrors);
                        $progress->save();
                    }
                }
                ImportProgress::where('batch_id', $this->batchId)->increment('processed');
            } catch (\Throwable $e) {
                // dd($e);
                $progress = ImportProgress::where('batch_id', $this->batchId)->first();
                if ($progress) 
                {
                    // Ambil error yang sudah ada
                    $currentErrors = json_decode($progress->errors, true) ?? [];

                    // Tambahkan error baru
                    $currentErrors[] = [
                        'row' => $count, // Nomor baris
                        'error' => 'Erro Import Shipping Rate ' . $e->getMessage(),
                    ];

                    // Simpan kembali ke kolom `errors`
                    $progress->errors = json_encode($currentErrors);
                    $progress->save();
                }

                Log::error($e);
                throw $e;
            }
        }
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
            // dd($th);
            Log::error($th);
            throw $th;
        }
    }

    private function proviceCityDistrict($province, $city, $district)
    {
        $checkDistrict = District::where('name', 'LIKE', "%{$district}%")->first();        
        // city && District
        if($checkDistrict)
        {
            if ($province && $city && $district) 
            {
                $postal = District::whereRaw('UPPER(name) LIKE ?', ["%{$district}%"])
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
            
            if ($province && $city && $district) 
            {
                $postal = District::where('name', 'LIKE', "%{$district}%")
                ->whereHas('city', function ($q) use ($city) {
                    $q->whereRaw('UPPER(name) LIKE ?', ["%$city%"]);
                })
                ->first();
                    if 
                    (
                        $postal && $postal->defaultSubdistrict
                    ) 
                    {
                    return $postal->defaultSubdistrict->defaultPostalCode->id ?? null;
                }
            }
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
        
        return null;
    }

}