<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CallMenuProductivity extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolePermissionForDirecturManagerStaff::class);
        $this->call(PermissionForMenuProductivity::class);
        $this->call(PermissionForReportPoinProductivity::class);
        $this->call(DailyTaskTypesTableSeeder::class);
        $this->call(PermissionForMenuDailyActivity::class);
        $this->call(PermissionForMenuDailyTaskProject::class);
        $this->call(PermissionForMenuShowDailyTaskProject::class);
        $this->call(PermissionForMenuShowReportPointDaily::class);
        $this->call(AddStatusTaskToDo::class);       
        $this->call(PermissionForMenuDivision::class);       
        $this->call(PermissionForMenuObjective::class);       
        $this->call(PermissionForDailyTaskStatusChange::class);       
        $this->call(PermissionForVisionAndMission::class);
        $this->call(CategoryDailyTaskSeeder::class);
        $this->call(PermissionForMenuProjectDashboardTable::class);
    }
}
