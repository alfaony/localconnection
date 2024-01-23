<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionWorkOrderShowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissionRole = Permission::where('method','index')->where('table','work_orders')->first();
        $permission = Permission::firstOrCreate([
            'name' => ucwords('show').' Work Order',
        ],[
            'method' => 'show',
            'table' => 'work_orders',
            'model' => 'WorkOrder',
            'guard_name' => 'web'
        ]);

        foreach ($permissionRole->roles as $a) 
        {
            PermissionRole::create(['role_id' => $a->id, 'permission_id' => $permission->id]);
        }
    }
}

