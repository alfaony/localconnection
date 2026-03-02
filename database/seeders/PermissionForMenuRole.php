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

class PermissionForMenuRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   

        $methods = ['updateName','updateMenuPermissions','selectAll','deselectAll'];
       
        $root = Role::where('name',RoleSchema::ROOT)->first();


        foreach ($methods as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Shifting Ob',
            ],[
                'method' => $method,
                'table' => 'roles',
                'model' => 'Role',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);

        }
    }
}




