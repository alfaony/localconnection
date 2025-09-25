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

class PermissionForMenuPunishmentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cari role staff
        $roles = Role::WhereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE])->get();

        DB::beginTransaction();
        try {   

            $roleOffices = ['index','edit', 'create', 'update', 'show', 'destroy'];
            $roleBarcodes = ['index','generate'];
            
             foreach ($roleOffices as $method) 
             {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Punishment User',
                ],[
                    'method' => $method,
                    'table' => 'punishment_users',
                    'model' => 'PunishmentUser',
                    'guard_name' => 'web'
                ]);


                foreach ($roles as $role) 
                {

                    // Semua role mendapat permission selain division_access
                    PermissionRole::firstOrCreate([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                    ]);
                }
            }


            foreach ($roleBarcodes as $method) 
            {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Barcode',
                ],[
                    'method' => $method,
                    'table' => 'barcodes',
                    'model' => 'Barcode',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {
                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
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

