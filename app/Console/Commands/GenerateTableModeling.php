<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\WorkOrder;
use App\Models\Company;
use App\Models\FineTuneTable;
use App\Models\FineTune;
use App\Models\FineTuneFile;
use App\Services\ServiceOpenAi;
use App\Schemas\RoleSchema;
use App\Models\Product;
use App\Models\TableModeling;
use App\Models\ShippingRate;

class GenerateTableModeling extends Command
{
    protected $signature = 'generate:table-modeling';
    protected $description = 'Generate fine-tune dataset and send it to OpenAI Microservice';
    
    public function handle()
    {
        $tables = FineTuneTable::all();
        $service = new ServiceOpenAi();

        $responses = [];
        $companies = Company::get();
        // $companies = Company::where("name",'BOS 1')->get();

        foreach($companies as $company)
        {
            foreach ($tables as $table) 
            {
                $user = $company->user()
                ->whereHas('role', function ($query) {
                    $query->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN]);
                })
                ->first();
                $tableData = $table;
                $table = $table->name;

                $this->info("Processing table: $table");
    
                // 1. Generate JSONL file with chunking
                $dataJson = $this->prepareJsonData($table, $company);

                
                // 2. Insert To Table Modeling
                $tableModeling = TableModeling::updateOrCreate([
                    'fine_tune_table_id' => $tableData->id,
                    'company_id' => $company->id,
                ],[
                    'data_model' => $dataJson,
                ]);
                
                $this->info('Template '.$table. ' Company : '.$company->name.' generated successfully!');
            }
            
            $this->info('Tempalting process completed!');
        }
    }

    /**
     * Generate dataset JSONL for Fine-Tuning using chunking
     */
    private function prepareJsonData($table, $company)
    {
        // Pastikan slug tersedia
        if (!isset($company->slug)) {
            Log::error("Company slug not found for fine-tuning data.");
            return false;
        }
        // Use chunking to process data in batches
        if ($table == "products")
        {
            // Fetch products from the company
            $products = Product::byCompany($company->id)
                ->orderBy('id')
                ->get();

            // Loop through the products and format each product as an associative array
            $formattedProducts = $products->map(function ($product) {
                $productData = [
                    'name' => $product->name,
                    'price_sell' => $product->price_sell,
                    'price_buy' => $product->price_buy,
                ];
            
                // Menambahkan kondisi untuk stok
                if ($product->stock > 0) {
                    $productData['stock'] = $product->stock;
                }
            
                // Anda bisa menambahkan kondisi lain sesuai kebutuhan
                // if ($product->price_sell > 1000) {
                //     $productData['premium'] = true;
                // }
            
                return $productData;
            });


            // Convert to JSON format with each product as an individual entry in JSONL (JSON Lines)
            $jsonData = $formattedProducts->map(function($item) {
                return json_encode($item, JSON_UNESCAPED_UNICODE);
            })->implode("\n");

            // Log the JSON data
            Log::info("JSON data for products: " . $jsonData);

            // Return the JSON data directly
            return $jsonData;
        }

        if($table == "logistics")
        {
            // Fetch shipping rates from the company
            $shippingRates = ShippingRate::get();

            // Loop through the products and format each product as an associative array
            $formattedShippingRates = $shippingRates->map(function ($shippingRate) {
                $shippingRateData = [
                    'provider' => $shippingRate->provider->name,
                    'origin' => $shippingRate->origin->subDistrict->district->city->name,
                    'destination' => $shippingRate->destination->subDistrict->district->city->name,
                    'base_weight' => $shippingRate->base_weight,
                    'base_price' => $shippingRate->base_price,
                    'additional_weight' => $shippingRate->additional_weight,
                    'additional_price' => $shippingRate->additional_price,
                    'rate_per_cbm' => $shippingRate->rate_per_cbm,
                    'delivery_time' => $shippingRate->delivery_time,
                ];
            
                return $shippingRateData;
            });

            // Convert to JSON format with each product as an individual entry in JSONL (JSON Lines)
            $jsonData = $formattedShippingRates->map(function($item) {
                return json_encode($item, JSON_UNESCAPED_UNICODE);
            })->implode("\n");

            // Log the JSON data
            Log::info("JSON data for shipping rates: " . $jsonData);

            // Return the JSON data directly
            return $jsonData;
        }

        // Return false if the table doesn't match
        return false;
    }
}
