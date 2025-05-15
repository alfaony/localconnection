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

class PermissionForMenuFlowChartsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        $menus = ['index','create', 'show', 'edit', 'update', 'destroy'
        , 'store', 'infoPic','infoManager','export'];
       
        $roles = Role::all();


        foreach ($menus as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Flowchart',
            ],[
                'method' => $method,
                'table' => 'flowcharts',
                'model' => 'Flowchart',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            foreach ($roles as $role) 
            {
                if (in_array($method, ['create','store','edit','destroy','update']) && in_array($role->name, [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::FINANCE, RoleSchema::PROCUREMENT, RoleSchema::HR, RoleSchema::MANAGER])) {
                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                } else {
                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                }
            }
        }
    }
}








