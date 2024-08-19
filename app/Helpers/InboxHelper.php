<?php

namespace App\Helpers;

use App\Models\Inbox;
use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class InboxHelper
{
    protected $database;
    protected $messaging;

    public function __construct()
    {
        if(config('services.firebase.service_account') && config('services.firebase.service_database_url'))
        {
            $factory = (new Factory)
                ->withServiceAccount(storage_path(config('services.firebase.service_account'))) // Ambil dari konfigurasi
                ->withDatabaseUri(config('services.firebase.service_database_url') ?? 'https://notifikasi-aca1a-default-rtdb.asia-southeast1.firebasedatabase.app');
    
            $this->database = $factory->createDatabase();
            $this->messaging = $factory->createMessaging();
        }
    }

    /**
     * Send a message to the inbox.
     *
     * @param int $userToId
     * @param int $userFromId
     * @param string $message
     * @param string|null $directUrl
     * @param bool $isRead
     * @return Inbox|bool
     */
    public function sent($userToId, $userFromId, $message, $directUrl = null, $isRead = false)
    {
        if ($userFromId != $userToId) {
            // Create a new inbox entry in the database
            $inboxMessage = Inbox::create([
                'user_id_to' => $userToId,
                'user_id_from' => $userFromId,
                'message' => $message,
                'direct_url' => $directUrl,
                'is_read' => $isRead,
            ]);

            if(isset($this->database))
            {
                // Push the notification to Firebase Realtime Database
                $this->database
                    ->getReference('notifications/' . $userToId)
                    ->push([
                        'message' => $message,
                        'direct_url' => $directUrl,
                        'is_read' => $isRead,
                        'timestamp' => now()->timestamp,
                    ]);
    
                // Retrieve the FCM token for the user (assuming you store tokens in the database)
                $userTo = User::find($userToId);
                $fcmToken = $userTo->fcm_token; // Assume 'fcm_token' is the column where the token is stored
    
                if ($fcmToken) {
                    // Send a push notification using Firebase Cloud Messaging
                    $notification = CloudMessage::withTarget('token', $fcmToken)
                        ->withNotification([
                            'title' => 'New Inbox Message',
                            'body' => $message,
                        ])
                        ->withData(['direct_url' => $directUrl]);
    
                    try {
                        $this->messaging->send($notification);
                    } catch (\Kreait\Firebase\Exception\MessagingException $e) {
                        // Log the error or handle the exception
                        \Log::error("FCM Error: " . $e->getMessage());
                    }
                } else {
                    \Log::warning("FCM token is missing for user_id: $userToId");
                }
            }

            return $inboxMessage;
        }

        return true;
    }

}