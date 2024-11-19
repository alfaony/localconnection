<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForMenuBastExportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // customers
        $role = Role::all();

        $customers = ['export','checkExportStatus','clearsession'];

        foreach ($customers as $method) 
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
            foreach ($role as $a) 
            {
                PermissionRole::create(['role_id' => $a->id, 'permission_id' => $permission->id]);
            }
        }

    }
}


