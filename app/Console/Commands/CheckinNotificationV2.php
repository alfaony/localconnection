<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmployeeChecking;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Exception\FirebaseException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\PassChecking;
use App\Models\CheckinLog;
use App\Events\EmployeeCheckinActivated;

class CheckinNotificationV2 extends Command
{
    protected $signature = 'checkin:active {--id= : The ID of the employee checking}';
    protected $description = 'Send notifications to users for scheduled check-ins';

    protected $messaging;

    public function __construct()
    {
        parent::__construct();

        // Firebase Messaging dipertahankan untuk FCM push notification ke device mobile
        $firebase = (new Factory)
            ->withServiceAccount(storage_path(config('services.firebase.service_account')))
            ->withProjectId(config('services.firebase.project_id'));

        $this->messaging = $firebase->createMessaging();
    }

    public function handle()
    {
        // Ambil semua jadwal check-in dari tabel EmployeeChecking yang aktif dan belum selesai
        $id = $this->option('id');
        $checkin = EmployeeChecking::find($id);

        if($checkin && $checkin->user->is_checkin)
        {
            $scheduleTime = Carbon::parse($checkin->scheduled_time)->format('H:i');
            $scheduleTimeout = Carbon::parse($checkin->scheduled_timeout)->format('H:i');

            $currentTime = Carbon::now()->tz('Asia/Jakarta')->format('H:i');
            $passCheckings = PassChecking::whereDate('date', Carbon::today())
                            ->where('user_id', $checkin->user_id)
                            ->whereTime('start_time', '<=', $scheduleTime)
                            ->whereTime('end_time', '>=', $scheduleTime)
                            ->first();

            $this->checkinLog($checkin, Carbon::now());

            if($currentTime == $scheduleTime && !$passCheckings && $checkin->user)
            {
                
                if($checkin->user->manual_checkin == false)
                {
                    $this->scheduleCheckinForUser($checkin);
                    $this->sendCheckinNotification($checkin, 'Time to Check-in', 'Please check-in now!');
                }else
                {
                    $this->sendCheckinNotification($checkin, "It's been 30 minutes, time to check in", 'Please check-in now!');
                }
            }
            elseif ($currentTime == $scheduleTime && $passCheckings) 
            {
                $checkin->is_active = false;
                $checkin->is_completed = true;
                $checkin->is_pass = true;
                $checkin->pass_checking_id = $passCheckings->id;
                $checkin->checkin_start_time = Carbon::now();
                $checkin->save();
            }
            elseif ($currentTime >= $scheduleTime && $currentTime <= $scheduleTimeout && !$passCheckings && $checkin->user)
            {
                
                if($checkin->user->manual_checkin == false)
                {
                    $this->scheduleCheckinForUser($checkin);
                    $this->sendCheckinNotification($checkin, 'Time to Check-in', 'Please check-in now!');
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
        try {
            broadcast(new EmployeeCheckinActivated($checkin));

            $this->checkinLog($checkin, null, json_encode([
                'local_id'       => $checkin->id,
                'scheduled_time' => $checkin->scheduled_time,
                'is_active'      => true,
                'status'         => 'Broadcast sent via WebSocket',
            ]));

        } catch (\Throwable $e) {
            Log::error('CheckinNotificationV2 broadcast gagal: ' . $e->getMessage());
            $this->error("Gagal broadcast WebSocket untuk user {$checkin->user_id}: {$e->getMessage()}");
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

            $fcmTokens = array_filter($fcmTokens, function($token) 
            {
                return is_string($token) && !empty($token);
            });
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
                foreach ($sendReport->failures()->getItems() as $failure) {
                    Log::error('Notification failed for token: ' . $failure->target()->value());
                }
                $error = $sendReport->failures()->getItems()[0]->error()->errors() ?? [];
                $this->checkinLog($checkin, null, null, json_encode($error));
            }


            $this->info("Notification sent to user: {$checkin->user_id} for check-in at: {$checkin->scheduled_time}");

        } catch (FirebaseException $e) {
            $this->checkinLog($checkin, null, null, json_encode($e->getMessage()));
            $this->error("Failed to send notification for user {$checkin->user_id}: " . $e->getMessage());
        }
    }

    protected function checkinLog($checkin, $excuteTime = null, $firebase = null,$fcm =  null)
    {
        $data = [];
        if ($fcm) {
            $data['response_fcm'] = $fcm;
        }
        if ($firebase) {
            $data['response_firebase'] = $firebase;
        }


        $existingLog = CheckinLog::where('employee_checkin_id', $checkin->id)->first();

        if ($existingLog) {
            $existingLog->update($data);
        } else {
            CheckinLog::create(array_merge([
                'employee_checkin_id' => $checkin->id,
                'excecuted_in_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            ], $data));
        }
    }

}
