<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForMenuWeeklyReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $weeklyReport = ['index','edit', 'create', 'update', 'show', 'destroy', 'store', 'downloadPdf', 'dataTableJson','reminderDashboard','mandatory_report'];
        $weeklyReportDashbaord = ['index','data'];

        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();
        $manager = Role::where('name',RoleSchema::MANAGER)->first();

        foreach ($weeklyReport as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Weekly Report',
            ],[
                'method' => $method,
                'table' => 'weekly_reports',
                'model' => 'WeeklyReport',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
        }

        foreach ($weeklyReportDashbaord as $method) 
        {
            // create permision
            $permissionDashboard = Permission::firstOrCreate([
                'name' => ucwords($method).' Dashboard Weekly Report',
            ],[
                'method' => $method,
                'table' => 'dashboard_weekly_reports',
                'model' => 'DashboardWeeklyReport',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permissionDashboard->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permissionDashboard->id]);
            PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permissionDashboard->id]);
        }


    }
}








