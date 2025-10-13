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

class PermissionForGetLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cari role staff
        $roles = Role::all();

        DB::beginTransaction();
        try {   

            $roleSales = ['getLocation'];            
             foreach ($roleSales as $method) 
             {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Warehouse',
                ],[
                    'method' => $method,
                    'table' => 'warehouses',
                    'model' => 'Warehouse',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {
                   $check =  PermissionRole::firstOrCreate([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollBack();
            Log::error($th->getMessage());
        }
    }
}



