<?php

namespace App\Observers;

use App\Models\TaskAssign;
use App\Schemas\ParamSchema;

class TaskAssignObserver
{
    /**
     * Handle the TaskAssign "created" event.
     *
     * @param  \App\Models\TaskAssign  $taskAssign
     * @return void
     */
    public function created(TaskAssign $taskAssign)
    {
        if (!$taskAssign->point && $taskAssign->taskStatus->name == ParamSchema::NOTCOMPLATE) 
        {
            if ($taskAssign->task->taskType->name == ParamSchema::REGULAR) 
            {
                $taskAssign->point = -abs($taskAssign->task->point); // Make point negative
            } else {
                $taskAssign->point = 0; // Set point to zero for non-regular tasks
            }
            $taskAssign->save();
        } elseif (!$taskAssign->point && $taskAssign->taskStatus->name == ParamSchema::COMPLATE) {
            // Assign point directly from the task
            $taskAssign->point = $taskAssign->task->point;
            $taskAssign->save();
        }
    }

    /**
     * Handle the TaskAssign "updated" event.
     *
     * @param  \App\Models\TaskAssign  $taskAssign
     * @return void
     */
    public function updated(TaskAssign $taskAssign)
    {
        if (!$taskAssign->point && $taskAssign->taskStatus->name == ParamSchema::NOTCOMPLATE) 
        {
            if ($taskAssign->task->taskType->name == ParamSchema::REGULAR) 
            {
                $taskAssign->point = -abs($taskAssign->task->point); // Make point negative
            } else {
                $taskAssign->point = 0; // Set point to zero for non-regular tasks
            }
            $taskAssign->save();
        } elseif (!$taskAssign->point && $taskAssign->taskStatus->name == ParamSchema::COMPLATE) {
            // Assign point directly from the task
            $taskAssign->point = $taskAssign->task->point;
            $taskAssign->save();
        }
        
    }

    /**
     * Handle the TaskAssign "deleted" event.
     *
     * @param  \App\Models\TaskAssign  $taskAssign
     * @return void
     */
    public function deleted(TaskAssign $taskAssign)
    {
        //
    }

    /**
     * Handle the TaskAssign "restored" event.
     *
     * @param  \App\Models\TaskAssign  $taskAssign
     * @return void
     */
    public function restored(TaskAssign $taskAssign)
    {
        //
    }

    /**
     * Handle the TaskAssign "force deleted" event.
     *
     * @param  \App\Models\TaskAssign  $taskAssign
     * @return void
     */
    public function forceDeleted(TaskAssign $taskAssign)
    {
        //
    }
}
