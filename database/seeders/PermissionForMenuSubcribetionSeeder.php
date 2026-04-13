<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForMenuSubcribetionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vehicles = ['index','create', 'show', 'edit', 'update', 'destroy'
        , 'store', 'infoPic','infoManager','export','storePhoto','destroyPhoto'];


        $subcribetions = ['index','create', 'show', 'edit', 'update', 'destroy'
        , 'store', 'infoPic','infoManager','export'];

        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();
        $bm = Role::where('name',RoleSchema::BM)->first();

        foreach ($vehicles as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Vehicle',
            ],[
                'method' => $method,
                'table' => 'vehicles',
                'model' => 'Vehicle',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            if ($root && $permission->id) {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            }

            if ($bm && $permission->id) {
                PermissionRole::create(['role_id' => $bm->id, 'permission_id' => $permission->id]);
            }

            if($admin && $permission->id) {
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            }
        }

        foreach ($subcribetions as $method) 
        {
            // create permision
            $permissionSub = Permission::firstOrCreate([
                'name' => ucwords($method).' Subscribe 57554 Letter',
            ],[
                'method' => $method,
                'table' => 'subscribe_letters',
                'model' => 'SubscribeLetter',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            if ($root && $permissionSub->id) {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permissionSub->id]);
            }
            if ($bm && $permissionSub->id) {
                PermissionRole::create(['role_id' => $bm->id, 'permission_id' => $permissionSub->id]);
            }

            if($admin && $permissionSub->id) {
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permissionSub->id]);
            }
            
        }
    }
}



















