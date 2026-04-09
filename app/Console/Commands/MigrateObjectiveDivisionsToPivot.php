<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Objective;

class MigrateObjectiveDivisionsToPivot extends Command
{
    protected $signature = 'objective:migrate-divisions-to-pivot';

    protected $description = 'Pindahkan data division_id dari tabel objectives ke pivot table division_objective';

    public function handle()
    {
        $objectives = Objective::whereNotNull('division_id')->get();

        if ($objectives->isEmpty()) {
            $this->info('Tidak ada data untuk dipindahkan.');
            return;
        }

        $count = 0;

        foreach ($objectives as $objective) {
            $alreadyExists = $objective->divisions()
                ->where('divisions.id', $objective->division_id)
                ->exists();

            if (!$alreadyExists) {
                $objective->divisions()->attach($objective->division_id);
                $count++;
            }
        }

        $this->info("Selesai. {$count} dari {$objectives->count()} objective dipindahkan ke pivot table.");
    }
}
