<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForMenuProductSupplier extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $productsSup = ['index','edit', 'create', 'update', 'show', 'destroy', 'store', 'import','export','importProgress','exportProgress'];
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();
        $procurement = Role::where('name',RoleSchema::PROCUREMENT)->first();
        $manager = Role::where('name',RoleSchema::MANAGER)->first();

        foreach ($productsSup as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Product Supplier',
            ],[
                'method' => $method,
                'table' => 'product_suppliers',
                'model' => 'ProductSupplier',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $procurement->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
        }
    }
}
