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

class PermissionForMenuPosition extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   

        $methods = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2', 'approvement'];
       
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();

        foreach ($methods as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Position',
            ],[
                'method' => $method,
                'table' => 'positions',
                'model' => 'Position',
                'guard_name' => 'web'
            ]);

            //assign role & permission

            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
        }
    }
}
