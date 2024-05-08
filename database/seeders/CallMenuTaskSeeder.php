<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CallMenuTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(TaskStatusSeeder::class);
        $this->call(TaskTypeSeeder::class);
        $this->call(PermissionForTaskSeeder::class);
        $this->call(PermissionForTaskAssignSeeder::class);
    }
}
