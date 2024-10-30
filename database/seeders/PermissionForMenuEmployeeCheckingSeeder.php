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

class PermissionForMenuEmployeeCheckingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   

        $methods = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'updatestatus','checkLastScheduledCheckin'];
        
        $manager = Role::where('name',RoleSchema::MANAGER)->first();
        $staff = Role::where('name',RoleSchema::STAFF)->first();
        $sales = Role::where('name',RoleSchema::SALES)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();
        $finance = Role::where('name',RoleSchema::FINANCE)->first();
        $procurement = Role::where('name',RoleSchema::PROCUREMENT)->first();
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $pm = Role::where('name',RoleSchema::PM)->first();
        $hr = Role::where('name',RoleSchema::HR)->first();
        $director = Role::where('name',RoleSchema::DIRECTOR)->first();


        foreach ($methods as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Employee Checking',
            ],[
                'method' => $method,
                'table' => 'employee_checkings',
                'model' => 'EmployeeChecking',
                'guard_name' => 'web'
            ]);

            //assign role & permission

            PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $staff->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $sales->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $finance->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $director->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $procurement->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $pm->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $hr->id, 'permission_id' => $permission->id]);
        }
    }
}