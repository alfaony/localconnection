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

class PermissionForMenuMeetingSeeder extends Seeder
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

            $itemRequest = ['index','edit', 'create', 'update', 'show', 'destroy', 'store', 'select2','join'];
            $dashboard = ['meetingAgenda'];
            
             foreach ($itemRequest as $method) 
             {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Meeting',
                ],[
                    'method' => $method,
                    'table' => 'meetings',
                    'model' => 'Meeting',
                    'guard_name' => 'web'
                ]);


                foreach ($roles as $role) 
                {
                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                }
            }


            foreach ($dashboard as $method) 
            {
                // create permision
                $permissionsDashboard = Permission::firstOrCreate([
                    'name' => ucwords($method).' Home',
                ],[
                    'method' => $method,
                    'table' => 'homes',
                    'model' => 'Home',
                    'guard_name' => 'web'
                ]); 

                //assign role & permission
                foreach ($roles as $role) 
                {
                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionsDashboard->id]);
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


