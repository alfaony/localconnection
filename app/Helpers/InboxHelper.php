<?php

namespace App\Helpers;

use App\Models\Inbox;
use App\Models\User;
use App\Events\InboxReceived;

class InboxHelper
{
    protected $database;
    protected $messaging;

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
    public function sent($userToId, $userFromId, $message, $directUrl = null, $isRead = false, $category = "entry")
    {
        try {
            //code...
            if ($userFromId != $userToId) 
            {
                // Create a new inbox entry in the database
                $inboxMessage = Inbox::create([
                    'user_id_to' => $userToId,
                    'user_id_from' => $userFromId,
                    'message' => $message,
                    'direct_url' => $directUrl,
                    'is_read' => $isRead,
                    'is_notif' => true,
                ]);
                broadcast(new InboxReceived($inboxMessage, $category))->toOthers();
                
    
                return $inboxMessage;
            }
    
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            return throw $th;
        }
    }


     public function read($userToId, $inboxId)
    {
        $inboxId = Inbox::find($inboxId);
        $inboxId->is_read = true;
        $inboxId->save();
    }
}