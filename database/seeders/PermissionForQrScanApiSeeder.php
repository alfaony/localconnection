<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;

class PermissionForQrScanApiSeeder extends Seeder
{
    public function run()
    {
        $this->call(ClearPermissionSeeder::class);
        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->command->error('Tidak ada Role ditemukan.');
            return;
        }

        $permissions = [
            // Used Laptop
            ['table' => 'used_laptops', 'method' => 'getUsedLaptopDetail', 'name' => 'Get Detail Used Laptop (Mobile)'],
            ['table' => 'used_laptops', 'method' => 'updateUsedLaptop', 'name' => 'Update Used Laptop (Mobile)'],
            
            // Used Item
            ['table' => 'used_items', 'method' => 'getUsedItemDetail', 'name' => 'Get Detail Used Item (Mobile)'],
            ['table' => 'used_items', 'method' => 'updateUsedItem', 'name' => 'Update Used Item (Mobile)'],
            
            // Product Store
            ['table' => 'product_stores', 'method' => 'getProductStoreDetail', 'name' => 'Get Detail Product Store (Mobile)'],
            ['table' => 'product_stores', 'method' => 'updateProductStore', 'name' => 'Update Product Store (Mobile)'],
            
            // Internet Customer
            ['table' => 'internet_customers', 'method' => 'getInternetCustomerDetail', 'name' => 'Get Detail Internet Customer (Mobile)'],
            
            // Quotes
            ['table' => 'quotes', 'method' => 'getQuotationPdf', 'name' => 'Download Quotation PDF (Mobile)'],
        ];

        foreach ($permissions as $permData) {
            $permission = Permission::firstOrCreate(
                [
                    'table'  => $permData['table'],
                    'method' => $permData['method'],
                ],
                [
                    'name'       => $permData['name'],
                    'model'      => 'API',
                    'guard_name' => 'api'
                ]
            );

            foreach ($roles as $role) {
                PermissionRole::firstOrCreate([
                    'role_id'       => $role->id,
                    'permission_id' => $permission->id
                ]);
            }
        }

        $this->command->info('Permission QrScanApi berhasil disinkronkan.');
    }
}