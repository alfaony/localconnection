<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use App\Models\PostalCode;

class SetDefaultForWilayah extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:default-wilayah-indonesia';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set default IDs for countries, provinces, cities, districts, and subdistricts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Reset default_province_id in countries
        Country::query()->update(['default_province_id' => null]);
        $this->info('All default_province_id in countries have been reset to NULL.');

        // Reset default_city_id in provinces
        Province::query()->update(['default_city_id' => null]);
        $this->info('All default_city_id in provinces have been reset to NULL.');

        // Reset default_district_id in cities
        City::query()->update(['default_district_id' => null]);
        $this->info('All default_district_id in cities have been reset to NULL.');

        // Reset default_subdistrict_id in districts
        District::query()->update(['default_subdistrict_id' => null]);
        $this->info('All default_subdistrict_id in districts have been reset to NULL.');

        // Reset default_postal_code_id in subdistricts
        Subdistrict::query()->update(['default_postal_code_id' => null]);
        $this->info('All default_postal_code_id in subdistricts have been reset to NULL.');

        $this->info('All default IDs have been successfully reset to NULL.');

        // Set default_province_id for countries
        Country::query()->chunkById(100, function ($countries) {
            foreach ($countries as $country) {
                $defaultProvince = Province::where('country_id', $country->id)->where('name', 'like', '%JAKARTA%')->orderBy('id')->first();
                if ($defaultProvince) {
                    $country->default_province_id = $defaultProvince->id;
                    $country->save();
                    $this->info("Default province set for country: {$country->name}");
                }
            }
        });

        // Set default_city_id for provinces
        Province::query()->chunkById(100, function ($provinces) {
            foreach ($provinces as $province) {
                $defaultCity = City::where('province_id', $province->id)->orderBy('id')->first();
                if ($defaultCity) {
                    $province->default_city_id = $defaultCity->id;
                    $province->save();
                    $this->info("Default city set for province: {$province->name}");
                }
            }
        });


        // Set default_district_id for cities
        City::query()->chunkById(100, function ($cities) {
            foreach ($cities as $city) {
                $defaultDistrict = District::where('city_id', $city->id)->orderBy('id')->first();
                if ($defaultDistrict) {
                    $city->default_district_id = $defaultDistrict->id;
                    $city->save();
                    $this->info("Default district set for city: {$city->name}");
                }
            }
        });

        // Set default_subdistrict_id for districts
        District::query()->chunkById(100, function ($districts) {
            foreach ($districts as $district) 
            {
                $defaultSubdistrict = Subdistrict::where('district_id', $district->id)->orderBy('id')->first();
                    if ($defaultSubdistrict) {
                        $district->default_subdistrict_id = $defaultSubdistrict->id;
                        $district->save();
                        $this->info("Default subdistrict set for district: {$district->name}");
                    }else
                    {}
            }
        });


        // Set default_subdistrict_id for subdistricts
        Subdistrict::query()->chunkById(100, function ($subdistricts) {
            foreach ($subdistricts as $subdistrict) {
                if($subdistrict->asDefaultDistrict)
                {
                    $defaultPostalCode = PostalCode::where('subdistrict_id', $subdistrict->id)->orderBy('id')->first();
                    if ($defaultPostalCode) {
                        $subdistrict->default_postal_code_id = $defaultPostalCode->id;
                        $subdistrict->save();
                        $this->info("Default postal code set for subdistrict: {$subdistrict->name}");
                    }
                }
            }
        });

        $this->info('Default IDs have been set successfully.');
    }
}
