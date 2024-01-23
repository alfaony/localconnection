<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Ramsey\Uuid\Uuid;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'delete_able'=> 0,
            'name'=>'root',
            'email' => 'root@emcdev.me',
            'password' => bcrypt('root123!'),
        ]);
    }
}
