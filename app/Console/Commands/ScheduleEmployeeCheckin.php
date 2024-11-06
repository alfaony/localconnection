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
        ->where('is_checkin', true)
        // ->whereDoesntHave('role', function ($query) {
        //     $query->whereIn('name', [RoleSchema::ROOT, RoleSchema::DIRECTOR, RoleSchema::SECURITY, RoleSchema::OB, RoleSchema::BM]); // Bukan 'Root' atau 'Admin'
        // })
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
        if (!in_array($user->email, $onLeaveEmails)) {
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
                'checkin_time' => Carbon::now(),
                'timeout_time' => Carbon::now(),
            ];
            $local = $this->saveLocal($user, $time, true);
        }

    }

    private function saveLocal($user, $time, $isOnLeave = false)
    {
        $firstDivision = $this->findFirstDivision($user);
        
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
            if(!$user->manual_checkin) 
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
                    'scheduled_time' => $time['checkin_time'],
                    'scheduled_timeout' => null,
                    'is_active' => true,
                    'is_completed' => false,
                ]);
            }
        }

    }

    private function findFirstDivision($user)
    {
        foreach ($user->divisions as $division) 
        {
            if($division->manual_checkin)
            {
                return $division;
            }
        }


        return $user->divisions->first();

    }    
    // Improvement that generates random check-in times with checking overlaps or not
    // private function generateRandomCheckinTimes()
    // {
    //     $times = [];
    //     $duration = config('services.checking_setting.duration_minutes'); // Get the duration from config
    //     $bufferMinutes = 30; // Buffer waktu minimal antar check-in
    //     $maxAttempts = 50; // Batas percobaan untuk menghindari loop tak terbatas

    //     while (count($times) < config('services.checking_setting.times') && $maxAttempts > 0) 
    //     {
    //         // Generate random check-in time
    //         $time = Carbon::today()->addHours(rand(8, 16))->addMinutes(rand(0, 59));

    //         // Lewati jam makan siang jika diperlukan
    //         if ($time->hour === 12) {
    //             continue; // Skip lunch hour (if needed)
    //         }

    //         // Hitung waktu timeout
    //         $timeout = $time->copy()->addMinutes($duration);

    //         // Cek apakah ada konflik atau jarak kurang dari buffer waktu
    //         $conflict = false;
    //         foreach ($times as $scheduled) {
    //             $existingStart = Carbon::parse($scheduled['checkin_time']);
    //             $existingEnd = Carbon::parse($scheduled['timeout_time']);

    //             // Cek tumpang tindih dan jarak minimal 10 menit
    //             if (
    //                 $time->between($existingStart->copy()->subMinutes($bufferMinutes), $existingEnd->copy()->addMinutes($bufferMinutes)) || 
    //                 $timeout->between($existingStart->copy()->subMinutes($bufferMinutes), $existingEnd->copy()->addMinutes($bufferMinutes))
    //             ) {
    //                 $conflict = true;
    //                 break; // Ada konflik, keluar dari loop
    //             }
    //         }

    //         // Jika tidak ada konflik, tambahkan ke daftar waktu
    //         if (!$conflict) {
    //             $times[] = [
    //                 'checkin_time' => $time->format('Y-m-d H:i:s'),
    //                 'timeout_time' => $timeout->format('Y-m-d H:i:s')
    //             ];
    //         } else {
    //             $maxAttempts--; // Kurangi batas percobaan hanya jika ada konflik
    //         }
    //     }

    //     return $times;
    // }
    private function generateRandomCheckinTimes($start_time = '08:00', $end_time = '17:00', $rest_time = '12:00')
    {
        $times = [];
        $duration = config('services.checking_setting.duration_minutes'); // Durasi dari config
        $bufferMinutes = 30; // Buffer waktu minimal antar check-in
        $maxAttempts = 100; // Batas percobaan untuk menghindari loop tak terbatas
        $targetCheckins = 10; // Jumlah check-in yang diinginkan

        // Konversi waktu mulai, akhir, dan istirahat ke objek Carbon dengan nilai default
        $start = Carbon::createFromTimeString($start_time);
        $end = Carbon::createFromTimeString($end_time);
        $restStart = Carbon::createFromTimeString($rest_time);
        $restEnd = $restStart->copy()->addHour(); // 1 jam waktu istirahat
        
        while (count($times) < $targetCheckins && $maxAttempts > 0) 
        {
            // Generate random check-in time within the working hours
            $time = Carbon::today()->addHours(rand($start->hour, $end->hour - 1))->addMinutes(rand(0, 59));

            // Lewati jam istirahat jika diperlukan
            if ($time->between($restStart, $restEnd)) {
                continue; // Skip rest time
            }

            // Hitung waktu timeout
            $timeout = $time->copy()->addMinutes($duration);

            // Pastikan timeout berada dalam jam kerja
            if ($timeout->greaterThan($end)) {
                continue; // Skip if timeout is outside of working hours
            }

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

        // Pastikan jumlah check-in tepat 10, jika tidak, beri pesan error atau log.
        if (count($times) < $targetCheckins) {
            throw new \Exception("Tidak bisa menghasilkan 10 waktu check-in unik dalam batas percobaan yang ditentukan.");
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
