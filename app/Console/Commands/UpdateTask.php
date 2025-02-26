<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyTask;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\DB;
use App\Models\DailyTaskStatusRecord;
use Illuminate\Support\Facades\Log;
use App\Models\DailyTaskMessage;
use Carbon\Carbon;
use App\Schemas\ParamSchema;

class UpdateTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:update 
                            {--email= : The email of the user} 
                            {--fromStatus= : The task status name} 
                            {--toStatus= : The task status name} 
                            {--subweek= : The number of weeks ago to consider for the task deadline}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update DailyTasks based on user email, task status, and date range (weeks ago)';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            // Mendapatkan parameter dari opsi
            $userEmail = $this->option('email');
            $taskStatusFrom = $this->option('fromStatus');
            $taskStatusTo = $this->option('toStatus');
            $weeksAgo = (int) $this->option('subweek');
            
            // Validasi input email dan status
            if (!$userEmail || !$taskStatusFrom || !$taskStatusTo || !$weeksAgo) {
                return $this->error("You must provide email, status, and subweek values.");
            }

            // Hitung tanggal batas berdasarkan minggu yang diberikan
            $endDate = Carbon::now()->subWeeks($weeksAgo);

            DB::transaction(function () use ($userEmail, $taskStatusFrom, $taskStatusTo, $endDate) {
                // Cari user berdasarkan email
                $user = \App\Models\User::where('email', $userEmail)->first();
                if (!$user) {
                    return $this->error("User with email {$userEmail} not found.");
                }

                // Cari status task berdasarkan nama
                $taskStatus = TaskStatus::where('name', $taskStatusFrom)->first();
                if (!$taskStatus) {
                    return $this->error("Task status with name {$taskStatusFrom} not found.");
                }

                $taskStatusTo = TaskStatus::where('name', $taskStatusTo)->first();
                if (!$taskStatusTo) {
                    return $this->error("Task status with name {$taskStatusTo} not found.");
                }

                // Ambil semua DailyTask dengan status 'inReview' yang end_datenya sampai tanggal yang ditentukan
                $tasks = DailyTask::whereHas('taskStatus', function ($query) use ($taskStatus, $user) 
                {
                    $query->where('name', $taskStatus->name)->where('assignment_user_id', $user->id);
                })
                ->whereDate('end_date', '<=', $endDate)
                ->get();

                foreach ($tasks as $task) {

                    if($taskStatusTo->name == ParamSchema::COMPLATE)
                    {
                        $task->point = 1; // Set point menjadi 1
                    }
                    $task->task_status_id = $taskStatusTo->id;
                    $task->save();

                    // Tambahkan record status
                    $this->statusrecord($task, $taskStatusTo);
                    $this->message($task->user_id, $task->id, 'info', 'Tugas telah diubah oleh sistem');
                }
            });

            return $this->info("Points updated and tasks marked as complete successfully!");
        } catch (\Exception $e) {
            // Jika ada error, lakukan rollback otomatis
            DB::rollBack();
            Log::error($e->getMessage());

            return $this->error("An error occurred while updating points and task status.");
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
        switch ($template) {
            case 'info':
                $message = '
                <div class="alert alert-info d-flex align-items-center p-3" role="alert" style="background-color: #e9f7fe; border-left: 4px solid #17a2b8; color: #0c5460; border-radius: 5px;">
                    <i class="fas fa-info-circle fa-lg mr-3" style="color: #17a2b8;"></i>
                    <div>
                        '.$message.' 
                    </div>
                </div>
                ';
                break;
            default:
                $message = '
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
