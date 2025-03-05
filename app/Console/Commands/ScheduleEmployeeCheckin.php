<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\NationalHoliday;
use App\Models\EmployeeChecking;
use Carbon\Carbon;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;
use App\Services\DayoffService;
use Illuminate\Support\Str;

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
        $thisDay = Str::lower(Carbon::today()->format('l')); // Mendapatkan nama hari saat ini (misalnya, "Monday")
        
        // 1. Cek jika hari ini adalah hari libur nasional
        if ($this->isNationalHoliday($today)) {
            $this->info("Hari ini adalah hari libur nasional. Tidak ada jadwal check-in.");
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
        

        foreach ($users as $user) 
        {
            $customRestTime = $user->custom_rest_times[$thisDay] ?? null;
            if ($today->isWeekend() && isset($customRestTime) && !$customRestTime['start'] && !$customRestTime['end'])
            {
                $this->info("Hari ini adalah akhir pekan. Tidak ada jadwal check-in.");
            }else
            {
                $this->scheduleCheckinForUser($user, $onLeaveEmails);
            }
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
        $dayOff = $onLeaveEmails['cuti'];
        $sickLeave = $onLeaveEmails['sakit'];

        // $sickLeave[] = strtolower("fatahPM2@gmail.com");

        if (in_array(strtolower($user->email), $dayOff)) 
        {
            $time = 
            [
                'checkin_time' => Carbon::now(),
                'timeout_time' => Carbon::now(),
            ];
            $local = $this->saveLocal($user, $time, "dayoff");
        }
        elseif(in_array(strtolower($user->email), $sickLeave))
        {
            $time = 
            [
                'checkin_time' => Carbon::now(),
                'timeout_time' => Carbon::now(),
            ];
            $local = $this->saveLocal($user, $time, "sick");
        }
        else
        {
            // $checkinTimes = $this->generateRandomCheckinTimes($user->start_time, $user->end_time, $user->rest_time);
            $checkinTimes = $this->generateRandomCheckinTimesUser($user);
            foreach ($checkinTimes as $time) 
            {
                // Simpan di database lokal
                $local = $this->saveLocal($user, $time);
            }
        }

    }

    private function saveLocal($user, $time, $statusLeave = null)
    {
        $firstDivision = $this->findFirstDivision($user);
        
        if($statusLeave == "dayoff")
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
        }
        else if($statusLeave == "sick")
        {
            return EmployeeChecking::create([
                'user_id' => $user->id,
                'division_id' => $firstDivision->id,
                'scheduled_time' => $time['checkin_time'],
                'scheduled_timeout' => $time['timeout_time'],
                'is_dayoff' => false,
                'is_active' => false,
                'is_completed' => false,
                'is_permission' => true,
            ]); 
        }
        else
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

    private function generateRandomCheckinTimes($start_time, $end_time, $rest_time)
    {
        $targetCheckins = 10; // Jumlah check-in yang diinginkan
        $duration = config('services.checking_setting.duration_minutes'); // Durasi dari konfigurasi
        $bufferMinutes = 30; // Waktu buffer minimal antar check-in
        $maxAttempts = 100; // Batas percobaan untuk menghindari loop tak terbatas

        // Konversi waktu mulai, akhir, dan istirahat ke objek Carbon dengan nilai default
        $start = Carbon::createFromTimeString($start_time ?? '08:00');
        $end = Carbon::createFromTimeString($end_time ?? '17:00');
        $restStart = Carbon::createFromTimeString($rest_time ?? '12:00');
        $restEnd = $restStart->copy()->addHour(); // Istirahat makan siang selama 1 jam

        do {
            $times = []; // Reset daftar waktu check-in
            $attempts = $maxAttempts;

            while (count($times) < $targetCheckins && $attempts > 0) 
            {
                // Menghasilkan jam dan menit acak yang berada dalam jam kerja yang valid
                $hour = rand($start->hour, $end->hour - 1); // Menghindari jam 17 sebagai waktu mulai
                $minute = rand(0, 59);
                $time = Carbon::today()->setTime($hour, $minute, 0);

                // Memastikan waktu berada dalam jam kerja dan melewatkan waktu istirahat
                if ($time->lt($start) || $time->gte($end) || $time->between($restStart, $restEnd)) {
                    continue; // Lewati jika di luar jam kerja atau di waktu istirahat
                }

                // Hitung waktu habisnya check-in
                $timeout = $time->copy()->addMinutes($duration);

                // Lewati jika waktu habis berada di luar jam kerja
                if ($timeout->greaterThan($end)) {
                    continue; 
                }

                // Cek apakah ada konflik atau jarak kurang dari waktu buffer
                $conflict = false;
                foreach ($times as $scheduled) {
                    $existingStart = Carbon::parse($scheduled['checkin_time']);
                    $existingEnd = Carbon::parse($scheduled['timeout_time']);

                    if (
                        $time->between($existingStart->copy()->subMinutes($bufferMinutes), $existingEnd->copy()->addMinutes($bufferMinutes)) || 
                        $timeout->between($existingStart->copy()->subMinutes($bufferMinutes), $existingEnd->copy()->addMinutes($bufferMinutes))
                    ) {
                        $conflict = true;
                        break; // Keluar dari loop jika ada konflik
                    }
                }

                // Jika tidak ada konflik, tambahkan ke daftar waktu
                if (!$conflict) {
                    $times[] = [
                        'checkin_time' => $time->format('Y-m-d H:i:s'),
                        'timeout_time' => $timeout->format('Y-m-d H:i:s')
                    ];
                } else {
                    $attempts--; // Kurangi jumlah percobaan hanya jika ada konflik
                }
            }

        } while (count($times) < $targetCheckins); // Ulangi jika jumlah check-in kurang dari 10

        return $times;
    }

    private function generateRandomCheckinTimesUser($user)
    {
        $today = Str::lower(Carbon::today()->format('l')); // Mendapatkan nama hari saat ini (misalnya, "Monday")
        $customRestTime = $user->custom_rest_times[$today] ?? null;

        $initialTargetCheckins = 10;  // Target check-in awal
        $targetCheckins = $initialTargetCheckins; // Target check-in yang diinginkan
        $duration = config('services.checking_setting.duration_minutes'); // Durasi setiap check-in dari konfigurasi
        $maxAttempts = 100; // Batas percobaan untuk menghindari loop tak terbatas

        // Buffer awal dan batas minimum
        $initialBufferMinutes = 30;
        $bufferMinutes = $initialBufferMinutes;
        $minBufferMinutes = 10;

        // Konversi waktu mulai, akhir, dan istirahat menjadi objek Carbon
        $start = Carbon::createFromTimeString($user->start_time ?? '08:00');
        $end = Carbon::createFromTimeString($user->end_time ?? '17:00');

        // Atur waktu istirahat berdasarkan custom rest time atau default jika tidak diatur
        if ($customRestTime && $customRestTime['start'] && $customRestTime['end']) {
            $restStart = Carbon::createFromTimeString($customRestTime['start']);
            $restEnd = Carbon::createFromTimeString($customRestTime['end']);
        } else {
            $restStart = Carbon::createFromTimeString($user->rest_time ?? '12:00');
            $restEnd = Carbon::createFromTimeString($user->end_rest_time ?? '13:00');
        }

        do {
            $times = []; // Reset daftar check-in untuk setiap percobaan
            $attempts = $maxAttempts;

            while (count($times) < $targetCheckins && $attempts > 0) {
                $hour = rand($start->hour, $end->hour - 1);
                $minute = rand(0, 59);
                $time = Carbon::today()->setTime($hour, $minute);

                if ($time->lt($start) || $time->gte($end) || $time->between($restStart, $restEnd)) {
                    continue;
                }

                $timeout = $time->copy()->addMinutes($duration);
                if ($timeout->greaterThan($end)) {
                    continue;
                }

                // Cek konflik dengan waktu check-in yang sudah dijadwalkan
                $conflict = false;
                foreach ($times as $scheduled) {
                    $existingStart = Carbon::parse($scheduled['checkin_time']);
                    $existingEnd = Carbon::parse($scheduled['timeout_time']);

                    if (
                        $time->between($existingStart->copy()->subMinutes($bufferMinutes), $existingEnd->copy()->addMinutes($bufferMinutes)) ||
                        $timeout->between($existingStart->copy()->subMinutes($bufferMinutes), $existingEnd->copy()->addMinutes($bufferMinutes))
                    ) {
                        $conflict = true;
                        break;
                    }
                }

                // Jika tidak ada konflik, tambahkan ke jadwal
                if (!$conflict) {
                    $times[] = [
                        'checkin_time' => $time->format('Y-m-d H:i:s'),
                        'timeout_time' => $timeout->format('Y-m-d H:i:s')
                    ];
                } else {
                    $attempts--;
                }
            }

            // Jika tidak cukup check-in, kurangi buffer hingga mencapai batas minimum
            if (count($times) < $targetCheckins && $bufferMinutes > $minBufferMinutes) {
                $bufferMinutes = max($minBufferMinutes, $bufferMinutes - 5);
            }

        } while (count($times) < $targetCheckins && $bufferMinutes > $minBufferMinutes);

        return $times;
    }




    protected function listDayoffEmployee()
    {
        $list = [];
        $listPermission = [];

        $dayoffListService = $this->dayoffService->getCutiListBOS();
        if (isset($dayoffListService['data']) && !$dayoffListService['error'] && count($dayoffListService['data']) > 0)
        {
            $dayoffList = $dayoffListService['data'];
            foreach ($dayoffList['cuti'] as $value) 
            {
                $list[] = strtolower($value['email_staff']);
            } 
            
            foreach ($dayoffList['sakit'] as $value) 
            {
                $listPermission[] = strtolower($value['email_staff']);
            } 
            
            return 
            [
                'cuti' => $list,
                'sakit' => $listPermission
            ];
        }else
        {
            return 
            [
                'cuti' => $list,
                'sakit' => $listPermission
            ];
        }

    }

}
