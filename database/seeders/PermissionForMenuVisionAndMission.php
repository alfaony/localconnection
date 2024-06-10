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

class PermissionForMenuVisionAndMission extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   

        $methodsVision = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2'];
       
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();
        $director = Role::where('name',RoleSchema::DIRECTOR)->first();
        $manager = Role::where('name',RoleSchema::MANAGER)->first();

        foreach ($methodsVision as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Vision',
            ],[
                'method' => $method,
                'table' => 'visions',
                'model' => 'Vision',
                'guard_name' => 'web'
            ]);

            //assign role & permission

            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $director->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
        }

        $methodsMission = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2'];
       
        foreach ($methodsMission as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Mission',
            ],[
                'method' => $method,
                'table' => 'missions',
                'model' => 'Mission',
                'guard_name' => 'web'
            ]);

            //assign role & permission

            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $director->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
        }
    }
}

