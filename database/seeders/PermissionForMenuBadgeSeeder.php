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

class PermissionForMenuBadgeSeeder extends Seeder
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

            $badge = ['index','create','store','show','edit','update','destroy','assignIndex','assignStore','revokeUserBadge'];
            $rolesBadge = Role::where('name',RoleSchema::ROOT)->get();

            foreach ($badge as $method) 
            {
                // create permision
                $permissionBadge = Permission::firstOrCreate([
                    'name' => ucwords($method).' Badge',
                ],[
                    'method' => $method,
                    'table' => 'badges',
                    'model' => 'Badge',
                    'guard_name' => 'web'
                ]);

                foreach ($rolesBadge as $role) 
                {

                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionBadge->id]);
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





