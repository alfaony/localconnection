<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\NationalHoliday;
use App\Models\EmployeeChecking;
use Carbon\Carbon;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;

use App\Schemas\RoleSchema;
class ScheduleEmployeeCheckin extends Command
{
    protected $signature = 'schedule:employee-checkin';
    protected $description = 'Schedule employee check-in excluding weekends, national holidays, and user leave days';

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
        $onLeaveEmails = [
            'user1@example.com',
            'user2@example.com',
            // tambahkan email lainnya sesuai kebutuhan
        ];

        // Ambil semua user yang tidak cuti
        $users = User::whereNotIn('email', $onLeaveEmails) // Tidak sedang cuti
             ->whereHas('divisions') // Memiliki divisi
             ->whereDoesntHave('role', function ($query) {
                 $query->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN]); // Bukan 'Root' atau 'Admin'
             })
             ->get();

        // 4. Koneksi ke Firebase
        try {
            $firebase = (new Factory)
            ->withServiceAccount(storage_path(config('services.firebase.service_account')))
            ->withDatabaseUri(config('services.firebase.service_database_checkin_url'))
            ->createDatabase();

            $this->info("Terhubung ke Firebase.");
        } catch (FirebaseException $e) {
            $this->error("Gagal terhubung ke Firebase. Data akan tetap disimpan secara lokal.");
            $firebase = null;
        }

        foreach ($users as $user) {
            $this->scheduleCheckinForUser($user, $firebase);
        }
        
        $this->info("Jadwal check-in selesai dibuat dan disimpan di Firebase serta database lokal.");
    }

    private function isNationalHoliday($date)
    {
        return NationalHoliday::where('date', $date->toDateString())->exists();
    }

    private function scheduleCheckinForUser($user, $firebase)
    {
        $checkinTimes = $this->generateRandomCheckinTimes();

        foreach ($checkinTimes as $time) {
            // Simpan di database lokal
            $local = $this->saveLocal($user, $time);

            // Coba simpan di Firebase jika tersedia
            if ($firebase) {
                try {
                    $firebase->getReference('employee_checkins/'.$user->id.'/'.$local->id)->set([
                        'local_id' => $local->id,
                        'scheduled_time' => $time,
                        'is_active' => true,
                        'created_at' => now()->toDateTimeString(),
                    ]);
                } catch (FirebaseException $e) {
                    $this->error("Gagal menyimpan di Firebase untuk user {$user->id}. Data tetap tersimpan di lokal.");
                }
            }
        }
    }

    private function saveLocal($user, $time)
    {
        $firstDivision = $user->first_division;

        return EmployeeChecking::create([
            'user_id' => $user->id,
            'division_id' => $firstDivision->id,
            'scheduled_time' => $time,
            'is_active' => true,
            'is_completed' => false,
        ]);
    }

    private function generateRandomCheckinTimes()
    {
        $times = [];
        while (count($times) < 5
        ) 
        {
            $time = Carbon::today()->addHours(rand(8, 16))->addMinutes(rand(0, 59));
            if ($time->hour === 12) {
                continue; // Lewati iterasi ini dan buat waktu baru
            }
            
            // if ($time->hour === 10) 
            // {
                $times[] = $time->format('Y-m-d H:i:s'); // Format menjadi datetime lengkap
            // }
        }
        return $times;
    }
}
