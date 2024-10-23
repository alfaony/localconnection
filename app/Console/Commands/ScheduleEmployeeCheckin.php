<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\NationalHoliday;
use App\Models\EmployeeChecking;
use Carbon\Carbon;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;
use App\Services\DayoffService;

use App\Schemas\RoleSchema;
class ScheduleEmployeeCheckin extends Command
{
    protected $signature = 'schedule:employee-checkin';
    protected $description = 'Schedule employee check-in excluding weekends, national holidays, and user leave days';
    protected $dayoffService;

    public function __construct(DayoffService $dayoffService)
    {
        parent::__construct();

        $this->dayoffService = $dayoffService;
    }
    public function handle()
    {
        $today = Carbon::today();
        
        // 1. Cek jika hari ini adalah hari libur nasional
        if ($this->isNationalHoliday($today)) {
            $this->info("Hari ini adalah hari libur nasional. Tidak ada jadwal check-in.");
            return;
        }
        
        // 2. Cek jika hari ini adalah akhir pekan (Sabtu/Minggu)
        if ($today->isWeekend()) {
            $this->info("Hari ini adalah akhir pekan. Tidak ada jadwal check-in.");
            return;
        }
        
        // 3. Array email pengguna yang sedang cuti
        $onLeaveEmails = $this->listDayoffEmployee();
        // Ambil semua user yang tidak cuti
        $users = User::query()
        ->when(!empty($onLeaveEmails), function ($query) use ($onLeaveEmails) {
            $query->whereNotIn('email', $onLeaveEmails); // Tidak sedang cuti jika ada email yang sedang cuti
        })
        ->whereHas('divisions') // Memiliki divisi
        ->whereDoesntHave('role', function ($query) {
            $query->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN]); // Bukan 'Root' atau 'Admin'
        })
        ->get();

        foreach ($users as $user) {
            $this->scheduleCheckinForUser($user);
        }
        
        $this->info("Jadwal check-in selesai dibuat dan disimpan di Firebase serta database lokal.");
    }

    private function isNationalHoliday($date)
    {
        return NationalHoliday::where('date', $date->toDateString())->exists();
    }

    // Comment
    private function scheduleCheckinForUser($user, $firebase = null)
    {
        $checkinTimes = $this->generateRandomCheckinTimes();

        foreach ($checkinTimes as $time) {
            // dd($it)
            // Simpan di database lokal
            $local = $this->saveLocal($user, $time);

            // Coba simpan di Firebase jika tersedia
            // if ($firebase) {
            //     try {
            //         $firebase->getReference('employee_checkins/'.$user->id.'/'.$local->id)->set([
            //             'local_id' => $local->id,
            //             'scheduled_time' => $time,
            //             'is_active' => true,
            //             'created_at' => now()->toDateTimeString(),
            //         ]);
            //     } catch (FirebaseException $e) {
            //         $this->error("Gagal menyimpan di Firebase untuk user {$user->id}. Data tetap tersimpan di lokal.");
            //     }
            // }
        }
    }

    private function saveLocal($user, $time)
    {
        $firstDivision = $user->first_division;
        
        if(!$firstDivision->manual_checkin) 
        {
            return EmployeeChecking::create([
                'user_id' => $user->id,
                'division_id' => $firstDivision->id,
                'scheduled_time' => $time['checkin_time'],
                'scheduled_timeout' => $time['timeout_time'],
                'is_active' => true,
                'is_completed' => false,
            ]);
        }else
        {
            return EmployeeChecking::create([
                'user_id' => $user->id,
                'division_id' => $firstDivision->id,
                'scheduled_time' => null,
                'scheduled_timeout' => null,
                'is_active' => true,
                'is_completed' => false,
            ]);
        }
    }

    private function generateRandomCheckinTimes()
    {
        $times = [];
        $duration = config('services.checking_setting.duration_minutes'); // Get the duration from config

        while (count($times) < config('services.checking_setting.times')) 
        {
            $time = Carbon::today()->addHours(rand(8, 16))->addMinutes(rand(0, 59));

            if ($time->hour === 12) 
            {
                continue; // Skip lunch hour (if needed)
            }

            // Check-in time and timeout (check-in time + duration)
            $timeout = $time->copy()->addMinutes($duration);

            $times[] = [
                'checkin_time' => $time->format('Y-m-d H:i:s'),
                'timeout_time' => $timeout->format('Y-m-d H:i:s')
            ];
        }

        return $times;
    }

    protected function listDayoffEmployee()
    {
        $list = [];
        $dayoffList = $this->dayoffService->getCutiListBOS();
        if(count($dayoffList) > 0)
        {
            foreach ($dayoffList as $value) 
            {
                $list[] = $value['email_staff'];
            }
        }

        return $list;
    }

}
