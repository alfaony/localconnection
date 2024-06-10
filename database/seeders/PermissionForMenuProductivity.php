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

class PermissionForMenuProductivity extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   

        $trainings = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2','addpoint'];
        $iprigs = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2','addpoint'];
        $salesAchivement = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2','addpoint'];

        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();
        $director = Role::where('name',RoleSchema::DIRECTOR)->first();
        $manager = Role::where('name',RoleSchema::MANAGER)->first();
        $staff = Role::where('name',RoleSchema::STAFF)->first();
        $sales = Role::where('name',RoleSchema::SALES)->first();


        foreach ($trainings as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Trainings',
            ],[
                'method' => $method,
                'table' => 'trainings',
                'model' => 'Training',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            if($method == "addpoint")
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $director->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
            }else
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $director->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $staff->id, 'permission_id' => $permission->id]);
            }
        }

        foreach ($iprigs as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Ip Rights',
            ],[
                'method' => $method,
                'table' => 'ip_rights',
                'model' => 'IpRights',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            if($method == "addpoint")
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $director->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
            }else
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $director->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $staff->id, 'permission_id' => $permission->id]);
            }
        }

        foreach ($salesAchivement as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).'Sales Achievements',
            ],[
                'method' => $method,
                'table' => 'sales_achievements',
                'model' => 'SalesAchievement',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            if($method == "addpoint")
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $director->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
            }else
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $director->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $sales->id, 'permission_id' => $permission->id]);
            }
        }

    }
}


