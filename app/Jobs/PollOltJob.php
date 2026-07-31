<?php

namespace App\Jobs;

use App\Models\Olt;
use App\Services\Olt\OltMonitoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class PollOltJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 2;

    public function __construct(public int $oltId)
    {
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("poll-olt-{$this->oltId}"))
                ->expireAfter(180)
                ->releaseAfter(30),
        ];
    }

    public function handle(OltMonitoringService $monitoring): void
    {
        $olt = Olt::find($this->oltId);

        if ($olt) {
            $monitoring->poll($olt);
        }
    }
}
