<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionSuggestionWorkOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissionRole = Permission::where('method','index')->where('table','supliers')->first();
        $permission = Permission::firstOrCreate([
            'name' => ucwords('suggestionWorkOrder').' Suplier',
        ],[
            'method' => 'suggestionWorkOrder',
            'table' => 'supliers',
            'model' => 'Suplier',
            'guard_name' => 'web'
        ]);

        foreach ($permissionRole->roles as $a) 
        {
            PermissionRole::create(['role_id' => $a->id, 'permission_id' => $permission->id]);
        }
    }
}
