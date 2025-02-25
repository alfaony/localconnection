<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForMenuDecisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $invoices = ['index','store','update','destroy','approvement','show'];

        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();
        $manager = Role::where('name',RoleSchema::MANAGER)->first();

        foreach ($invoices as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Decision',
            ],[
                'method' => $method,
                'table' => 'decisions',
                'model' => 'Decision',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            if($method == 'approvement')
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            }else
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
            }
        }
    }
}








