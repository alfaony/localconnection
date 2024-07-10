<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TaskStatus;

use App\Schemas\ParamSchema;

class GenerateBacklogsAndSortStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:backlogsandsortstatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // ADD BACKLOG
        $task = TaskStatus::where('name',ParamSchema::BACKLOG)->first();
        
        if(!$task)
        {
            $task = new TaskStatus();
            $task->name = ParamSchema::BACKLOG;
            $task->save();
        }
        
        // ADD SORT
        $tasks =  TaskStatus::all();
        $sort = 0;
        foreach ($tasks as $value) 
        {
            switch ($value->name) {
                case ParamSchema::BACKLOG:
                    $sort = 1;
                    break;
                
                case ParamSchema::TODO:
                    $sort = 2;
                    break;
                case ParamSchema::DOING:
                    $sort = 3;
                    break;
                case ParamSchema::INREVIEW:
                    $sort = 4;
                    break;

                case ParamSchema::NOTCOMPLATE:
                    $sort = 5;
                    break;

                case ParamSchema::COMPLATE:
                    $sort = 6;
                    break;
            }

            $value->sort = $sort;
            $value->save();
        }

        $this->info('Generate ');
    }
}
