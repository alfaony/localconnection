<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyTask;
use App\Models\TaskStatus;
use App\Schemas\ParamSchema;
use Illuminate\Support\Facades\DB;
use App\Models\DailyTaskStatusRecord;
use App\Models\DailyTaskMessage;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class UpdateDailyTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all inReview DailyTasks to complete up to a specified date';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            DB::transaction(function () {
                // Ambil semua DailyTask dengan status 'inReview' dan yang end_datenya sampai 9 September
                $tasks = DailyTask::whereHas('taskStatus', function ($query) {
                    $query->where('name', ParamSchema::INREVIEW);
                })
                ->whereDate('end_date', '<=', Carbon::create(2025, 2, 5))
                ->get();
    
                foreach ($tasks as $task) {
                    $task->point = 1; // Set point menjadi 1
                    $task->task_status_id = TaskStatus::where('name', ParamSchema::COMPLATE)->first()->id; // Set status ke complete
                    $task->save();

                    // Tambahkan record status
                    $this->statusrecord($task, TaskStatus::where('name', ParamSchema::COMPLATE)->first());
                    $this->message($task->user_id, $task->id, 'info', 'Tugas telah diselesaikan oleh sistem');
                }
            });
            
            return $this->info("Points updated and tasks marked as complete successfully!");
        } catch (\Exception $e) {
            // Jika ada error, lakukan rollback otomatis
            DB::rollBack();
            Log::error($e->getMessage());

            return $this->error("An error occured while updating points and tasks status");
        }
    }

    protected function statusrecord($dailyTask, $status)
    {
        DailyTaskStatusRecord::create([
            'daily_task_id' => $dailyTask->id,
            'task_status_id' => $status->id,
            'date' => now(),
        ]);

        return true;
    }

    protected function message($userId, $dailyTaskId, $template, $message, $filePath = null)
    {
        switch ($template) 
        {
            case 'info':
                $message = 
                '
                <div class="alert alert-info d-flex align-items-center p-3" role="alert" style="background-color: #e9f7fe; border-left: 4px solid #17a2b8; color: #0c5460; border-radius: 5px;">
                    <i class="fas fa-info-circle fa-lg mr-3" style="color: #17a2b8;"></i>
                    <div>
                        '.$message.' 
                    </div>
                </div>
                ';
                break;
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
        $dailyTaskMessage->user_id = $userId;
        $dailyTaskMessage->daily_task_id = $dailyTaskId;
        $dailyTaskMessage->message = $message;
        $dailyTaskMessage->file_path = $filePath ?? NULL;
        $dailyTaskMessage->save();

        return true;
    } 
}

