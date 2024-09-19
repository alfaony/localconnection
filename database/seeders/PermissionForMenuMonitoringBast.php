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

class PermissionForMenuMonitoringBast extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   

        $methodBast = ['createsuggest','dataTableJsonWorkOrderWithoutBast'];
        $methodReportProject = ['createsuggest','dataTableJsonWorkOrderWithoutReportProject'];
       
        $roles = Role::all();


        foreach ($methodBast as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Bast',
            ],[
                'method' => $method,
                'table' => 'basts',
                'model' => 'Bast',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            foreach ($roles as $role) 
            {
                PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
            }
        }

        foreach ($methodReportProject as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Report Project',
            ],[
                'method' => $method,
                'table' => 'report_projects',
                'model' => 'ReportProject',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            foreach ($roles as $role) 
            {
                PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
            }
        }
    }
}







