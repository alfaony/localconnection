<?php

namespace App\Jobs;

use App\Models\ProductStore;
use App\Models\ImportProgress;
use App\Models\CategoryProductStore;
use App\Models\BrandProductStore;
use App\Models\Rack;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportProductStoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batchId;
    protected $csvData;
    protected $userId;
    protected $companyId;
    protected $accessibleCompanyIds;

    public function __construct($csvData, $userId, $companyId, $batchId, $accessibleCompanyIds)
    {
        $this->csvData = $csvData;
        $this->userId = $userId;
        $this->companyId = $companyId;
        $this->batchId = $batchId;
        $this->accessibleCompanyIds = $accessibleCompanyIds;
    }

    public function handle()
    {
        $total = count($this->csvData) - 1; // Exclude header
        $processed = 0;
        $totalImport = 0; // success count
        $errors = [];

        // Set initial progress
        $this->updateProgress($processed, $total, $totalImport, $errors, 'processing');

        foreach ($this->csvData as $index => $row) {
            try {
                // Skip header row
                if ($index === 0) {
                    continue;
                }

                // Validate required fields
                if (empty($row[2])) {
                    throw new \Exception("Nama produk wajib diisi");
                }

                // ============================================
                // MAPPING KOLOM CSV
                // ============================================
                $brandName = $row[0] ?? null;
                $categoryName = $row[1] ?? null;
                $name = $row[2] ?? null;
                $variant = $row[3] ?? null;
                $specification = $row[4] ?? null;
                $length = is_numeric($row[5]) ? $row[5] : null;
                $width = is_numeric($row[6]) ? $row[6] : null;
                $height = is_numeric($row[7]) ? $row[7] : null;
                $weight = is_numeric($row[8]) ? $row[8] : null;
                $sellingPrice = $row[9] ?? 0;
                $rackName = $row[10] ?? null;

                // ============================================


                // ============================================
                // Validate required fields
                // ============================================
                
                if (empty($brandName)) {
                    throw new \Exception("Brand wajib diisi");
                }

                if (empty($categoryName)) {
                    throw new \Exception("Kategori wajib diisi");
                }

                if (empty($name)) {
                    throw new \Exception("Nama produk wajib diisi");
                }

                // Validasi Selling Price: wajib isi dan harus numerik tanpa format simbol/koma
                if (empty($sellingPrice)) {
                    throw new \Exception("Harga jual wajib diisi");
                }

                // Cek jika mengandung koma, titik, atau huruf (misalnya 'Rp' atau '50,000')
                if (!is_numeric(str_replace([',', '.', 'Rp', ' '], '', $sellingPrice))) {
                    throw new \Exception("Harga jual harus berupa angka tanpa format simbol atau pemisah");
                }

                // ============================================

                // Normalize price (hilangkan karakter non angka)
                $cleanSellingPrice = (int) preg_replace('/[^0-9]/', '', $sellingPrice);

                // Cek duplikasi nama produk dengan case-insensitive
                $existingProduct = ProductStore::where('company_id', $this->companyId)
                    ->whereRaw('UPPER(name) = ?', [Str::upper($name)])
                    ->first();

                if ($existingProduct) {
                    throw new \Exception("Nama produk '$name' sudah ada di database");
                }

                // Find or create category dengan uppercase
                $category = null;
                if (!empty($categoryName)) {
                    $categoryNameUpper = Str::upper($categoryName);
                    
                    $category = CategoryProductStore::whereIn('company_id', $this->accessibleCompanyIds)
                        ->whereRaw('UPPER(name) = ?', [$categoryNameUpper])
                        ->first();

                    if (!$category) {
                        $category = CategoryProductStore::create([
                            'name' => $categoryName,
                            'user_create_id' => $this->userId,
                            'company_id' => $this->companyId
                        ]);
                    }
                }

                // Find or create brand dengan uppercase
                $brand = null;
                if (!empty($brandName)) {
                    $brandNameUpper = Str::upper($brandName);
                    
                    $brand = BrandProductStore::whereIn('company_id', $this->accessibleCompanyIds)
                        ->whereRaw('UPPER(name) = ?', [$brandNameUpper])
                        ->first();

                    if (!$brand) {
                        $brand = BrandProductStore::create([
                            'name' => $brandName,
                            'user_create_id' => $this->userId,
                            'company_id' => $this->companyId
                        ]);
                    }
                }

                // Find rack
                $rack = null;
                // if (!empty($rackName)) {
                //     $rack = Rack::whereIn('company_id', $this->accessibleCompanyIds)
                //         ->where('name', $rackName)
                //         ->first();
                // }

                // dd($sellingPrice);

                // Create product
                $productStore = ProductStore::create([
                    'name' => $name,
                    'category_product_store_id' => $category?->id,
                    'brand_product_store_id' => $brand?->id,
                    'variant' => $variant,
                    'specification' => $specification,
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'weight' => $weight,
                    'selling_price' => $sellingPrice,
                    'rack_id' => $rack?->id,
                    'user_create_id' => $this->userId,
                    'company_id' => $this->companyId,
                ]);

                $totalImport++; // increment success
                
            } catch (\Exception $e) {
                // dd($e);
                $errors[] = [
                    'row' => $index + 1,
                    'message' => $e->getMessage(),
                    'data' => isset($row[0]) ? $row[0] : 'Unknown'
                ];
                
                Log::error("Import error on row " . ($index + 1), [
                    'batch_id' => $this->batchId,
                    'error' => $e->getMessage(),
                    'data' => $row
                ]);
            }

            $processed++;
            
            // Update progress every row
            $this->updateProgress($processed, $total, $totalImport, $errors, 'processing');
        }

        // Mark as completed
        $this->updateProgress($processed, $total, $totalImport, $errors, 'completed');
    }

    protected function updateProgress($processed, $total, $totalImport, $errors, $status)
    {
        // Pastikan errors adalah array yang valid
        $errorArray = is_array($errors) ? $errors : [];
        
        ImportProgress::updateOrCreate(
            ['batch_id' => $this->batchId],
            [
                'processed' => $processed,
                'total' => $total,
                'total_import' => $totalImport,
                'errors' => $errorArray, // Laravel auto json_encode
            ]
        );
    }

    public function failed(\Throwable $exception)
    {
        ImportProgress::updateOrCreate(
            ['batch_id' => $this->batchId],
            [
                'status' => 'failed',
                'errors' => [
                    [
                        'row' => 'System',
                        'message' => $exception->getMessage()
                    ]
                ]
            ]
        );
        
        Log::error("Import job failed for batch {$this->batchId}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}