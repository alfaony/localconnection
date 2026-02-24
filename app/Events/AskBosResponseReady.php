<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AskBosResponseReady implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public string $userId;
    public string $analysis;
    public int    $trustScore;
    public int    $executionScore;

    public function __construct(string $userId, string $analysis, int $trustScore, int $executionScore)
    {
        $this->userId         = $userId;
        $this->analysis       = $analysis;
        $this->trustScore     = $trustScore;
        $this->executionScore = $executionScore;
    }

    public function broadcastOn(): PrivateChannel
    {
        // Mirip pola office.scan.{userId}
        return new PrivateChannel('ask-bos.' . $this->userId);
    }

    public function broadcastWith(): array
    {
        return [
            'analysis'       => $this->analysis,
            'trust_score'    => $this->trustScore,
            'execution_score'=> $this->executionScore,
        ];
    }
}
