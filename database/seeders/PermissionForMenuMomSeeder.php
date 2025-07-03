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

class PermissionForMenuMomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cari role staff
        $roles = Role::whereNotIn('name', [RoleSchema::BM, RoleSchema::OB])->get();

        DB::beginTransaction();
        try {

            $momRequest = ['index','edit', 'create', 'update', 'show', 'destroy', 'store', 'select2', 'updateAgenda','storeAgenda','deleteAgenda','updateTask', 'editTask','storeTask','deleteTask','approveExternalTask'];
            
             foreach ($momRequest as $method) 
             {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Minute of Meeting',
                ],[
                    'method' => $method,
                    'table' => 'moms',
                    'model' => 'Mom',
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



