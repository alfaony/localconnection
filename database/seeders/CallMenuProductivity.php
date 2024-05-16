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
        
    }
}
