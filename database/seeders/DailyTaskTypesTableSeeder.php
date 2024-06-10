<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DailyTaskTypesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('daily_task_types')->insert(
        [
            [
                'id' => Str::uuid(),
                'slug' => 'daily',
                'name' => 'Daily',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'recurring',
                'name' => 'Recurring',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
