<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\TaskStatus;

class AddStatusTaskToDo extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $collection = array('todo');
        foreach ($collection as $a) 
        {
            $typeStatus = TaskStatus::where('name',$a)->first();
            if(!$typeStatus)
            {
                $typeStatus = New TaskStatus();
                $typeStatus->name = $a;
                $typeStatus->save();
            }
        }
    }
}

