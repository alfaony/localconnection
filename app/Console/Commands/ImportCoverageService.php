<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use App\Models\OpticalDistribution;
use App\Models\CoverageService;
use App\Models\CoverageServiceDistribution;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportCoverageService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coverage-service:import 
                            {file : Nama file CSV di public/coverage_service/} 
                            {slug : Slug perusahaan}
                            {type? : create atau update (default: create)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data coverage service dari file CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('file');
        $companySlug = $this->argument('slug');
        $type = $this->argument('type') ?? "create";

        $filePath = public_path('coverage_service/' . $filename);
        
        // Validasi file exists
        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return;
        }
        
        // Validasi company exists
        $company = Company::where('slug', $companySlug)->first();
        if (!$company) {
            $this->error("Perusahaan dengan slug '{$companySlug}' tidak ditemukan");
            return;
        }
        
        $this->info("Memproses file: {$filename}");
        $this->info("Perusahaan: {$company->name}");
        
        try {
            // Load CSV
            $rows = $this->readCSV($filePath);
            
            $successCount = 0;
            $failures = [];
            
            $rowNumber = 1;
            foreach ($rows as $row) {
                $rowNumber = $rowNumber + 2; // +2 karena header di row 1 dan array dimulai dari 0
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Map row data
                $data = [
                    'province' => trim($row["Provinsi"] ?? ''),
                    'city' => trim($row["Kota/Kabupaten"] ?? ''),
                    'district' => trim($row["Kecamatan"] ?? ''),
                    'subdistrict' => trim($row["Kelurahan"] ?? ''),
                    'odp' => trim($row["ODP"] ?? ''),
                ];

                // Validate required fields
                if (empty($data['province']) || empty($data['city']) || 
                    empty($data['district']) || empty($data['subdistrict']) || 
                    empty($data['odp'])) {
                    $failures[] = [
                        'row' => $rowNumber,
                        'data' => implode(' - ', array_filter($data)),
                        'reason' => 'Data tidak lengkap'
                    ];
                    continue;
                }
                
                // Process the row
                if ($type == "create") {
                    $result = $this->processRow($data, $company, $rowNumber);
                } else if ($type == "update") {
                    $result = $this->processRowUpdate($data, $company, $rowNumber);
                } else if ($type == "check") {
                    dd($data);
                }
                
                if ($result['success']) {
                    $successCount++;
                    $this->info("Row {$rowNumber} berhasil diproses: {$data['subdistrict']}");
                } else {
                    $failures[] = [
                        'row' => $rowNumber,
                        'data' => implode(' - ', array_filter($data)),
                        'reason' => $result['reason']
                    ];
                    $this->error("Row {$rowNumber} gagal: {$result['reason']}");
                }
            }
            
            // Display results
            $this->info("\nHasil Import:");
            $this->info("✅ Berhasil: {$successCount}");
            $this->info("❌ Gagal: " . count($failures));
            
            if (!empty($failures)) {
                $this->info("\nDetail yang gagal:");
                $this->table(['Row', 'Data', 'Alasan'], $failures);
            }
            
        } catch (\Exception $e) {
            $this->error("Terjadi kesalahan: " . $e->getMessage());
            Log::error('Import Coverage Service Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Process a single row from the CSV file (Create)
     */
    private function processRow($data, $company, $rowNumber)
    {
        DB::beginTransaction();
        
        try {
            // Cari provinsi
            $province = Province::where('name', 'like', '%' . $data['province'] . '%')->first();
            if (!$province) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Provinsi tidak ditemukan: ' . $data['province']
                ];
            }
            
            // Cari kota
            $city = City::where('province_id', $province->id)
                        ->where('name', $data['city'])
                        ->first();
            if (!$city) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Kota tidak ditemukan: ' . $data['city']
                ];
            }
            
            // Cari kecamatan
            $district = District::where('city_id', $city->id)
                               ->where('name', $data['district'])
                               ->first();
            if (!$district) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Kecamatan tidak ditemukan: ' . $data['district']
                ];
            }
            
            // Cari kelurahan
            $subdistrict = Subdistrict::where('district_id', $district->id)
                                     ->where('name', $data['subdistrict'])
                                     ->first();
            if (!$subdistrict) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Kelurahan tidak ditemukan: ' . $data['subdistrict']
                ];
            }
            
            // Cari ODP - bisa multiple, dipisahkan dengan koma atau semicolon
            $odpNames = preg_split('/[,;]+/', $data['odp']);
            $odpIds = [];
            
            foreach ($odpNames as $odpName) {
                $odpName = trim($odpName);
                if (empty($odpName)) continue;
                
                $odp = OpticalDistribution::where('company_id', $company->id)
                                         ->where('name', 'like', '%' . $odpName . '%')
                                         ->first();
                
                if (!$odp) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'reason' => 'ODP tidak ditemukan: ' . $odpName
                    ];
                }
                
                $odpIds[] = $odp->id;
            }
            
            if (empty($odpIds)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Tidak ada ODP yang valid'
                ];
            }
            
            // Cek apakah coverage service sudah ada untuk lokasi ini
            $existingCoverage = CoverageService::where('company_id', $company->id)
                                              ->where('province_id', $province->id)
                                              ->where('city_id', $city->id)
                                              ->where('district_id', $district->id)
                                              ->where('subdistrict_id', $subdistrict->id)
                                              ->first();
            
            if ($existingCoverage) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Coverage service sudah ada untuk lokasi ini'
                ];
            }
            
            // Buat coverage service
            $coverageService = CoverageService::create([
                'company_id' => $company->id,
                'province_id' => $province->id,
                'city_id' => $city->id,
                'district_id' => $district->id,
                'subdistrict_id' => $subdistrict->id,
            ]);
            
            // Tambahkan ODP
            foreach ($odpIds as $odpId) {
                CoverageServiceDistribution::create([
                    'coverage_service_id' => $coverageService->id,
                    'optical_distribution_id' => $odpId
                ]);
            }
            
            DB::commit();
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Process Row Error: ' . $e->getMessage());
            return [
                'success' => false,
                'reason' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process a single row from the CSV file (Update)
     */
    private function processRowUpdate($data, $company, $rowNumber)
    {
        DB::beginTransaction();
        
        try {
            // Cari provinsi
            $province = Province::where('name', $data['province'])->first();
            if (!$province) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Provinsi tidak ditemukan: ' . $data['province']
                ];
            }
            
            // Cari kota
            $city = City::where('province_id', $province->id)
                        ->where('name', $data['city'])
                        ->first();
            if (!$city) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Kota tidak ditemukan: ' . $data['city']
                ];
            }
            
            // Cari kecamatan
            $district = District::where('city_id', $city->id)
                               ->where('name', $data['district'])
                               ->first();
            if (!$district) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Kecamatan tidak ditemukan: ' . $data['district']
                ];
            }
            
            // Cari kelurahan
            $subdistrict = Subdistrict::where('district_id', $district->id)
                                     ->where('name', $data['subdistrict'])
                                     ->first();
            if (!$subdistrict) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Kelurahan tidak ditemukan: ' . $data['subdistrict']
                ];
            }
            
            // Cari coverage service yang sudah ada
            $coverageService = CoverageService::where('company_id', $company->id)
                                             ->where('province_id', $province->id)
                                             ->where('city_id', $city->id)
                                             ->where('district_id', $district->id)
                                             ->where('subdistrict_id', $subdistrict->id)
                                             ->first();
            
            if (!$coverageService) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Coverage service tidak ditemukan untuk lokasi ini'
                ];
            }
            
            // Cari ODP - bisa multiple, dipisahkan dengan koma atau semicolon
            $odpNames = preg_split('/[,;]+/', $data['odp']);
            $odpIds = [];
            
            foreach ($odpNames as $odpName) {
                $odpName = trim($odpName);
                if (empty($odpName)) continue;
                
                $odp = OpticalDistribution::where('company_id', $company->id)
                                         ->where('name', 'like', '%' . $odpName . '%')
                                         ->first();
                
                if (!$odp) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'reason' => 'ODP tidak ditemukan: ' . $odpName
                    ];
                }
                
                $odpIds[] = $odp->id;
            }
            
            if (empty($odpIds)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Tidak ada ODP yang valid'
                ];
            }
            
            // Hapus ODP yang lama
            CoverageServiceDistribution::where('coverage_service_id', $coverageService->id)->delete();
            
            // Tambahkan ODP yang baru
            foreach ($odpIds as $odpId) {
                CoverageServiceDistribution::create([
                    'coverage_service_id' => $coverageService->id,
                    'optical_distribution_id' => $odpId
                ]);
            }
            
            DB::commit();
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Process Row Update Error: ' . $e->getMessage());
            return [
                'success' => false,
                'reason' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Read CSV file
     */
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