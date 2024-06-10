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

class PermissionForMenuDailyTaskProject extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   

        $methods = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2', 'customfieldstore' ,'customfieldupdate' ,'customfielddestroy' ,'getcustomfield'];
       
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();
        $director = Role::where('name',RoleSchema::DIRECTOR)->first();
        $manager = Role::where('name',RoleSchema::MANAGER)->first();
        $staff = Role::where('name',RoleSchema::STAFF)->first();

        foreach ($methods as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Daily Task Project',
            ],[
                'method' => $method,
                'table' => 'daily_task_projects',
                'model' => 'DailyTaskProject',
                'guard_name' => 'web'
            ]);

            //assign role & permission

            if($method == "getcustomfield")
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $director->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $staff->id, 'permission_id' => $permission->id]);

            }
            else
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $director->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
            }
        }
    }
}
