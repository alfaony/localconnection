<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CallMenuEqipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ReductionSeeder::class);
        $this->call(MakeRoleOfficeManagerSeeder::class);
        $this->call(PermissionForEquipmentSeeder::class);
        $this->call(PermissonForEquipmentReductionSeeder::class);
    }
}
