<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\DailyTaskCategory;


class CategoryDailyTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = Company::get();

        foreach ($company as $a) 
        {
            $user =$a->user()->first();
            // Create a new record
            DailyTaskCategory::create(
            [
                'name' => 'initiative',
                'user_id' => $user->id
            ]);
        }
    }
}
