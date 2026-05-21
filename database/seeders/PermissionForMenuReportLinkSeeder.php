<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForMenuReportLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $invoices = ['index','edit', 'create', 'update', 'show', 'destroy', 'store', 'import', 'export'];
        $root = Role::whereIn('name',[RoleSchema::ROOT,RoleSchema::ADMIN, RoleSchema::MANAGER])->get();

        foreach ($invoices as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Report Link',
            ],[
                'method' => $method,
                'table' => 'report_links',
                'model' => 'ReportLink',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            foreach ($root as $r) 
            {
                PermissionRole::create(['role_id' => $r->id, 'permission_id' => $permission->id]);
            }
        }

        $this->call(ClearPermissionSeeder::class);
    }
}





