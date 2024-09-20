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

class PermissionForMenuDevice extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $assets = ['index','dataJson'];
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $bm = Role::where('name',RoleSchema::BM)->first();
        $ob = Role::where('name',RoleSchema::OB)->first();

        foreach ($assets as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Device',
            ],[
                'method' => $method,
                'table' => 'devices',
                'model' => 'Device',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $bm->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $ob->id, 'permission_id' => $permission->id]);
        }
    }
}


