<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CallMenuAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AttendanceSettingSeeder::class);
        $this->call(PermissionForMenuAttendanceSeeder::class);
        $this->call(PermissionForMenuReportPointSeeder::class);
    }
}
