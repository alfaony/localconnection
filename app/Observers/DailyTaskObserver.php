<?php

namespace App\Observers;

use App\Models\DailyTask;

class DailyTaskObserver
{
    public function updated(DailyTask $dailyTask)
    {
        if ($dailyTask->isDirty() && $dailyTask->momTask) 
        {
            $dailyTask->momTask->update([
                'title' => $dailyTask->name,
                'description' => $dailyTask->description,
                'start_date' => $dailyTask->start_date,
                'end_date' => $dailyTask->end_date,
                'task_status_id' => $dailyTask->task_status_id
            ]);
        }
    }
}
