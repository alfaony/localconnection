<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Schemas\RoleSchema;

use App\Models\User;
use App\Models\Role;

class AllUserIsAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();

        $allUser = User::all();
        foreach ($allUser as $user) 
        {
            if(!$user->delete_able)
            {
                $user->role_id = $root->id;
                $user->save();
            }else
            {
                $user->role_id = $admin->id;
                $user->save();
            }
        }
    }
}
