<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Division;
use App\Models\DivisionQuotaLock;
use App\Models\SettingCompany;
use App\Models\Company;
use Carbon\Carbon;

class EnsureDivisionQuotaLocks extends Command
{
    protected $signature = 'quota:ensure-locks {--company_id= : The ID of the company}';
    protected $description = 'Ensure every division has locked quota for current period';

    public function handle()
    {
        $companyId = $this->option('company_id');
        $now = Carbon::now('Asia/Jakarta'); // ✅ Pastikan timezone konsisten

        if ($companyId) {
            $companies = Company::where('id', $companyId)->get();
            
            // ✅ Validasi company exists
            if ($companies->isEmpty()) {
                $this->error("Company with ID {$companyId} not found.");
                return 1;
            }
        } else {
            $companies = Company::all();
        }

        foreach ($companies as $company) 
        {
            $this->info("Processing company: {$company->name}");
            
            $setting = SettingCompany::byCompany($company->id)
                ->get()
                ->pluck('field_value', 'field_title');
            
            $periodStartDay = isset($setting['range_start_date']) && $setting['range_start_date'] !== '' 
                ? (int) $setting['range_start_date'] 
                : 21;

            // ✅ Validasi range tanggal
            if ($periodStartDay < 1 || $periodStartDay > 28) {
                $this->warn("Invalid range_start_date ({$periodStartDay}) for company {$company->name}. Using default 21.");
                $periodStartDay = 21;
            }

            // Calculate period month and yearcl
            if ($now->day >= $periodStartDay) {
                $periodMonth = $now->copy()->addMonth()->month;
                $periodYear = $now->copy()->addMonth()->year;
            } else {
                $periodMonth = $now->month;
                $periodYear = $now->year;
            }

            $this->info("Period: {$periodYear}-{$periodMonth} (Day {$periodStartDay} to Day " . ($periodStartDay - 1) . " next month)");

            // ✅ Fix query - whereHas bisa lambat, pertimbangkan join
            $divisions = Division::whereHas('user', function ($query) use ($company) {
                    $query->where('company_id', $company->id);
                })
                ->where('point_quota_monthly', '>', 0)
                ->get();

            if ($divisions->isEmpty()) {
                $this->warn("No divisions with quota found for company {$company->name}");
                continue;
            }

            foreach ($divisions as $division) {
                $exists = DivisionQuotaLock::where('division_id', $division->id)
                    ->where('month', $periodMonth)
                    ->where('year', $periodYear)
                    ->exists();

                if (!$exists) {
                    DivisionQuotaLock::create([
                        'division_id' => $division->id,
                        'month' => $periodMonth,
                        'year' => $periodYear,
                        'locked_quota' => $division->point_quota_monthly,
                    ]);

                    $this->info("✓ Created quota lock for {$division->name} ({$periodYear}-{$periodMonth})");
                } else {
                    $this->comment("  Quota lock for {$division->name} already exists");
                }
            }

            $this->info("Finished processing company {$company->name}\n");
        }

        $this->info('All companies processed successfully.');
        return 0; // ✅ Success exit code
    }
}