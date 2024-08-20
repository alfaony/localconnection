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
        if(config('services.firebase.service_account') && config('services.firebase.service_database_url') && !isset($this->database))
        {
            $factory = (new Factory)
                ->withServiceAccount(storage_path(config('services.firebase.service_account'))) // Ambil dari konfigurasi
                ->withDatabaseUri(config('services.firebase.service_database_url'));
    
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
                        // 'message' => $message,
                        // 'direct_url' => $directUrl,
                        'is_read' => $isRead,
                        // 'timestamp' => now()->timestamp,
                        'inbox_id' => $inboxMessage->id,
                    ]);

            }

            return $inboxMessage;
        }

        return true;
    }

    public function read($userToId, $inboxId)
    {
        if($this->database)
        {

            // Cari referensi data dengan inbox_id yang sesuai
            $notificationRef = $this->database->getReference('notifications/' . $userToId);
            
            if($notificationRef->getSnapshot()->exists() === false)
            {
                return;
            }

            $reference = $this->database->getReference('notifications/' . $userToId)
            ->orderByChild('inbox_id')
            ->equalTo($inboxId)
            ->getSnapshot();

            // Periksa apakah data ditemukan
            if ($reference->exists()) {
                $updates = [];
                foreach ($reference->getValue() as $key => $value) {
                    $updates['notifications/' . $userToId . '/' . $key . '/is_read'] = true;
                }
            
                // Perform the update
                $this->database->getReference()->update($updates);
            }
        }

        return;
    }
}