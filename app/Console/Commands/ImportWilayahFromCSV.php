<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use App\Models\PostalCode;

class ImportWilayahFromCSV extends Command
{
    protected $signature = 'import:wilayah-csv';
    protected $description = 'Import or update wilayah Indonesia from CSV files';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Starting import process...");

        $countryName = 'Indonesia';
        $country = Country::firstOrCreate(['name' => $countryName]);

        $this->info('Inserting Provinces...');
        $this->importProvinces($country);

        $this->info("Import completed successfully!");
    }

    private function importProvinces($country)
    {
        $provinceFilePath = storage_path('wilayah/province.csv');
        $provinceData = $this->readCSV($provinceFilePath);
        
        $number = 0;
        foreach ($provinceData as $provinceRow) {
            $province = Province::updateOrCreate(
                ['name' => $provinceRow['prov_name']],
                ['country_id' => $country->id],
                ['is_default' => $number == 0 ? true : false]
            );

            $this->info("Inserting Cities for Province: {$province->name}");
            $this->importCities($province, $provinceRow['prov_id']);

            $number++;
        }
    }

    private function importCities($province, $provinceId)
    {
        $cityFilePath = storage_path('wilayah/city.csv');
        $cityData = $this->readCSV($cityFilePath);

        $number = 0;
        foreach ($cityData as $cityRow) {
            if ($cityRow['prov_id'] === $provinceId) {
                $city = City::updateOrCreate(
                    ['name' => $cityRow['city_name']],
                    ['province_id' => $province->id],
                    ['is_default' => $number == 0 ? true : false]
                );
                
                $this->info("Inserting Districts for City: {$city->name}");
                $this->importDistricts($city, $cityRow['city_id']);

                $number++;
            }
        }
    }

    private function importDistricts($city, $cityId)
    {
        $districtFilePath = storage_path('wilayah/district.csv');
        $districtData = $this->readCSV($districtFilePath);

        $number = 0;
        foreach ($districtData as $districtRow) {
            if ($districtRow['city_id'] === $cityId) {
                $district = District::updateOrCreate(
                    ['name' => $districtRow['dis_name']],
                    ['city_id' => $city->id],
                    ['is_default' => $number == 0 ? true : false]
                );

                $this->info("Inserting Subdistricts for District: {$district->name}");
                // $this->importPortalCode($district, $districtRow['dis_id']); 

                $number++;
            }
        }
    }

    // private function importSubdistricts($district, $districtId)
    // {
    //     $subdistrictFilePath = storage_path('wilayah/subdistrict.csv');
    //     $subdistrictData = $this->readCSV($subdistrictFilePath);

    //     foreach ($subdistrictData as $subdistrictRow) {
    //         if ($subdistrictRow['dis_id'] === $districtId) {
    //             $subdistrict = Subdistrict::updateOrCreate(
    //                 ['name' => $subdistrictRow['subdis_name']],
    //                 ['district_id' => $district->id]
    //             );

    //             $this->info("Inserting Subdistricts for Sub District: {$subdistrict->name}");
    //             $this->importPortalCode($subdistrict, $subdistrictRow['subdis_id']);
    //         }
    //     }
    // }


    private function importPortalCode($district, $districtId)
    {
        $portalFilePath = storage_path('wilayah/postal_code.csv');
        $portalData = $this->readCSV($portalFilePath);
        
        foreach ($portalData as $portalRow) {
            if ($portalRow['dis_id'] == $districtId) {
                if ($district) { // Pastikan subdistrict ditemukan berdasarkan subdistrictId
                    $postalCode = PostalCode::where('postal_code', $portalRow['postal_code'])
                        ->where('district_id', $district->id)
                        ->first();
                
                    if (!$postalCode) {
                
                        // Buat data baru jika tidak ditemukan
                        PostalCode::create([
                            'postal_code' => $portalRow['postal_code'],
                            'district_id' => $district->id,
                        ]);
                        $this->info("Inserted Postal Code for : {$portalRow['postal_code']}");
                    }
                    //  else {
                    // }
                } else {
                    $this->warn("Subdistrict not found for ID: {$portalRow['subdis_id']}");
                }
            }
        }
    }

    private function readCSV($filePath)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->error("File not found or unreadable: $filePath");
            return [];
        }

        $header = null;
        $data = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }
}