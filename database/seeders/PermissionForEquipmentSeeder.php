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

class PermissionForEquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $equipment = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2','history'];
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $bm = Role::where('name',RoleSchema::BM)->first();
        $ob = Role::where('name',RoleSchema::OB)->first();

        foreach ($equipment as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Equipment',
            ],[
                'method' => $method,
                'table' => 'equipment',
                'model' => 'Equipment',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            if($method == "create" || $method == "history" || $method == "destroy")
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $bm->id, 'permission_id' => $permission->id]);
            }else
            {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $bm->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $ob->id, 'permission_id' => $permission->id]);

            }
        }
    }
}
