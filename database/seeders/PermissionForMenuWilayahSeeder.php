<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForMenuWilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = ['index','downloadPdf', 'select2','dataTableJson'];
        $deleted = ['edit', 'create', 'update', 'show', 'destroy', 'store'];

        $tables = ['wilayahs', 'provinces', 'cities', 'districts', 'subdistricts', 'postal_codes'];

        foreach ($tables as $table) {
            Permission::where('table', $table)
                ->whereIn('method', $deleted)
                ->each(function ($permission) {
                    DB::table('permission_role')->where('permission_id', $permission->id)->delete();
                    $permission->delete();
                });
        }

        $wilayahOnly = ['select2'];

        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();

        foreach ($wilayahOnly as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Wilayah',
            ],[
                'method' => $method,
                'table' => 'wilayahs',
                'model' => 'Wilayah',
                'guard_name' => 'web'
            ]);


            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
        }


        foreach ($menu as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Province',
            ],[
                'method' => $method,
                'table' => 'provinces',
                'model' => 'Province',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
        }


        foreach ($menu as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' City',
            ],[
                'method' => $method,
                'table' => 'cities',
                'model' => 'City',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
        }


        foreach ($menu as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' District',
            ],[
                'method' => $method,
                'table' => 'districts',
                'model' => 'District',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
        }

        foreach ($menu as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Sub District',
            ],[
                'method' => $method,
                'table' => 'subdistricts',
                'model' => 'SubDistrict',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
        }


        foreach ($menu as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Postal Code',
            ],[
                'method' => $method,
                'table' => 'postal_codes',
                'model' => 'PostalCode',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
        }

        $this->call(ClearPermissionSeeder::class);
        
    }
}







