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

class PermissionForMenuSecurity extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        $security = Role::where('name',RoleSchema::SECURITY)->first();
        if(!$security)
        {
            $security = Role::create([
                'name' => RoleSchema::SECURITY,
                'desc' => 'Akun Security',
                'guard_name' => 'web'
            ]);
        }

        $securityChecks = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2'];
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $bm = Role::where('name',RoleSchema::BM)->first();
        $security = Role::where('name',RoleSchema::SECURITY)->first();

        foreach ($securityChecks as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Security check',
            ],[
                'method' => $method,
                'table' => 'security_checks',
                'model' => 'SecurityCheck',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            if($method == "destroy")
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $bm->id, 'permission_id' => $permission->id]);
            }else
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $bm->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $security->id, 'permission_id' => $permission->id]);
            }

        }

        $cctvChecks = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2'];
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $bm = Role::where('name',RoleSchema::BM)->first();
        $security = Role::where('name',RoleSchema::SECURITY)->first();

        foreach ($securityChecks as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Cctv check',
            ],[
                'method' => $method,
                'table' => 'cctv_checks',
                'model' => 'CctvCheck',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            if($method == "destroy")
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $bm->id, 'permission_id' => $permission->id]);
            }else
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $bm->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $security->id, 'permission_id' => $permission->id]);
            }
        }
    }
}

