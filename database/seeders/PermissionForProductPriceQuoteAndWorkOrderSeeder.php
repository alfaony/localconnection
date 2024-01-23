<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForProductPriceQuoteAndWorkOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();
        $hr = Role::where('name',RoleSchema::HR)->first();
        $finance = Role::where('name',RoleSchema::FINANCE)->first();
        $procurement = Role::where('name',RoleSchema::PROCUREMENT)->first();
        $sales = Role::where('name',RoleSchema::SALES)->first();
        $pm = Role::where('name',RoleSchema::PM)->first();

        $quotes = ['productPrice'];

        foreach ($quotes as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Quote',
            ],[
                'method' => $method,
                'table' => 'quotes',
                'model' => 'Quote',
                'guard_name' => 'web'
            ]);
            
            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $hr->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $finance->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $procurement->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $sales->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $pm->id, 'permission_id' => $permission->id]);
        }

        $workOrders = ['productPrice'];

        foreach ($workOrders as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' WorkOrder',
            ],[
                'method' => $method,
                'table' => 'work_orders',
                'model' => 'WorkOrder',
                'guard_name' => 'web'
            ]);
            
            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $hr->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $finance->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $procurement->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $sales->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $pm->id, 'permission_id' => $permission->id]);
        }
    }
}
