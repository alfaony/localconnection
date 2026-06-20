<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Schemas\RoleSchema;
use App\Models\Permission;
use App\Models\PermissionRole;

class PermissionForMenuRouterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cari role staff
        $admin = Role::where('name', RoleSchema::ADMIN)->first();
        $root = Role::where('name', RoleSchema::ROOT)->first();
        $finance = Role::where('name', RoleSchema::FINANCE)->first();
        $manager = Role::where('name', RoleSchema::MANAGER)->first();
        $staffFinance = Role::where('name', RoleSchema::STAFF_FINANCE)->first();
        $managerFinance = Role::where('name', RoleSchema::MANAGER_FINANCE)->first();
        
        $tecknicianRole = Role::where('name', RoleSchema::TECKNICIAN_INTERNET)->first();

        DB::beginTransaction();
        try {
            $routerRole = ['index','edit', 'create', 'update', 'show', 'destroy', 'store', 'select2','mapping','mass-move'];
            
            
             foreach ($routerRole as $method) 
             {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Router',
                ],[
                    'method' => $method,
                    'table' => 'routers',
                    'model' => 'Router',
                    'guard_name' => 'web'
                ]);
                if (in_array($method, ['destroy']))
                {
                    // PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                    PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);  
                    // PermissionRole::create(['role_id' => $tecknicianRole->id, 'permission_id' => $permission->id]);
                } 
                
                if (in_array($method, ['mapping'])) 
                {
                    // PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                    PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                    // PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
                    // PermissionRole::create(['role_id' => $tecknicianRole->id, 'permission_id' => $permission->id]);
                    continue;
                }

                    // PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                    PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                    // PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
                    // PermissionRole::create(['role_id' => $tecknicianRole->id, 'permission_id' => $permission->id]);

            }
            DB::commit();
            $this->call(ClearPermissionSeeder::class);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollBack();
            Log::error($th->getMessage());
        }
    }
}



