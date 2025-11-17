<?php

namespace App\Helpers;

use App\Models\Inbox;
use App\Models\User;
use App\Events\InboxReceived;
use Illuminate\Support\Facades\Log;

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
    public function sent($userToId, $userFromId, $message, $directUrl = null, $isRead = false, $category = "entry", $downloadUrl = null)
    {
        try {
            //code...
            if ($userFromId != $userToId) 
            {

                Log::info('InboxHelper::sent START', [
                'userToId' => $userToId,
                'userFromId' => $userFromId,
                'category' => $category,
                'downloadUrl' => $downloadUrl,
                'downloadUrl_type' => gettype($downloadUrl),
                'downloadUrl_is_null' => is_null($downloadUrl),
            ]);

                // Create a new inbox entry in the database
                $inboxMessage = Inbox::create([
                    'user_id_to' => $userToId,
                    'user_id_from' => $userFromId,
                    'message' => $message,
                    'direct_url' => $directUrl,
                    'is_read' => $isRead,
                    'is_notif' => true,
                ]);
                broadcast(new InboxReceived($inboxMessage, $category, $downloadUrl))->toOthers();
                
    
                Log::info('Inbox message sent', [
                    'user_id' => $userToId,
                    'inbox_id' => $inboxMessage->id,
                    'download_url' => $downloadUrl
                ]);

                return $inboxMessage;

            }
    
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error('Failed to send inbox message', [
                'error' => $th->getMessage()
            ]);
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