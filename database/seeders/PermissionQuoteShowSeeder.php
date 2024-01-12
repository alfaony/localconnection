<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionQuoteShowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissionRole = Permission::where('method','index')->where('table','quotes')->first();
        $permission = Permission::firstOrCreate([
            'name' => ucwords('show').' Quote',
        ],[
            'method' => 'show',
            'table' => 'quotes',
            'model' => 'Quote',
            'guard_name' => 'web'
        ]);

        foreach ($permissionRole->roles as $a) 
        {
            PermissionRole::create(['role_id' => $a->id, 'permission_id' => $permission->id]);
        }
    }
}

