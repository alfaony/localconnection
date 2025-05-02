<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForAccessExportCheckin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $employeeCheckingExports = ['export','checkExportStatus','clearsession'];

        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();
        $manager = Role::where('name',RoleSchema::MANAGER)->first();
        $finance = Role::where('name',RoleSchema::FINANCE)->first();

        foreach ($employeeCheckingExports as $method) 
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
            if ($root && $permission->id) {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            }
            if ($admin && $permission->id) {
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            }
            if ($manager && $permission->id) {
                PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
            }
            if ($finance && $permission->id) {
                PermissionRole::create(['role_id' => $finance->id, 'permission_id' => $permission->id]);
            }
        }
    }
}


















