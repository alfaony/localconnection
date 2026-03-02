<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForMenuUsedComputer extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(GenerateMasterTypePerCompanySeeder::class);
        $this->call(PermissionForMenuUsedLaptop::class);
        $this->call(PermissionForMenuMasterCheckItems::class);
        $this->call(PermissionForMenuUsedItemSeeder::class);
    }
}
