<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForMenuWorkOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // customers
        $role = Role::all();

        $workOrders = ['export','checkExportStatus','clearsession'];

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
            foreach ($role as $a) 
            {
                PermissionRole::create(['role_id' => $a->id, 'permission_id' => $permission->id]);
            }
        }

    }
}


