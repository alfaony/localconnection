<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Role;
use App\Models\User;

use App\Schemas\RoleSchema;
class UpdateProductPrice extends Command
{
    protected $signature = 'update:product-price {file}';
    protected $description = 'Update product price from an Excel file';

    public function handle()
    {
        $filePath = resource_path('files/' . $this->argument('file'));
        
        $data = Excel::toArray([], $filePath)[0];

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                $productName = $row[0];
                $companyName = $row[1];
                $priceBuy = $row[3];
                $priceSell = $row[4];
                
                $company = Company::where('name',$companyName)->first();

                if ($company) 
                {
                    $product = Product::byCompany($company->id)->where('name', 'LIKE', "%{$productName}%")->first();
                    if ($product) {
                        $product->price_buy = $priceBuy;
                        $product->price_sell = $priceSell;
                        $product->save();
                    }else
                    {
                        $this->error("Product {$productName} not found.");
                    }
                }
            }
        });

        $this->info('Product Price updated successfully.');
    }
}
