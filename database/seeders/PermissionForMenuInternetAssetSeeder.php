<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForMenuInternetAssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = ['index','edit', 'create', 'update', 'show', 'destroy', 'store', 'downloadPdf', 'select2','dataTableJson'];
        $wilayahOnly = ['index','create', 'edit', 'update', 'show', 'destroy', 'store', 'select2','dataTableJson'];

        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();

        foreach ($wilayahOnly as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Internet Asset',
            ],[
                'method' => $method,
                'table' => 'internet_assets',
                'model' => 'InternetAsset',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
        }
    }
}
