<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Schemas\RoleSchema;

class RolePermissionForDirecturManagerStaff extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $director = Role::where('name',RoleSchema::DIRECTOR)->first();
        if(!$director)
        {
            $director = Role::create([
                'name' => RoleSchema::DIRECTOR,
                'desc' => 'Akun Director',
                'guard_name' => 'web'
            ]);
        }

        $manager = Role::where('name',RoleSchema::MANAGER)->first();
        if(!$manager)
        {
            $manager = Role::create([
                'name' => RoleSchema::MANAGER,
                'desc' => 'Akun Manager Office Boy',
                'guard_name' => 'web'
            ]);
        }

        $staff = Role::where('name',RoleSchema::STAFF)->first();
        if(!$staff)
        {
            $bm = Role::create([
                'name' => RoleSchema::STAFF,
                'desc' => 'Akun Staff Office Boy',
                'guard_name' => 'web'
            ]);
        }
    }
}

