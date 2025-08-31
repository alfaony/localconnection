<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BarcodeVerifiedSuccess implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $userId;
    public $verified;
    public function __construct(String $userId, Bool $verified)
    {
        $this->userId = $userId;
        $this->verified = $verified;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('office.scan.' . $this->userId);
    }

    public function broadcastWith(): array
    {
        return 
        [
            'user_id' => $this->userId,
            'verified' => $this->verified,
        ];
    }
}
