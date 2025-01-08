<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\Product;

class RestoreProductBos1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:product-bos-1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore all deleted products for BOS 1 company';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \DB::beginTransaction();
        try {
            $company = Company::where('name', "BOS 1")->first();

            if (!$company) {
                $this->error('Company "BOS 1" not found.');
                return;
            }

            // Restore only soft deleted products
            $products = Product::onlyTrashed()->byCompany($company->id)->get();

            if ($products->isEmpty()) {
                $this->info('No deleted products found for BOS 1.');
                return;
            }

            foreach ($products as $product) {
                $product->restore();
                $this->info('Product "' . $product->name . '" has been restored.');
            }

            \DB::commit();
            $this->info('All deleted products for BOS 1 have been restored.');
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->error('Restore failed.');
            $this->error($e->getMessage());
        }
    }
}