<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyTask;
use App\Models\TaskStatus;
use App\Models\DailyTaskStatusRecord;
use App\Models\DailyTaskMessage;
use App\Models\User;
use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;
use App\Helpers\InboxHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompleteInReviewDailyTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dailytask:complete-inreview';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically complete all Daily Tasks that are currently in INREVIEW status.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Starting the process to complete INREVIEW tasks...");

        try {
            DB::beginTransaction();

            $inReviewStatus = TaskStatus::where('name', ParamSchema::INREVIEW)->first();
            $completeStatus = TaskStatus::where('name', ParamSchema::COMPLATE)->first();

            if (!$inReviewStatus || !$completeStatus) {
                $this->error("Status INREVIEW or COMPLETE not found.");
                return 1;
            }

            // Temukan semua task dengan status INREVIEW yang lebih dari 2 bulan
            $twoMonthsAgo = now()->subMonths(2);
            $tasks = DailyTask::where('task_status_id', $inReviewStatus->id)
                              ->where('created_at', '<', $twoMonthsAgo)
                              ->get();

            if ($tasks->isEmpty()) {
                $this->info("No tasks found with INREVIEW status.");
                return 0;
            }

            // Cari user sistem sebagai approver
            // Prioritas: ROOT -> SYSTEM_BOS -> SYSTEM_ADMIN -> SYSTEM -> ADMIN
            $systemUser = User::whereHas('role', function ($q) {
                $q->whereIn('name', [
                    RoleSchema::SYSTEM_BOS,
                    RoleSchema::SYSTEM_ADMIN,
                    RoleSchema::ROOT,
                    RoleSchema::ADMIN
                ]);
            })->first();

            $approverId = $systemUser ? $systemUser->id : null;
            $approverName = $systemUser ? $systemUser->name : 'System';

            $count = 0;
            $inboxHelper = new InboxHelper();

            foreach ($tasks as $task) {
                // Update task
                $task->point = 0;
                $task->task_status_id = $completeStatus->id;
                $task->approved = true;
                $task->save();

                // Status record
                DailyTaskStatusRecord::create([
                    'daily_task_id' => $task->id,
                    'task_status_id' => $completeStatus->id,
                    'date' => now(),
                ]);

                // Message record (Template Approvement)
                $messageText = 'Membuat Approvement Tugas ' . $task->name . ' (Auto-approved by System)';
                $messageHtml = '
                <div class="alert alert-success d-flex align-items-center" role="alert" style="background-color: #d4edda; border-color: #155724; color: #155724;">
                    <i class="fa fa-thumbs-up mr-2" style="color: #155724;"></i>
                    <div>
                        ' . $messageText . '
                    </div>
                </div>
                ';

                DailyTaskMessage::create([
                    'user_id' => $approverId ?? $task->assignment_user_id, // Fallback if no system user found
                    'daily_task_id' => $task->id,
                    'message' => $messageHtml,
                    'file_path' => null,
                ]);

                // Notifikasi
                $directUrl = route('dailytask.show', ['dailytask' => $task->slug]);
                $userTo = ($task->assignment_user_id) ? $task->assignment_user_id : $task->user_id;

                if ($approverId) {
                    $inboxHelper->sent(
                        $userTo,
                        $approverId,
                        "Tugas " . $task->name . " telah di " . $completeStatus->name . " (Auto)",
                        $directUrl
                    );
                }

                if ($task->head) {
                    if ($approverId) {
                        $inboxHelper->sent($task->head->user_id, $approverId, $task->name . " telah di " . $completeStatus->name . " (Auto)", $directUrl);
                        if ($task->head->assignment_user_id && $task->head->assignment_user_id != $task->head->user_id) {
                            $inboxHelper->sent($task->head->assignment_user_id, $approverId, $task->name . " telah di " . $completeStatus->name . " (Auto)", $directUrl);
                        }
                    }
                }

                $count++;
            }

            DB::commit();

            $this->info("Successfully completed {$count} INREVIEW tasks.");
            return 0;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error auto-completing INREVIEW tasks: " . $e->getMessage());
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
