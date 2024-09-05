<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;
use App\Models\DailyTask;
use App\Schemas\ParamSchema;

class GenerateAllGroupId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:groupid';

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
        $tasks = DailyTask::whereHas('type', function ($query) {
            $query->where('name', ParamSchema::RECURRING);
        })->get();

        foreach ($tasks as $task) {
            $findaGroup = DailyTask::where('name','like', '%'.$task->name.'%')->first();
            if (isset($findaGroup->recurring_group_id)) 
            {
                $task->recurring_group_id = $findaGroup->recurring_group_id;
            } else 
            {
                $task->recurring_group_id = Uuid::uuid4()->toString();
            }

            $task->save();
            $this->info('Recurring Group ID for task '.$task->name.' is '.$task->recurring_group_id);
        }
    }
}
