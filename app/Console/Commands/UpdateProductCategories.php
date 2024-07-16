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
class UpdateProductCategories extends Command
{
    protected $signature = 'update:product-categories {file}';
    protected $description = 'Update product categories from an Excel file';

    public function handle()
    {
        $filePath = public_path($this->argument('file'));
        $data = Excel::toArray([], $filePath)[0];

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                $companyName = $row[0];
                $productName = $row[1];
                $categoryName = $row[5];

                $company = Company::where('name',$companyName)->first();

                if ($company) {
                    $category = ProductCategory::byCompany($company->id)->where('name', 'LIKE', "%{$categoryName}%")->first();
                    if(!$category)
                    {
                        $admin = Role::where('name',RoleSchema::ADMIN)->first();
                        $root = Role::where('name',RoleSchema::ROOT)->first();

                        $user = User::where('company_id', $company->id)
                            ->where(function ($query) use ($root, $admin) {
                                $query->where('role_id', $root->id)
                                    ->orWhere('role_id', $admin->id);
                            })
                        
                            ->first();

                        $category = ProductCategory::create(['name' => $categoryName ,'user_id' => $user->id]);
                    }
                    $product = Product::byCompany($company->id)->where('name', 'LIKE', "%{$productName}%")->first();
                    

                    if ($product) {
                        $product->product_category_id = $category->id;
                        $product->save();
                    }
                }
            }
        });

        $this->info('Product categories updated successfully.');
    }
}