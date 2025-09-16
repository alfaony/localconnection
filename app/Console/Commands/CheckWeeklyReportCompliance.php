<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\TaskStatus;
use App\Models\Division;
use App\Models\DailyTask;
use App\Models\DailyTaskStatusRecord;
use App\Models\DailyTaskMessage;
use App\Models\WeeklyReport;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\SettingCompany;
use App\Models\PunishmentUser;

use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;
class CheckWeeklyReportCompliance extends Command
{
    protected $signature = 'weekly:check-compliance';
    protected $description = 'Cek user yang tidak mengisi weekly report sesuai divisinya';

    public function handle()
    {
        try {
            $now = Carbon::now();
            $lastWeek = $now->copy()->subWeek();
            $week = $lastWeek->isoWeek();
            $year = $lastWeek->year;
    
            $results = [];
    
            // Ambil semua user yang rolenya is_mandatory_report == true
            $users = User::with(['role', 'divisions'])
            ->get()
            ->filter(fn ($user) => $user->role && $user->role->is_mandatory_report);
            

            foreach ($users as $user) 
            {
                foreach ($user->divisions as $division) {
                    // Hanya yang weekly_report_required = true
                    if (!($division->pivot->weekly_report_required ?? false)) {
                        continue;
                    }
    
                    // Cek apakah user sudah mengisi untuk minggu lalu
                    $exists = WeeklyReport::where('division_id', $division->id)
                        ->where('week', $week)
                        ->where('year', $year)
                        ->exists();
    
                    $naming = "Pengurangan Point !! , Tidak Melakukan Weekly Report divisi ". $division->name." Pada Week ".$week;
    
                    $taskStatuss = TaskStatus::where('name', ParamSchema::COMPLATE)->first();
                    $admin = User::with('role')
                        ->whereHas('role', fn ($query) => $query->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::DIRECTOR]))
                        ->where('company_id', $user->company_id)
                        ->first();
    
                    if (!$exists) 
                    {
                        $settingCompany = SettingCompany::byCompany($user->company_id)->where('menu','punishment')->get()->pluck('field_value','field_title');

                        $dailyTask = new DailyTask();
                        $dailyTask->user_id = $admin ? $admin->id : $user->id;
                        $dailyTask->task_status_id = $taskStatuss->id;
                        $dailyTask->start_date = Carbon::now();
                        $dailyTask->end_date = Carbon::now();
                        $dailyTask->submit = Carbon::now();
                        $dailyTask->submit = Carbon::now();
                        $dailyTask->status_submit = ParamSchema::ONTIME;
                        $dailyTask->assignment_user_id = $user->id;
                        $dailyTask->name = $naming;
                        $dailyTask->description = "<p>".$naming."</p>";
                        $dailyTask->report_note = "<p>".$naming."</p>";
                        $dailyTask->point = $settingCompany['point_punishment_weekly_report'] ?? 0;
                        $dailyTask->save();
                        

                        $punishment = new PunishmentUser();
                        $punishment->user_id = $user->id;
                        $punishment->dailytask_id = $dailyTask->id;
                        $punishment->point = $settingCompany['point_punishment_weekly_report'] ?? 0;
                        $punishment->save();

                        $messageType = 'approvement';
                        $this->message($dailyTask, $messageType, 'Sistem ' . ucfirst($messageType) . ' Tugas ' . $dailyTask->name);
                        
                        $this->statusrecord($dailyTask, $taskStatuss);
                        
                        $this->info("User ".$user->name." tidak mengisi weekly report di divisi ".$division->name." untuk week ".$week.", maka akan di berikan task dengan nama ".$naming);
                    }
                }
            }

            $this->info('Weekly compliance check completed.');
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);

            $this->info('Weekly compliance check completed.');
        }
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