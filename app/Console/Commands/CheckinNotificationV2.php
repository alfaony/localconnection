<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmployeeChecking;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Exception\FirebaseException;
use Carbon\Carbon;

class CheckinNotificationV2 extends Command
{
    protected $signature = 'checkin:active {--id= : The ID of the employee checking}';
    protected $description = 'Send notifications to users for scheduled check-ins';

    protected $messaging;

    public function __construct()
    {
        parent::__construct();

        // Menginisialisasi Firebase Messaging
        $firebase = (new Factory)
            ->withServiceAccount(storage_path(config('services.firebase.service_account')))
            ->withProjectId(config('services.firebase.project_id'))
            ->withDatabaseUri(config('services.firebase.service_database_checkin_url')); // Add database URL

        $this->messaging = $firebase->createMessaging();
        $this->firebase = $firebase->createDatabase();
    }

    public function handle()
    {
        // Ambil semua jadwal check-in dari tabel EmployeeChecking yang aktif dan belum selesai
        $id = $this->option('id');
        $checkin = EmployeeChecking::find($id);

        if($checkin)
        {
            $scheduleTime = Carbon::parse($checkin->scheduled_time)->format('H:i');
            $currentTime = Carbon::now()->tz('Asia/Jakarta')->format('H:i');
    
            if($currentTime == $scheduleTime)
            {
                
                if($checkin->user->manual_checkin == false)
                {
                    $this->sendCheckinNotification($checkin, 'Time to Check-in', 'Please check-in now!');
                    $this->scheduleCheckinForUser($checkin);
                }else
                {
                    $this->sendCheckinNotification($checkin, "It's been 30 minutes, time to check in", 'Please check-in now!');
                }
            }
        }


        $this->info('Check-in notifications processed successfully.');
    }

    private function scheduleCheckinForUser($checkin)
    {
        // Coba simpan di Firebase jika tersedia
        if ($this->firebase) 
        {
            try {
                $this->firebase->getReference('employee_checkins/'.$checkin->user_id.'/'.$checkin->id)->set([
                    'local_id' => $checkin->id,
                    'scheduled_time' => $checkin->scheduled_time,
                    'scheduled_timeout' => $checkin->scheduled_timeout,
                    'is_active' => true,
                    'requires_photo' => $checkin->division->requires_photo,
                    'requires_location' => $checkin->division->requires_location,
                    'time_server' => Carbon::now()->tz('Asia/Jakarta'),
                ]);
            } catch (FirebaseException $e) {
                $this->error("Gagal menyimpan di Firebase untuk user {$checkin->user_id}. Data tetap tersimpan di lokal.");
            }
        }
    }

    protected function sendCheckinNotification(EmployeeChecking $checkin, $title, $body)
    {
        try {
            // Ambil FCM ID dari user terkait
            if (!$checkin->user->status) 
            {
                $this->warn("No user status found for user: {$checkin->user_id}");
                return;
            }

            $userFcmCollect = $checkin->user->status;

            if (!$userFcmCollect) 
            {
                $this->warn("No FCM ID found for user: {$checkin->user_id}");
                return;
            }

            $url = route('employee-checking.index');

            // Buat pesan notifikasi
            $fcmTokens = $userFcmCollect->pluck('fcm_id')->toArray();

            // Create the notification message
            $message = CloudMessage::new()
                ->withNotification([
                    'title' => $title,
                    'body' => $body,
                ])
                ->withData([
                    'checkin_time' => $checkin->scheduled_time,
                    'user_id' => $checkin->user_id,
                    'url' => $url,
            ])->withAndroidConfig([
                'priority' => 'high',
            ]);
        

            // Kirim pesan notifikasi ke Firebase
            $sendReport = $this->messaging->sendMulticast($message, $fcmTokens);
            if ($sendReport->hasFailures()) 
            {
                // Log or handle any failures
                foreach ($sendReport->failures()->all() as $failure) {
                    Log::error('Notification failed for token: ' . $failure->target()->value());
                }
            }

            $this->info("Notification sent to user: {$checkin->user_id} for check-in at: {$checkin->scheduled_time}");

        } catch (FirebaseException $e) {
            $this->error("Failed to send notification for user {$checkin->user_id}: " . $e->getMessage());
        }
    }
}
