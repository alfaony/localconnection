<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use Illuminate\Support\Facades\Storage;

class InsertOrUpdateWilayah extends Command
{
    protected $signature = 'wilayah:sync';
    protected $description = 'Insert or Update Wilayah Indonesia from JSON files';

    public function handle()
    {
        $this->info('Starting wilayah sync...');

        // Insert or Update Country
        $country = Country::updateOrCreate(
            ['name' => 'Indonesia'] // Update code if exists
        );
        $this->info('Country "Indonesia" synced.');

        // Read Province JSON
        $provincePath = storage_path('wilayah_indonesia/provinsi/provinsi.json');
        if (!file_exists($provincePath)) {
            $this->error('Province JSON file not found.');
            return;
        }

        $provinces = json_decode(file_get_contents($provincePath), true);

        foreach ($provinces as $key => $provinceData) {
            $province = Province::updateOrCreate(
                ['name' => $provinceData, 'country_id' => $country->id]
            );
            $this->info("Province synced: {$province->name}");

            // Read City JSON for each province
            $cityPath = storage_path("wilayah_indonesia/kabupaten_kota/kab-{$key}.json");
            if (file_exists($cityPath)) {
                $cities = json_decode(file_get_contents($cityPath), true);

                foreach ($cities as $cityKey => $cityData) {
                    $city = City::updateOrCreate(
                        ['name' => $cityData, 'province_id' => $province->id]
                    );
                    $this->info("  City synced: {$city->name}");

                    // Read District JSON for each city
                    $districtPath = storage_path("wilayah_indonesia/kecamatan/kec-{$key}-{$cityKey}.json");
                    if (file_exists($districtPath)) {
                        $districts = json_decode(file_get_contents($districtPath), true);

                        foreach ($districts as $districtKey => $districtData) {
                            $district = District::updateOrCreate(
                                ['name' => $districtData, 'city_id' => $city->id]
                            );
                            $this->info("    District synced: {$district->name}");
                        }
                    }
                }
            }
        }

        $this->info('Wilayah Indonesia sync complete!');
    }
}