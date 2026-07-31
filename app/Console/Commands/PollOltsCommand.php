<?php

namespace App\Console\Commands;

use App\Jobs\PollOltJob;
use App\Models\Olt;
use App\Services\Olt\OltMonitoringService;
use Illuminate\Console\Command;

class PollOltsCommand extends Command
{
    protected $signature = 'olts:poll
                            {--olt= : Poll a specific OLT ID}
                            {--force : Ignore polling interval and disabled state}
                            {--dispatch : Dispatch polling to queue workers}';

    protected $description = 'Poll OLT health and synchronize ONU monitoring data';

    public function handle(OltMonitoringService $monitoring): int
    {
        $query = Olt::query();

        if ($this->option('olt')) {
            $query->whereKey((int) $this->option('olt'));
        }

        $processed = 0;
        $failed = 0;

        $query->orderBy('id')->chunk(50, function ($olts) use ($monitoring, &$processed, &$failed) {
            foreach ($olts as $olt) {
                if (!$this->option('force') && !$olt->needsPolling()) {
                    continue;
                }

                if ($this->option('dispatch')) {
                    PollOltJob::dispatch($olt->id)->onQueue(config('olt.queue', 'default'));
                    $processed++;
                    continue;
                }

                $result = $monitoring->poll($olt);
                $processed++;

                if ($result['success']) {
                    $onuInfo = $result['onus_synced'] === null
                        ? 'OID ONU belum dikonfigurasi'
                        : "{$result['onus_synced']} ONU";
                    $this->info("{$olt->name}: UP, {$onuInfo}");
                } else {
                    $failed++;
                    $this->error("{$olt->name}: {$result['message']}");
                }
            }
        });

        $this->line("Selesai: {$processed} OLT diproses, {$failed} gagal.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
