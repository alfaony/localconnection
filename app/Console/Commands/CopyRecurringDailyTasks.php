<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyTask;
use App\Models\TaskStatus;
use App\Models\DailyTaskMessage;
use App\Models\DailyTaskStatusRecord;
use App\Schemas\ParamSchema;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CopyRecurringDailyTasks extends Command
{
    protected $signature = 'tasks:process-recurring';

    protected $description = 'Process recurring tasks and create copies for the current week';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Dapatkan hari ini dalam format 'monday', 'tuesday', dll.
        $today = strtolower(Carbon::now()->format('l'));

        // Case 1: Proses tugas yang recurring di minggu yang sama tapi kurang dari hari ini
        $this->processTasksForSameWeekBeforeToday($today);

        // Case 2: Proses tugas yang recurring 1 minggu dari hari ini
        $this->processTasksForNextWeek($today);

        $this->processTasksForDifferentDayNextWeek($today);

        $this->info('Recurring tasks processed successfully.');
    }

    protected function processTasksForSameWeekBeforeToday($today)
    {
        // Cari semua tugas yang recurring dan harinya ada di recurring_days dan sudah melewati hari ini
        $today = strtolower(Carbon::now()->format('l'));

        $tasks = DailyTask::whereHas('type', function ($query) {
                $query->where('name', ParamSchema::RECURRING);
            })
            ->where('recurring_days', 'LIKE', "%$today%")
            ->whereBetween('start_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfDay()])
            ->get();

        foreach ($tasks as $task) {
            $existingTask = DailyTask::where('slug', Str::slug($task->name . '-' . Carbon::now()->format('dmY')))->first();
            if (!$existingTask) {
                $this->createNewTask($task);
            }
        }
    }

    protected function processTasksForNextWeek($today)
    {
        // Cari semua tugas yang recurring yang di-set untuk hari ini minggu depan
        $tasks = DailyTask::whereHas('type', function ($query) {
            $query->where('name', ParamSchema::RECURRING);
        })->whereDate('start_date', '=', Carbon::now()->subWeek())->get();

        foreach ($tasks as $task) {
            // Cek apakah tugas untuk minggu depan sudah ada
            $existingTask = DailyTask::where('slug', Str::slug($task->name . '-' . Carbon::now()->format('dmY')))->first();

            if (!$existingTask) {
                $this->createNewTask($task, true);
            }
        }
    }

    protected function processTasksForDifferentDayNextWeek($today)
    {

        // Cari semua tugas yang recurring yang di-set untuk hari ini minggu depan
        $tasks = DailyTask::whereHas('type', function ($query) {
            $query->where('name', ParamSchema::RECURRING);
        })
        ->where('recurring_days', 'LIKE', "%$today%")
        ->whereBetween('start_date', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
        ->get();

        foreach ($tasks as $task) {
            // Cek apakah tugas untuk minggu depan sudah ada
            $existingTask = DailyTask::where('slug', Str::slug($task->name . '-' . Carbon::now()->format('dmY')))->first();

            if (!$existingTask) {
                $this->createNewTask($task, false);
            }
        }
    }

    protected function createNewTask($task, $nextWeek = false)
    {
        // Salin tugas asli
        $newTask = $task->replicate();

        // Hitung durasi antara start_date dan end_date asli
        $duration = Carbon::parse($task->end_date)->diffInDays(Carbon::parse($task->start_date));
        $todo = TaskStatus::where('name',ParamSchema::TODO)->firstOrFail();

        // Tentukan tanggal baru
        if ($nextWeek) {
            $newTask->start_date = Carbon::parse($task->start_date)->addWeek();
            $newTask->end_date = Carbon::parse($task->end_date)->addWeek();
        } else {
            $newTask->start_date = Carbon::now(); // Gunakan tanggal hari ini
            $newTask->end_date = Carbon::now()->addDays($duration); // Tambahkan durasi yang sama ke start_date
        }

        // Generate slug baru
        $newTask->slug = Str::slug($task->name . '-' . Carbon::now()->format('dmY'));
        $newTask->task_status_id = $todo->id;
        $newTask->report_note = NULL;
        $newTask->submit = NULL;
        $newTask->status_submit = NULL;
        $newTask->approved = FALSE;
        $newTask->point = 0; // Assuming default value is 0
        // Simpan tugas baru
        $newTask->save();
        
        $keyResults = $task->keyResults;
        foreach ($keyResults as $keyResult) 
        {
            $newTask->keyResults()->attach($keyResult->id);
        }

        $this->message($newTask,'create',' System Membuat Tugas '.$newTask->name);
        $this->statusrecord($newTask, $todo);
        // Tambahkan log message
        $this->info('Created new task: ' . $newTask->name);
    }

    protected function message($dailyTask, $template, $message, $filePath = null)
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
        $dailyTaskMessage->user_id = $dailyTask->user_id;
        $dailyTaskMessage->daily_task_id = $dailyTask->id;
        $dailyTaskMessage->message = $message;
        $dailyTaskMessage->file_path = $filePath ?? NULL;
        $dailyTaskMessage->save();

        return true;
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

}
