<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionForMenuInternetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PermissionForMenuDataCenter::class);
        $this->call(PermissionForMenuPopSeeder::class);
        $this->call(PermissionForMenuOdsSeeder::class);
        $this->call(PermissionForMenuCoverageServiceSeeder::class);
        $this->call(PermissionForMenuInternetPackageSeeder::class);
        $this->call(PermissionForMenuInternetCustomerSeeder::class);
    }
}
