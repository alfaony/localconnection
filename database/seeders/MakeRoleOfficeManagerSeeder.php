<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Schemas\RoleSchema;
class MakeRoleOfficeManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $officeboy = Role::where('name',RoleSchema::OB)->first();
        if(!$officeboy)
        {
            $officeboy = Role::create([
                'name' => RoleSchema::OB,
                'desc' => 'Akun Office Boy',
                'guard_name' => 'web'
            ]);
        }

        $bm = Role::where('name',RoleSchema::BM)->first();
        if(!$bm)
        {
            $bm = Role::create([
                'name' => RoleSchema::BM,
                'desc' => 'Akun Manager Office Boy',
                'guard_name' => 'web'
            ]);
        }
    }
}
