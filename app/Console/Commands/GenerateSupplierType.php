<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\SupplierType;
use App\Models\ProductSupplier;

class GenerateSupplierType extends Command
{
    // Nama dan signature command
    protected $signature = 'generate:supplier-type-for-all-companies';

    // Deskripsi command
    protected $description = 'Generate a new SupplierType for each Company and apply it to all ProductSuppliers';

    // Jalankan perintah
    public function handle()
    {
        // Ambil semua perusahaan (Company) yang ada
        $companies = Company::all();

        foreach ($companies as $company) {
            $this->info("Processing for Company: {$company->name}");

            // Membuat SupplierType baru dengan nama "Toko" untuk setiap company
            $supplierType = SupplierType::firstOrCreate(
                ['company_id' => $company->id, 'name' => 'Toko'],
                ['company_id' => $company->id, 'name' => 'Toko']
            );

            $this->info("SupplierType 'Toko' created for company: {$company->name}");

            // Mengupdate semua ProductSupplier untuk perusahaan ini dengan supplier_type_id yang baru
            $updated = ProductSupplier::where('company_id', $company->id)
                ->update(['supplier_type_id' => $supplierType->id]);

            $this->info("Updated {$updated} ProductSuppliers for company: {$company->name}.");
        }

        return 0;
    }
}