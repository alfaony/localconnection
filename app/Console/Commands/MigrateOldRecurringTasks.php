<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyTask;
use App\Models\RecurringRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\DailyTaskType;
use App\Schemas\ParamSchema;

class MigrateOldRecurringTasks extends Command
{
    protected $signature = 'tasks:migrate-old-recurring';

    protected $description = 'Migrate old recurring system to new recurring rules';

    public function handle()
    {
        // Update DailyTaskType PRAMSCHEMA::DAILY to default true and non recurring
        $dailyTaskType = DailyTaskType::where('name', ParamSchema::DAILY)->first();
        if($dailyTaskType && !$dailyTaskType->is_default)
        {
            $dailyTaskType->is_default = true;
            $dailyTaskType->save();

            $this->info("Updated DailyTaskType PRAMSCHEMA::DAILY to default true and non recurring");
        }

        $this->info("Starting migration of old recurring tasks...");

        DB::beginTransaction();
        try {
            // Step 1: Ambil semua unique recurring_group_id
            $groupIds = DailyTask::whereNotNull('recurring_group_id')
                ->groupBy('recurring_group_id')
                ->pluck('recurring_group_id');

            foreach ($groupIds as $groupId) {
                $template = DailyTask::where('recurring_group_id', $groupId)
                    ->orderBy('start_date')
                    ->first();

                if (!$template || empty($template->recurring_days)) {
                    $this->warn("Skipping group {$groupId} due to missing data.");
                    continue;
                }

                // Step 2: Konversi recurring_days ke RRULE by_day
                $days = json_decode($template->recurring_days, true);
                $byDay = array_map(function ($day) {
                    return match (strtolower($day)) {
                        'monday' => 'MO',
                        'tuesday' => 'TU',
                        'wednesday' => 'WE',
                        'thursday' => 'TH',
                        'friday' => 'FR',
                        'saturday' => 'SA',
                        'sunday' => 'SU',
                        default => null,
                    };
                }, $days);

                $byDay = array_filter($byDay);

                if (empty($byDay)) {
                    $this->warn("Skipping group {$groupId} due to invalid days.");
                    continue;
                }

                // Step 3: Buat RecurringRule baru
                $rule = RecurringRule::create([
                    'frequency' => 'WEEKLY',
                    'interval' => 1,
                    'by_day' => $byDay,
                    'start_date' => $template->start_date,
                    'description' => $template->description,
                ]);

                // Step 4: Assign rule ke template (atau semua task)
                DailyTask::where('recurring_group_id', $groupId)
                    ->update([
                        'recurring_rule_id' => $rule->id,
                        // 'recurring_days' => null, // kosongkan field lama
                        // 'recurring_group_id' => null // opsional
                    ]);

                $this->info("✅ Migrated group {$groupId} with rule ID {$rule->id}");
            }

            DB::commit();
            $this->info("Migration completed successfully.");
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error("Migration failed: " . $th->getMessage());
        }
    }
}
