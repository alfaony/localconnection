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

class PermissionForHistoryInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   

        $methods = ['history'];
       
        $roles = Role::all();


        foreach ($methods as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Invoice',
            ],[
                'method' => $method,
                'table' => 'invoices',
                'model' => 'Invoice',
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
