<?php

namespace App\Events;

use App\Models\Inbox;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class InboxReceived implements ShouldBroadcast
{
    use SerializesModels;

    public $inbox;
    public $category;
    public $downloadUrl;

    public function __construct(Inbox $inbox, $category, $downloadUrl = null)
    {
        $this->inbox = $inbox;
        $this->category = $category;
        $this->downloadUrl = $downloadUrl;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.' . $this->inbox->user_id_to);
    }

    public function broadcastWith(): array
    {
        $url = route('inbox.show', $this->inbox->id);
        return [
            'message' => $this->inbox->message,
            'direct_url' => $url,
            'user_from' => optional($this->inbox->userFrom)->name ?? 'System',
            'inbox_id' => $this->inbox->id,
            'category' => $this->category,
            'download_url' => $this->downloadUrl
        ];
    }
}

