<?php

namespace App\Observers;

use App\Models\TaskAssign;
use App\Models\TaskStatus;
use App\Schemas\ParamSchema;
use Carbon\Carbon;

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
            } else 
            {
                $taskAssign->point = ParamSchema::ZERO; // Set point to zero for non-regular tasks
            }
            $taskAssign->save();
        } elseif (!$taskAssign->point && $taskAssign->taskStatus->name == ParamSchema::COMPLATE) {
            // Assign point directly from the task
            $taskAssign->point = $taskAssign->task->point;
            $taskAssign->save();

            if ($taskAssign->task->taskType->name == ParamSchema::REGULAR) 
            {
                $newTaskAssign = $taskAssign->replicate(); // Membuat salinan dari TaskAssign
                $newTaskAssign->date = Carbon::parse($taskAssign->date)->addWeek(); // Mengatur tanggal satu minggu ke depan
                $newTaskAssign->task_status_id = TaskStatus::where('name', ParamSchema::DOING)->first()->id; // Mengatur status ke DOING
                $newTaskAssign->save(); // Menyimpan TaskAssign baru
            }
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
                $taskAssign->point = ParamSchema::ZERO; // Set point to zero for non-regular tasks
            }
            $taskAssign->save();
        } elseif (!$taskAssign->point && $taskAssign->taskStatus->name == ParamSchema::COMPLATE) {
            // Assign point directly from the task
            $taskAssign->point = $taskAssign->task->point;
            $taskAssign->save();

            if ($taskAssign->task->taskType->name == ParamSchema::REGULAR) 
            {
                $newTaskAssign = $taskAssign->replicate(); // Membuat salinan dari TaskAssign
                $newTaskAssign->date = Carbon::parse($taskAssign->date)->addWeek(); // Mengatur tanggal satu minggu ke depan
                $newTaskAssign->task_status_id = TaskStatus::where('name', ParamSchema::DOING)->first()->id; // Mengatur status ke DOING
                $newTaskAssign->save(); // Menyimpan TaskAssign baru
            }
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
