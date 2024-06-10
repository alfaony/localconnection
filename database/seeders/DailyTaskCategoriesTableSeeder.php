<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class DailyTaskCategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $user = User::where('email', 'root@emcdev.me')->firstOrFail();

        DB::table('daily_task_categories')->insert([
            [
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'slug' => 'category-1',
                'name' => 'Keloola',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'slug' => 'category-2',
                'name' => 'Absensi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
