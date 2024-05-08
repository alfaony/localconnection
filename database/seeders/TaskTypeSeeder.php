<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\TaskType;

class TaskTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $collection = array('regular','special','security');
        foreach ($collection as $a) 
        {
            $taskType = TaskType::where('name',$a)->first();
            if(!$taskType)
            {
                $taskType = New TaskType();
                $taskType->name = $a;
                $taskType->save();
            }
        }
    }
}
