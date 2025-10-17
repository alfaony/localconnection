<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\OfficeAttendance;
use App\Models\TaskStatus;
use App\Models\Division;
use App\Models\DailyTask;
use App\Models\DailyTaskStatusRecord;
use App\Models\DailyTaskMessage;
use App\Models\WeeklyReport;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\PunishmentUser;
use App\Models\SettingCompany;
use App\Models\NationalHoliday;

use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;
use App\Helpers\InboxHelper;

class validityUserOfCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'validity:userOfCompany {--id= : The ID of the employee checking} {--type= : The type of the employee checking}';

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
        $id = $this->option('id');
        $type = $this->option('type');

        $setting = SettingCompany::byCompany($id)->where('menu','punishment_role')->get()->pluck('field_value','field_title');
        try {
            if (isset($setting['range_start_date']) && isset($setting['range_end_date']) && $type == "wfh") 
            {
                $startDay = (int) $setting['range_start_date'];
                $endDay = (int) $setting['range_end_date'];
                $today = Carbon::today();
    
                $startDate = Carbon::now()->subMonthNoOverflow()->startOfMonth()->setDay($startDay);
                $endDate = Carbon::now()->startOfMonth()->setDay($endDay);
    
                if ($endDay > $endDate->daysInMonth) {
                    $endDate->day = $endDate->daysInMonth;
                }
    
                $users = User::where('is_checkin', true)->withCheckinCountsJob($id, null, $startDate, $endDate)
                ->withCount(['dailyTaskAssigns as overdue_count' => function ($query) use ($today) {
                    $query->whereHas('taskStatus', function ($q) {
                        $q->whereIn('name', [
                            ParamSchema::BACKLOG,
                            ParamSchema::DOING,
                            ParamSchema::NOTCOMPLATE,
                            ParamSchema::TODO,
                        ]);
                    })->whereDate('end_date', '<', $today);
                }])
                ->get();
                $users = $users->map(function ($user) {
                    $user->point_percentage = $user->point_percentage;
                    return $user;
                })
                // ->filter(function ($user) 
                // {
                //     return $user->point_percentage < 70;
                // })
                ;
                foreach ($users as $user) 
                {
                    if($user->point_percentage < $setting['presence_checkin'])
                    {
                        // dd($user);
                        $this->addPoint($user, 'Kehadiran kurang dari '.$setting['presence_checkin'].'% perhitungan pada tanggal '.$startDate->format('d-m-Y') .' sampai '.$endDate->format('d-m-Y'), $setting['punishment_point_wfh']);
                    }else if($user->overdue_count > $setting['overdue_task'])
                    {
                        $this->addPoint($user, 'Jumlah task overdue melebihi batas '.$setting['overdue_task'], $setting['punishment_point_wfh']);
                    }
                }

                $this->info('Punishment WFH check completed.');
            }
            if ($type == 'wfo') 
            {
                $today = Carbon::today();
                if ($this->isNationalHoliday($today)) {
                    $this->info("Hari ini adalah hari libur nasional. Tidak ada jadwal check-in.");
                    return;
                }

                // if ($today->isWeekend()) 
                // {
                //     $this->info("Hari ini adalah akhir pekan. Tidak ada jadwal check-in.");
                //     return;
                // }

                $entryTime = Carbon::createFromFormat('H:i', $setting['entry_time']);
                $toleranceMinutes = (int) $setting['tolerance'];
                $checkinTarget = (int) $setting['checkin_onday'];

                $users = User::where('company_id', $id)->where('wfo_check_in', true)->get();

                foreach ($users as $user) {
                    // Ambil semua attendance hari ini
                    if(!$user->isDayoff() && $user->shouldWorkToday())
                    {
                        $attendances = OfficeAttendance::where('user_id', $user->id)
                            ->whereDate('created_at', Carbon::today())
                            ->orderBy('created_at', 'asc')
                            ->get();
    
                        $totalCheckin = $attendances->count();
                        $firstCheckin = $attendances->first();
                        $terlambat = false;
                        $message = null;
                        
                        $checkinTarget = $firstCheckin->user ? ($firstCheckin->user->wfoRules && $firstCheckin->user->wfoRules->times_checkin_in_day ? $firstCheckin->user->wfoRules->times_checkin_in_day : $checkinTarget) : $checkinTarget;
                        $point = $firstCheckin->user ? ($firstCheckin->user->wfoRules && $firstCheckin->user->wfoRules->point_checkin_in_day ? $firstCheckin->user->wfoRules->point_checkin_in_day : $setting['punishment_point_wfo']) : $setting['punishment_point_wfo'];


                        if ($firstCheckin) 
                        {
                            $actualCheckin = Carbon::parse($firstCheckin->time);
                            $graceTime = $firstCheckin->user ? ($firstCheckin->user->wfoRules && $firstCheckin->user->wfoRules->entry_time_checkin->addMinutes($toleranceMinutes) ? $firstCheckin->user->wfoRules->entry_time_checkin->addMinutes($toleranceMinutes) : $entryTime->copy()->addMinutes($toleranceMinutes)) : $entryTime->copy()->addMinutes($toleranceMinutes);
                            $terlambat = $actualCheckin->gt($graceTime);

                            $message = $terlambat ? "Terlambat " . $actualCheckin->diffInMinutes($graceTime) . " menit dari jam " . $graceTime->format('H:i') : null;
                        }

                        if (($totalCheckin < $checkinTarget || $terlambat) && $point) 
                        {
                            if(!isset($message) && $totalCheckin < $checkinTarget)
                            {
                                $message = "Belum memenuhi target check-in check-in per hari ".$totalCheckin." dari ".$checkinTarget;
                            }
                            // Tambahkan user ke dalam hasil atau lakukan aksi
                            $this->addPoint($user, $message, $point);
                        }
                    }
                }

                $this->info('Punishment WFO check completed.');
            }

        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            $this->error($th->getMessage());
            Log::error("Error storing file: " . $th->getMessage());
        }
    }

    public function addPoint($user, $naming, $point)
    {
        $taskStatuss = TaskStatus::where('name', ParamSchema::COMPLATE)->first();
        $admin = User::with('role')
            ->whereHas('role', fn ($query) => $query->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::DIRECTOR]))
            ->where('company_id', $user->company_id)
            ->first();

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
        $dailyTask->point = $point;
        $dailyTask->save();

        // dd($dailyTask);

        $messageType = 'approvement';
        $this->message($dailyTask, $messageType, 'Sistem ' . ucfirst($messageType) . ' Tugas ' . $dailyTask->name);
        
        $this->statusrecord($dailyTask, $taskStatuss);

        $punishment = new PunishmentUser();
        $punishment->user_id = $user->id;
        $punishment->dailytask_id = $dailyTask->id;
        $punishment->point = $point;
        $punishment->save();

        $url = route('dailytask.show', $dailyTask->slug);
        $this->sentInbox($admin->id, $user->id, $naming, $url);
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

    private function isNationalHoliday($date)
    {
        return NationalHoliday::where('date', $date->toDateString())->exists();
    }

    public function sentInbox($from, $to,$message,$directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $to, 
            $from,
            $message, 
            $directUrl
        );

        return;
    }
}
