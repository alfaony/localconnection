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

class PermissionForMenuUserProfile extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   

        $roles = Role::all();
        
        $methods = ['profileEdit','profileUpdate'];
       

        foreach ($methods as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' User',
            ],[
                'method' => $method,
                'table' => 'users',
                'model' => 'User',
                'guard_name' => 'web'
            ]);

            foreach ($roles as $role) 
            {
                PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
            }
        }
    }
}




