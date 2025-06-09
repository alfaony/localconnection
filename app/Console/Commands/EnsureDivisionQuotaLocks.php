<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Division;
use App\Models\DivisionQuotaLock;

class EnsureDivisionQuotaLocks extends Command
{
    protected $signature = 'quota:ensure-locks';
    protected $description = 'Ensure every division has locked quota for current month';

    public function handle()
    {
        $month = now()->month;
        $year = now()->year;

        $divisions = Division::where('point_quota_monthly', '>', 0)->get();

        foreach ($divisions as $division) {
            $exists = DivisionQuotaLock::where('division_id', $division->id)
                ->where('month', $month)
                ->where('year', $year)
                ->exists();

            if (!$exists) {
                DivisionQuotaLock::create([
                    'division_id' => $division->id,
                    'month' => $month,
                    'year' => $year,
                    'locked_quota' => $division->point_quota_monthly,
                ]);

                $this->info("Locked quota for {$division->name} created.");
            }
        }

        $this->info('All divisions checked.');
    }
}
