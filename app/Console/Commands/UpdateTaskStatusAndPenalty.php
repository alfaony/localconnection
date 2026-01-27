<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\DailyTask;
use App\Models\SettingCompany;
use App\Models\User;
use App\Models\TaskStatus;
use App\Models\Division;
use App\Models\DailyTaskStatusRecord;
use App\Models\DailyTaskMessage;
use App\Models\WeeklyReport;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

class UpdateTaskStatusAndPenalty extends Command
{
    protected $signature = 'dailytask:check-status';
    protected $description = 'Update overdue TODO tasks to NOT COMPLETE and apply penalties. Also auto-complete stale DOING/NOT COMPLETE tasks.';

    public function handle()
    {
        $now = Carbon::now();
        

        // =============================
        // 1. TODO → NOT COMPLETE (lewat deadline)
        // =============================

        $todoTasks = DailyTask::with('assign')->whereHas('taskStatus', function ($query) {
            $query->where('name', ParamSchema::TODO);
        })
        ->whereDate('end_date', '<', $now)
        ->whereHas('assign')
        ->get();

        $taskStatuss1 = TaskStatus::where('name', ParamSchema::NOTCOMPLATE)->first();

        foreach ($todoTasks as $task) 
        {
            $settingCompany = SettingCompany::byCompany($task->assign->company_id)->where('menu','punishment')->get()->pluck('field_value','field_title');
            $task->task_status_id = $taskStatuss1->id;
            $task->status_submit = ParamSchema::PINALTY_NOT_PROGRESS;
            $task->point = $settingCompany['point_punishment_task_todo'] ?? 0;
            $task->save();

            $admin1 = User::with('role')
                    ->whereHas('role', fn ($query) => $query->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::DIRECTOR]))
                    ->where('company_id', $task->assign->company_id)
                    ->first();

            $messageType = 'reject';
            $this->message($task, $messageType, $admin1, 'Sistem ' . ucfirst($messageType) . ' Tugas ' . $task->name);
            
            $this->statusrecord($task, $taskStatuss1);
        }

        // =============================
        // 2. DOING / NOT COMPLETE → COMPLETE (lebih dari 30 hari)
        // =============================

        $cutoffDate = $now->subDays(30);

        $expiredTasks = DailyTask::whereHas('taskStatus', function ($query) 
            {
                $query->where('name', ParamSchema::DOING);
            })
            ->whereDate('end_date', '<', $cutoffDate)
            ->whereHas('assign')
            ->get();

        foreach ($expiredTasks as $task) 
        {

            $settingCompany = SettingCompany::byCompany($task->assign->company_id)->where('menu','punishment_task_doing')->get()->pluck('field_value','field_title');
             if($settingCompany['status_punihsment_task_doing'] == true)
             {
                 $task->task_status_id = TaskStatus::where('name', ParamSchema::NOTCOMPLATE)->firstOrFail()->id;
                 $task->status_submit = ParamSchema::PINALTY_NOT_PROGRESS;
                 $task->point = $settingCompany['point_punishment_task_doing'] ?? 0;
                 $task->save();
                
                 dd($task);
                 $admin2 = User::with('role')
                         ->whereHas('role', fn ($query) => $query->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::DIRECTOR]))
                         ->where('company_id', $task->assign->company_id)
                         ->first();
     
                 $messageType = 'reject';
                 $this->message($task, $messageType, $admin2, 'Sistem ' . ucfirst($messageType) . ' Tugas ' . $task->name);
                 
                 $this->statusrecord($task, $taskStatuss1);
             }
        }

        $this->info("Auto-completed ".count($todoTasks)." old DOING/NOT COMPLETE tasks with penalty ");
        $this->info("Auto-completed ".count($expiredTasks)." old DOING/NOT COMPLETE tasks with penalty ");


    }

    protected function message($task, $template, $user,$message, $filePath = null)
    {
        switch ($template) 
        {
            case 'create':
                $message = 
                '
                <div class="alert alert-primary d-flex align-items-center" role="alert" style="background-color: #cce5ff; border-color: #004085; color: #004085;">
                    <i class="fa fa-plus-circle mr-2" style="color: #004085;"></i>
                    <div>
                        '.$message.' 
                    </div>
                </div>
                ';
                break;
            case 'edit':
                $message = 
                '
                <div class="alert alert-warning d-flex align-items-center" role="alert" style="background-color: #fff3cd; border-color: #856404; color: #856404;">
                    <i class="fa fa-edit mr-2" style="color: #856404;"></i>
                    <div>
                        '.$message.' 
                    </div>
                </div>
                ';
                break;
            case 'report':
                $message = 
                '
                <div class="alert alert-primary d-flex align-items-center" role="alert" style="background-color: #cce5ff; border-color: #004085; color: #004085;">
                    <i class="fa fa-plus-circle mr-2" style="color: #004085;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            case 'approvement':
                $message = 
                '
                <div class="alert alert-success d-flex align-items-center" role="alert" style="background-color: #d4edda; border-color: #155724; color: #155724;">
                    <i class="fa fa-thumbs-up mr-2" style="color: #155724;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            case 'extend':
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #383d41; color: #383d41;">
                    <i class="fa fa-clock mr-2" style="color: #383d41;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;

            case 'reject':
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #ae2121; color: #ae2121;">
                    <i class="fa fa-times-circle mr-2" style="color: #ae2121;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            case 'trash':
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #ae2121; color: #ae2121;">
                    <i class="fa fa-trash mr-2" style="color: #ae2121;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            default:
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #383d41; color: #383d41;">
                    <i class="fa fa-comment mr-2" style="color: #383d41;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
        }

        $dailyTaskMessage = new DailyTaskMessage();
        $dailyTaskMessage->user_id = $user->id;
        $dailyTaskMessage->daily_task_id = $task->id;
        $dailyTaskMessage->message = $message;
        $dailyTaskMessage->file_path = $filePath ?? NULL;
        $dailyTaskMessage->save();

        return true;
    }   

    protected function statusrecord($task, $status)
    {
        DailyTaskStatusRecord::create([
            'daily_task_id' => $task->id,
            'task_status_id' => $status->id,
            'date' => now(),
        ]);

        return true;
    }
}
