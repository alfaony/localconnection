<?php

namespace App\Events;

use App\Models\EmployeeChecking;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class EmployeeCheckinActivated implements ShouldBroadcastNow
{
    use SerializesModels;

    public EmployeeChecking $checkin;

    public function __construct(EmployeeChecking $checkin)
    {
        $this->checkin = $checkin;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('employee-checkin.' . $this->checkin->user_id);
    }

    public function broadcastWith(): array
    {
        return [
            'local_id'          => $this->checkin->id,
            'scheduled_time'    => $this->checkin->scheduled_time,
            'scheduled_timeout' => $this->checkin->scheduled_timeout,
            'is_active'         => true,
            'requires_photo'    => (bool) $this->checkin->user->requires_photo,
            'requires_location' => (bool) $this->checkin->user->requires_location,
            'time_server'       => Carbon::now()->tz('Asia/Jakarta')->format('H:i:s'),
        ];
    }
}
