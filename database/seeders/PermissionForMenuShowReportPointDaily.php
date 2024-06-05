<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForMenuShowReportPointDaily extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dashboards = ['index', 'showReportPointDaily'];

        foreach ($dashboards as $method) 
        {   
            $root = Role::where('name',RoleSchema::ROOT)->first();
            $manager = Role::where('name',RoleSchema::MANAGER)->first();
            $staff = Role::where('name',RoleSchema::STAFF)->first();
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Home',
            ],[
                'method' => $method,
                'table' => 'homes',
                'model' => 'Home',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $staff->id, 'permission_id' => $permission->id]);
        }
    }
}


