<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventOccurrence;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

class GenerateEventOccurrences extends Command
{
    protected $signature = 'event:generate-occurrences
                            {--weeks=2 : Berapa minggu ke depan yang digenerate}
                            {--dry-run : Tampilkan saja tanpa simpan}';

    protected $description = 'Generate occurrences untuk routine events (dijalankan tiap minggu via scheduler)';

    public function handle(): int
    {
        $weeks   = (int) $this->option('weeks');
        $dryRun  = $this->option('dry-run');
        $created = 0;

        $routineEvents = Event::where('is_routine', true)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get();

        $this->info("Ditemukan {$routineEvents->count()} routine event.");

        for ($w = 0; $w <= $weeks; $w++) {
            $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeeks($w);

            foreach ($routineEvents as $event) {
                // Cek batas routine_end_date
                if ($event->routine_end_date && $weekStart->gt($event->routine_end_date)) {
                    continue;
                }

                // Hitung occurrence untuk minggu ini
                $occurrence = $event->generateOccurrenceForWeek($weekStart);

                if ($occurrence && $occurrence->wasRecentlyCreated) {
                    $created++;
                    $this->line("  ✓ [{$event->name}] {$occurrence->start_date->format('d M')} – {$occurrence->end_date->format('d M')}");
                }
            }
        }

        $this->info("Selesai. Total occurrence dibuat: {$created}");
        return Command::SUCCESS;
    }
}
