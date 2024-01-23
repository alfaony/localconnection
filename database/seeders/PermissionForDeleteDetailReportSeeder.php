<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForDeleteDetailReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissionRole = Permission::where('method','index')->where('table','report_projects')->first();
        $permission = Permission::firstOrCreate([
            'name' => ucwords('destroyDetail').' Report Project',
        ],[
            'method' => 'destroyDetail',
            'table' => 'report_projects',
            'model' => 'ReportProject',
            'guard_name' => 'web'
        ]);

        foreach ($permissionRole->roles as $a) 
        {
            // create permision

            //assign role & permission
            PermissionRole::create(['role_id' => $a->id, 'permission_id' => $permission->id]);
        }
    }
}
