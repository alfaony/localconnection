<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForMenuDayoffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $methods = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'checkInfo','financeApprovement','hrApprovement','infoApprovementFinance','infoApprovementHr','shareUser'];
        
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
                'name' => ucwords($method).' Dayoff',
            ],[
                'method' => $method,
                'table' => 'dayoffs',
                'model' => 'Dayoff',
                'guard_name' => 'web'
            ]);

            //assign role & 
            if (in_array($method, ['financeApprovement', 'infoApprovementFinance'])) 
            {
                if ($finance) {
                    PermissionRole::create(['role_id' => $finance->id, 'permission_id' => $permission->id]);
                }

                if ($hr) 
                {
                    PermissionRole::create(['role_id' => $hr->id, 'permission_id' => $permission->id]);
                }

                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);

            }
            elseif (in_array($method, ['hrApprovement', 'infoApprovementHr'])) {
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            } else 
            {
                $roles = [$manager, $staff, $sales, $admin, $finance, $root, $director, $procurement, $pm, $hr];
                foreach ($roles as $role) {
                    if ($role) {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }
                }
            }
        }
    }
}

