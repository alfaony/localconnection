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
        ->whereHas('divisions') // Memiliki divisi
        ->whereDoesntHave('role', function ($query) {
            $query->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN]); // Bukan 'Root' atau 'Admin'
        })
        ->get();

        foreach ($users as $user) {
            $this->scheduleCheckinForUser($user, $onLeaveEmails);
        }
        
        $this->info("Jadwal check-in selesai dibuat dan disimpan di Firebase serta database lokal.");
    }

    private function isNationalHoliday($date)
    {
        return NationalHoliday::where('date', $date->toDateString())->exists();
    }

    // Comment
    private function scheduleCheckinForUser($user, $onLeaveEmails = [])
    {
        // Cek jika user sedang cuti
        if ($onLeaveEmails && !in_array($user->email, $onLeaveEmails)) {
            $checkinTimes = $this->generateRandomCheckinTimes();
            foreach ($checkinTimes as $time) 
            {
                // Simpan di database lokal
                $local = $this->saveLocal($user, $time);
            }
        }else
        {
            $time = 
            [
                'checkin_time' => NULL,
                'timeout_time' => NULL,
            ];
            $local = $this->saveLocal($user, $time, true);
        }

    }

    private function saveLocal($user, $time, $isOnLeave = false)
    {
        $firstDivision = $user->first_division;
        
        if($isOnLeave)
        {
            return EmployeeChecking::create([
                'user_id' => $user->id,
                'division_id' => $firstDivision->id,
                'scheduled_time' => $time['checkin_time'],
                'scheduled_timeout' => $time['timeout_time'],
                'is_dayoff' => true,
                'is_active' => false,
                'is_completed' => false,
            ]);
        }else
        {
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

    }

    // private function generateRandomCheckinTimes()
    // {
    //     $times = [];
    //     $duration = config('services.checking_setting.duration_minutes'); // Get the duration from config

    //     while (count($times) < config('services.checking_setting.times')) 
    //     {
    //         $time = Carbon::today()->addHours(rand(8, 16))->addMinutes(rand(0, 59));

    //         if ($time->hour === 12) 
    //         {
    //             continue; // Skip lunch hour (if needed)
    //         }

    //         // Check-in time and timeout (check-in time + duration)
    //         $timeout = $time->copy()->addMinutes($duration);

    //         $times[] = [
    //             'checkin_time' => $time->format('Y-m-d H:i:s'),
    //             'timeout_time' => $timeout->format('Y-m-d H:i:s')
    //         ];
    //     }

    //     return $times;
    // }
    
    // Improvement that generates random check-in times with checking overlaps or not
    private function generateRandomCheckinTimes()
    {
        $times = [];
        $duration = config('services.checking_setting.duration_minutes'); // Get the duration from config
        $bufferMinutes = 10; // Buffer waktu minimal antar check-in
        $maxAttempts = 50; // Batas percobaan untuk menghindari loop tak terbatas

        while (count($times) < config('services.checking_setting.times') && $maxAttempts > 0) 
        {
            // Generate random check-in time
            $time = Carbon::today()->addHours(rand(8, 16))->addMinutes(rand(0, 59));

            // Lewati jam makan siang jika diperlukan
            if ($time->hour === 12) {
                continue; // Skip lunch hour (if needed)
            }

            // Hitung waktu timeout
            $timeout = $time->copy()->addMinutes($duration);

            // Cek apakah ada konflik atau jarak kurang dari buffer waktu
            $conflict = false;
            foreach ($times as $scheduled) {
                $existingStart = Carbon::parse($scheduled['checkin_time']);
                $existingEnd = Carbon::parse($scheduled['timeout_time']);

                // Cek tumpang tindih dan jarak minimal 10 menit
                if (
                    $time->between($existingStart->copy()->subMinutes($bufferMinutes), $existingEnd->copy()->addMinutes($bufferMinutes)) || 
                    $timeout->between($existingStart->copy()->subMinutes($bufferMinutes), $existingEnd->copy()->addMinutes($bufferMinutes))
                ) {
                    $conflict = true;
                    break; // Ada konflik, keluar dari loop
                }
            }

            // Jika tidak ada konflik, tambahkan ke daftar waktu
            if (!$conflict) {
                $times[] = [
                    'checkin_time' => $time->format('Y-m-d H:i:s'),
                    'timeout_time' => $timeout->format('Y-m-d H:i:s')
                ];
            } else {
                $maxAttempts--; // Kurangi batas percobaan hanya jika ada konflik
            }
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
