<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\RecurringRule;
use App\Models\DailyTask;
use App\Models\DailyTaskCustomFieldValue;
use App\Models\DailyTaskMedia;
use App\Models\TaskStatus;
use App\Schemas\ParamSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DailyTaskMessage;
use App\Models\DailyTaskStatusRecord;

class GenerateRecurringTasks extends Command
{
    protected $signature = 'recurring:generate';
    protected $description = 'Generate recurring DailyTasks berdasarkan RecurringRule';

    public function handle()
    {
        $today = Carbon::today();
        $dayCode = strtoupper(substr($today->format('l'), 0, 2)); // e.g. 'MO', 'WE'
        Log::info('recurring:generate started', [
            'date' => $today->toDateString(),
            'day_code' => $dayCode,
        ]);

        DB::beginTransaction();
        try {
            $rules = RecurringRule::where('active', true)
                ->whereDate('start_date', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('until')->orWhereDate('until', '>=', $today);
                })
                ->get();
            Log::info('Fetched recurring rules for generation', [
                'count' => $rules->count(),
            ]);
    
            foreach ($rules as $rule) 
            {
                Log::info('Evaluating recurring rule', [
                    'rule_id' => $rule->id,
                    'frequency' => $rule->frequency,
                    'by_day' => $rule->by_day,
                    'by_month_day' => $rule->by_month_day,
                    'by_month' => $rule->by_month,
                ]);

                if (!$this->shouldRunToday($rule, $today, $dayCode)) {
                    Log::info('Skipping rule; not scheduled for today', [
                        'rule_id' => $rule->id,
                        'date' => $today->toDateString(),
                    ]);
                    continue;
                }
    
                // Cegah duplikasi
                $already = DailyTask::where('recurring_rule_id', $rule->id)
                    ->whereDate('start_date', $today)
                    ->exists();
    
                if ($already) {
                    Log::info('Skipping rule; daily task already exists for today', [
                        'rule_id' => $rule->id,
                        'date' => $today->toDateString(),
                    ]);
                    continue;
                }
    
                // Ambil template task terakhir
                $template = $rule->dailyTask()
                    ->with(['customFieldValues', 'keyResults', 'media'])
                    ->orderBy('start_date', 'desc')
                    ->first();
    
                if (!$template) {
                    Log::warning('Skipping rule; no template task found', [
                        'rule_id' => $rule->id,
                    ]);
                    continue;
                }

                if ($this->shouldSkipTaskGeneration($template->assignment_user_id, $today)) 
                {
                    Log::info('Skipping task generation due to holiday or leave', [
                        'rule_id' => $rule->id,
                        'assignment_user_id' => $template->assignment_user_id,
                        'date' => $today->toDateString(),
                    ]);
                    return; // Skip bikin task hari ini
                }

                $todo = TaskStatus::where('name',ParamSchema::TODO)->firstOrFail();
                Log::debug('Resolved TODO task status for recurring generation', [
                    'status_id' => $todo->id,
                ]);
    
                $newTask = $template->replicate();
                $newTask->slug = $this->createUniqueSlug(DailyTask::class, $template->name);
                $newTask->start_date = $today;
                $newTask->end_date = $today;
                $newTask->recurring_rule_id = $rule->id;
                $newTask->task_status_id = $todo->id;
                $newTask->report_note = NULL;
                $newTask->submit = NULL;
                $newTask->status_submit = NULL;
                $newTask->approved = FALSE;
                $newTask->point = 0; // Assuming default value is 0
                // Simpan tugas baru
                $newTask->save();
                Log::info('Created new daily task from recurring rule', [
                    'rule_id' => $rule->id,
                    'daily_task_id' => $newTask->id,
                    'slug' => $newTask->slug,
                ]);
                
                $keyResults = $template->keyResults;
                foreach ($keyResults as $keyResult) 
                {
                    $newTask->keyResults()->attach($keyResult->id);
                }
                Log::debug('Attached key results to new daily task', [
                    'daily_task_id' => $newTask->id,
                    'key_result_ids' => $keyResults->pluck('id')->toArray(),
                ]);
    
                $this->message($newTask,'create',' System Membuat Tugas '.$newTask->name);
                $this->statusrecord($newTask, $todo);
    
                // Custom field
                foreach ($template->customFieldValues as $cf) {
                    DailyTaskCustomFieldValue::create([
                        'daily_task_id' => $newTask->id,
                        'custom_field_id' => $cf->custom_field_id,
                        'custom_field_value_id' => $cf->custom_field_value_id,
                    ]);
                }
                Log::debug('Copied custom field values to new daily task', [
                    'daily_task_id' => $newTask->id,
                    'custom_field_value_ids' => $template->customFieldValues->pluck('id')->toArray(),
                ]);
    
                // Key result
                $newTask->keyResults()->sync($template->keyResults->pluck('id')->toArray());
                Log::debug('Synchronized key results on new daily task', [
                    'daily_task_id' => $newTask->id,
                ]);
    
                // Media
                foreach ($template->taskMedia as $media) {
                    DailyTaskMedia::create([
                        'daily_task_id' => $newTask->id,
                        'file_path' => $media->file_path,
                        'file_type' => $media->file_type,
                        'status' => $media->status,
                    ]);
                }
                Log::debug('Copied media attachments to new daily task', [
                    'daily_task_id' => $newTask->id,
                    'media_count' => $template->taskMedia->count(),
                ]);


                $this->info("Generated task for rule #{$rule->id} on {$today->toDateString()}");
                Log::info('Completed task generation for rule', [
                    'rule_id' => $rule->id,
                    'daily_task_id' => $newTask->id,
                ]);

                DB::commit();
                Log::info('Transaction committed for recurring task generation', [
                    'rule_id' => $rule->id,
                ]);
            }
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error('Error generating recurring tasks', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            DB::rollBack();
            Log::warning('Transaction rolled back for recurring task generation');
        }
    }

    private function shouldRunToday($rule, $today, $dayCode)
    {
        return match ($rule->frequency) {
            'DAILY'   => true,
            'WEEKLY'  => in_array($dayCode, $rule->by_day ?? []),
            'MONTHLY' => in_array($today->day, $rule->by_month_day ?? []),
            'YEARLY'  => in_array($today->month, $rule->by_month ?? [])
                        && in_array($today->day, $rule->by_month_day ?? []),
            default   => false,
        };
    }

    protected function message($dailyTask, $template, $message, $filePath = null)
    {
        switch ($template) 
        {
            case 'create':
                $message = 
                '
                <div class="alert alert-primary d-flex align-items-center" role="alert" style="background-color: #cce5ff; border-color: #004085; color: #004085;">
                    <i class="fa fa-plus-circle mr-2" style="color: #004085;"></i>
                    <div>
                        '.$message.' 
                    </div>
                </div>
                ';
                break;
            case 'edit':
                $message = 
                '
                <div class="alert alert-warning d-flex align-items-center" role="alert" style="background-color: #fff3cd; border-color: #856404; color: #856404;">
                    <i class="fa fa-edit mr-2" style="color: #856404;"></i>
                    <div>
                        '.$message.' 
                    </div>
                </div>
                ';
                break;
            case 'report':
                $message = 
                '
                <div class="alert alert-primary d-flex align-items-center" role="alert" style="background-color: #cce5ff; border-color: #004085; color: #004085;">
                    <i class="fa fa-plus-circle mr-2" style="color: #004085;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            case 'approvement':
                $message = 
                '
                <div class="alert alert-success d-flex align-items-center" role="alert" style="background-color: #d4edda; border-color: #155724; color: #155724;">
                    <i class="fa fa-thumbs-up mr-2" style="color: #155724;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            case 'extend':
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #383d41; color: #383d41;">
                    <i class="fa fa-clock mr-2" style="color: #383d41;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;

            case 'reject':
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #ae2121; color: #ae2121;">
                    <i class="fa fa-times-circle mr-2" style="color: #ae2121;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            default:
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #383d41; color: #383d41;">
                    <i class="fa fa-comment mr-2" style="color: #383d41;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
        }

        $dailyTaskMessage = new DailyTaskMessage();
        $dailyTaskMessage->user_id = $dailyTask->user_id;
        $dailyTaskMessage->daily_task_id = $dailyTask->id;
        $dailyTaskMessage->message = $message;
        $dailyTaskMessage->file_path = $filePath ?? NULL;
        $dailyTaskMessage->save();
        Log::debug('Created daily task message', [
            'daily_task_id' => $dailyTask->id,
            'user_id' => $dailyTask->user_id,
            'template' => $template,
        ]);

        return true;
    } 

    protected function statusrecord($dailyTask, $status)
    {
        DailyTaskStatusRecord::create([
            'daily_task_id' => $dailyTask->id,
            'task_status_id' => $status->id,
            'date' => now(),
        ]);
        Log::debug('Recorded daily task status change', [
            'daily_task_id' => $dailyTask->id,
            'task_status_id' => $status->id,
        ]);

        return true;
    }

    protected function createUniqueSlug($modelClass, $title)
    {
            $baseSlug = Str::slug($title);
            $slug = $baseSlug;

            $existingSlugs = $modelClass::withTrashed()
                ->where('slug', 'LIKE', "{$baseSlug}%")
                ->pluck('slug')
                ->toArray();

            if (!in_array($slug, $existingSlugs)) {
                return $slug;
            }

            $count = 1;
            while (in_array("{$baseSlug}-{$count}", $existingSlugs)) {
                $count++;
            }

            $uniqueSlug = "{$baseSlug}-{$count}";
            Log::debug('Generated unique slug for model', [
                'model' => $modelClass,
                'base_slug' => $baseSlug,
                'unique_slug' => $uniqueSlug,
            ]);

            return $uniqueSlug;
    }

    protected function shouldSkipTaskGeneration($userId, Carbon $date): bool
    {
        // Cek apakah hari ini libur (misal cek di tabel holidays)
        $isHoliday = \App\Models\NationalHoliday::whereDate('date', $date)->exists();

        // Cek apakah user sedang cuti
        $isOnLeave = \App\Models\Dayoff::where('user_id', $userId)
            ->where(function ($query) {
                $query->whereNotNull('approval_hr_user_id')->orWhereNotNull('approval_finance_user_id');
            })
            ->whereDate('date_start', '<=', $date)
            ->whereDate('date_end', '>=', $date)
            ->exists();

        Log::info('Evaluated skip condition for task generation', [
            'user_id' => $userId,
            'date' => $date->toDateString(),
            'is_holiday' => $isHoliday,
            'is_on_leave' => $isOnLeave,
        ]);

        return $isHoliday || $isOnLeave;
    }
}
