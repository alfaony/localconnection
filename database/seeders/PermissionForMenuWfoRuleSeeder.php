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

class PermissionForMenuWfoRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cari role staff
        $this->call(ClearPermissionSeeder::class);
        $roles = Role::WhereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN])->get();

        DB::beginTransaction();
        try {   

            $wfoRule = ['index','edit', 'create','store' ,'update', 'destroy',];


            foreach ($wfoRule as $method) 
            {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' WFO Rule',
                ],[
                    'method' => $method,
                    'table' => 'wfo_rules',
                    'model' => 'WfoRule',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {
                    if(in_array($role->name, [RoleSchema::ROOT, RoleSchema::ADMIN]))
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }
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


