<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyTask;
use App\Models\TaskStatus;
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
                $this->createNewTask($task, true);
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

        // Tambahkan log message
        $this->info('Created new task: ' . $newTask->name);
    }

}
