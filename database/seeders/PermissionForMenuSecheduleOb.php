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

class PermissionForMenuSecheduleOb extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   

        $methods = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2', 'calendar'];
       
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $bm = Role::where('name',RoleSchema::BM)->first();
        $ob = Role::where('name',RoleSchema::OB)->first();


        foreach ($methods as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Schedule Ob',
            ],[
                'method' => $method,
                'table' => 'schedule_obs',
                'model' => 'ScheduleOb',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $bm->id, 'permission_id' => $permission->id]);
        }

        $dashboards = ['index','showScheduleOb'];

        foreach ($dashboards as $method) 
        {   
            $root = Role::where('name',RoleSchema::ROOT)->first();
            $admin = Role::where('name',RoleSchema::ADMIN)->first();
            $director = Role::where('name',RoleSchema::DIRECTOR)->first();
            $manager = Role::where('name',RoleSchema::MANAGER)->first();
            $staff = Role::where('name',RoleSchema::STAFF)->first();
            // create permision
            $permissions = Permission::firstOrCreate([
                'name' => ucwords($method).' Home',
            ],[
                'method' => $method,
                'table' => 'homes',
                'model' => 'Home',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permissions->id]);
            PermissionRole::create(['role_id' => $bm->id, 'permission_id' => $permissions->id]);
            PermissionRole::create(['role_id' => $ob->id, 'permission_id' => $permissions->id]);
        }
    }
}





