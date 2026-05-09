<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class EmployeeCheckinDeactivated implements ShouldBroadcastNow
{
    public string $userId;
    public int $checkinId;

    public function __construct(string $userId, int $checkinId)
    {
        $this->userId    = $userId;
        $this->checkinId = $checkinId;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('employee-checkin.' . $this->userId);
    }

    public function broadcastWith(): array
    {
        return [
            'local_id'  => $this->checkinId,
            'is_active' => false,
        ];
    }
}
