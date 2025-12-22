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

class PermissionForMenuUsedLaptop extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ClearPermissionSeeder::class);
        $roles = Role::whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::PROCUREMENT, RoleSchema::MANAGER, RoleSchema::MANAGER_FINANCE, RoleSchema::FINANCE])->get();
        
        $methods = ['index','edit', 'create', 'update', 'show', 'destroy', 'store', 'maskAsSold','mediaDestroy','checkSerialNumber', 'export'];

        foreach ($methods as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Used Laptop',
            ],[
                'method' => $method,
                'table' => 'used_laptops',
                'model' => 'UsedLaptop',
                'guard_name' => 'web'
            ]);

            foreach ($roles as $role) 
            {
                PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
            }
        }

    }
}

