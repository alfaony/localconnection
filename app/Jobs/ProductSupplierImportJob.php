<?php

namespace App\Jobs;

use App\Models\ImportProgress;
use App\Models\ProductSupplier;
use App\Models\SupplierCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ProductSupplierImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $batchId;
    protected $companyId;

    public function __construct($data, $batchId, $companyId)
    {
        $this->data = $data;
        $this->batchId = $batchId;
        $this->companyId = $companyId;
    }

    public function handle()
    {
        foreach ($this->data as $index => $row) 
        {
            // Update progress
            ImportProgress::where('batch_id', $this->batchId)->increment('processed');

            // Cek jika semua data kosong, maka lewati
            if (!array_filter($row)) continue;

            try {
                // Parsing data kategori
                $categories = array_map('trim', explode(',', $row['Master kategori']));

                $categoryIds = [];
                foreach ($categories as $categoryName) 
                {
                    if ($categoryName) {
                        $category = SupplierCategory::byCompanyJob($this->companyId)->firstOrCreate(['name' => $categoryName], ['company_id' => $this->companyId]);
                        $categoryIds[] = $category->id;
                    }
                }

                // Simpan data supplier
                $supplier = ProductSupplier::updateOrCreate(
                    [
                        'store_name' => $row['NAMA TOKO'],
                        'owner_name' => $row['PEMILIK TOKO'],
                        'company_id' => $this->companyId,
                    ],
                    [
                        'phone_number' => $row['NO HP'],
                        'location' => $row['TEMPAT'],
                        'sales_info' => $row['INFORMASI PENJUALAN'],
                        'additional_info' => $row['INFORMASI TAMBAHAN'],
                    ]
                );

                // Hubungkan dengan kategori
                if ($supplier->exists) {
                    $supplier->supplierCategories()->sync($categoryIds);
                } else {
                    $supplier->supplierCategories()->syncWithoutDetaching($categoryIds);
                }
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                // Jika terjadi error, maka simpan error di field errors
                ImportProgress::where('batch_id', $this->batchId)->update(['errors' => array_merge(
                    ImportProgress::where('batch_id', $this->batchId)->value('errors'), 
                    [$index => $e->getMessage()]
                )]);
            }
        }
    }
}
