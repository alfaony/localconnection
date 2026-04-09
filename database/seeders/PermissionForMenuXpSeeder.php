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

class PermissionForMenuXpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::beginTransaction();
        try {   

            $xpConfig = ['index','create','store','show','edit','update','destroy','assignIndex','assignUpdate'];
            $rolesXp = Role::where('name',RoleSchema::ROOT)->get();

            foreach ($xpConfig as $method) 
            {
                // create permision
                $permissionXpConfig = Permission::firstOrCreate([
                    'name' => ucwords($method).' Xp Config',
                ],[
                    'method' => $method,
                    'table' => 'xp_configs',
                    'model' => 'XpConfig',
                    'guard_name' => 'web'
                ]);

                foreach ($rolesXp as $role) 
                {

                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionXpConfig->id]);
                }
            }

            $employeeXp = ['index','store','destroy','myHistory','leaderboard','userHistory'];
            $rolesEmployeeXp = Role::whereNotIn('name', [
                RoleSchema::CUSTOMER_SOFTWARE,
                RoleSchema::CUSTOMER_INTERNET,
            ])->get();

            foreach ($employeeXp as $method) 
            {
                // create permision
                $permissionEmployeeXp = Permission::firstOrCreate([
                    'name' => ucwords($method).' Employee Xp',
                ],[
                    'method' => $method,
                    'table' => 'employee_xps',
                    'model' => 'EmployeeXp',
                    'guard_name' => 'web'
                ]);

                foreach ($rolesEmployeeXp as $role) 
                {
                    if (in_array($method, ['index','store','destroy']) && in_array($role->name, [RoleSchema::ROOT])) {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionEmployeeXp->id]);
                    }
                    if (in_array($method, ['index','myHistory','leaderboard','userHistory'])) {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionEmployeeXp->id]);
                    }
                }
            }


            $this->call(ClearPermissionSeeder::class);
            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollBack();
            Log::error($th->getMessage());
        }
    }
}





