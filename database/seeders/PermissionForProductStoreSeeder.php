<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForProductStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = Role::whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::PROCUREMENT, RoleSchema::MANAGER, RoleSchema::MANAGER_FINANCE, RoleSchema::FINANCE, RoleSchema::SPRINTER])->get();
        
        $this->call(ClearPermissionSeeder::class);

        $methods = ['index','edit', 'create', 'update', 'show', 'destroy', 'store',"print",'import'];

        foreach ($methods as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Product Store',
            ],[
                'method' => $method,
                'table' => 'product_stores',
                'model' => 'ProductStore',
                'guard_name' => 'web'
            ]);

            if($method != 'print')
            {
                $permissionCategory = Permission::firstOrCreate([
                    'name' => ucwords($method).' Category Product Store',
                ],[
                    'method' => $method,
                    'table' => 'category_product_stores',
                    'model' => 'CategoryProductStore',
                    'guard_name' => 'web'
                ]);
    
                $permissionBrand = Permission::firstOrCreate([
                    'name' => ucwords($method).' Brand Product Store',
                ],[
                    'method' => $method,
                    'table' => 'brand_product_stores',
                    'model' => 'BrandProductStore',
                    'guard_name' => 'web'
                ]);
            }

            foreach ($roles as $role) 
            {
                PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionCategory->id]);
                PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionBrand->id]);
            }
        }

    }
}



