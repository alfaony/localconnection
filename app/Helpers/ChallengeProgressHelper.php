<?php

namespace App\Helpers;

use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\DailyTask;
use App\Models\EmployeeXpHistory;
use App\Schemas\ParamSchema;
use App\Models\TaskStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Helpers\InboxHelper;

class ChallengeProgressHelper
{
    /**
     * Hitung progress user pada sebuah challenge.
     * Return: integer (jumlah/nilai saat ini, bukan persentase).
     */
    public static function current(Challenge $challenge, string $userId): int
    {
        $start = $challenge->start_date->startOfDay();
        $end   = $challenge->end_date->endOfDay();

        return match ($challenge->module_type) {
            Challenge::MODULE_TASK          => self::countTask($userId, $start, $end),
            Challenge::MODULE_INTERNET      => self::countInternet($userId, $start, $end),
            Challenge::MODULE_KASIR         => self::countKasir($userId, $start, $end),
            Challenge::MODULE_SPRINTER      => self::countSprinter($userId, $start, $end),
            Challenge::MODULE_MEETING       => self::countMeeting($userId, $start, $end),
            Challenge::MODULE_DECISION      => self::countDecision($userId, $start, $end),
            Challenge::MODULE_WEEKLY_REPORT => self::countWeeklyReport($userId, $start, $end),
            Challenge::MODULE_SCORE         => self::countScore($userId, $start, $end),
            default                         => 0,
        };
    }

    /**
     * Hitung persen (0–100, bisa > 100 jika melebihi target).
     */
    public static function percent(Challenge $challenge, string $userId): int
    {
        if ($challenge->target_count <= 0) return 0;
        $pct = (int) round((self::current($challenge, $userId) / $challenge->target_count) * 100);
        return min($pct, 100);
    }

    // ── Module Queries ─────────────────────────────────────────────────────

    private static function countTask(string $userId, Carbon $start, Carbon $end): int
    {
        $completeId = TaskStatus::where('name', ParamSchema::COMPLATE)->value('id');
        return DailyTask::where('assignment_user_id', $userId)
            ->where('task_status_id', $completeId)
            ->whereBetween('submit', [$start, $end])
            ->count();
    }

    private static function countInternet(string $userId, Carbon $start, Carbon $end): int
    {
        if (!class_exists(\App\Models\InternetCustomerInstallation::class)) return 0;
        return \App\Models\InternetCustomerInstallation::where('technical_user_id', $userId)
            ->whereBetween('installed_at', [$start, $end])
            ->count();
    }

    private static function countKasir(string $userId, Carbon $start, Carbon $end): int
    {
        // Model belum diimplementasi — placeholder
        return \App\Models\Sale::where('user_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    private static function countSprinter(string $userId, Carbon $start, Carbon $end): int
    {
        return DB::table('item_requests')
            ->where('assigned_pic_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    private static function countMeeting(string $userId, Carbon $start, Carbon $end): int
    {
        // Meeting: user sebagai peserta (pivot meeting_user)
        $meetings = DB::table('meeting_user')
            ->join('meetings', 'meetings.id', '=', 'meeting_user.meeting_id')
            ->where('meeting_user.user_id', $userId)
            ->whereBetween('meetings.start_date', [$start->toDateString(), $end->toDateString()])
            ->whereNull('meetings.deleted_at')
            ->count();

        // MOM: user sebagai pembuat
        $moms = DB::table('moms')
            ->where('user_id', $userId)
            ->whereBetween('mom_date', [$start->toDateString(), $end->toDateString()])
            ->whereNull('deleted_at')
            ->count();

        return $meetings + $moms;
    }

    private static function countDecision(string $userId, Carbon $start, Carbon $end): int
    {
        return DB::table('decisions')
            ->where('user_create_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->whereNull('deleted_at')
            ->count();
    }

    private static function countWeeklyReport(string $userId, Carbon $start, Carbon $end): int
    {
        return DB::table('weekly_reports')
            ->where('user_id', $userId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNull('deleted_at')
            ->count();
    }

    private static function countScore(string $userId, Carbon $start, Carbon $end): int
    {
        return (int) EmployeeXpHistory::where('user_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->where('xp', '>', 0)
            ->sum('xp');
    }

    // ── Reward Distribution ────────────────────────────────────────────────

    /**
     * Cek & distribusikan reward jika user sudah mencapai target.
     * Dipanggil saat endpoint home/active-challenges.
     */
    public static function checkAndGiveReward(Challenge $challenge, string $userId): void
    {
        $cu = ChallengeUser::where('challenge_id', $challenge->id)
                           ->where('user_id', $userId)
                           ->where('reward_given', false)
                           ->first();

        if (!$cu) return;

        $current = self::current($challenge, $userId);
        if ($current < $challenge->target_count) return;

        // Tandai selesai
        $cu->update([
            'reward_given' => true,
            'completed_at' => now(),
            'finished_at'  => now(),
        ]);

        // Beri Point (DirectPoint langsung approve)
        if ($challenge->reward_point > 0) {
            \App\Models\DirectPoint::create([
                'from_user_id' => $challenge->created_by,
                'to_user_id'   => $userId,
                'point'        => $challenge->reward_point,
                'approved_point' => $challenge->reward_point,
                'reason'       => 'Challenge reward: ' . $challenge->name,
                'metode'       => ParamSchema::CHALLENGE,
                'status'       => \App\Models\DirectPoint::STATUS_APPROVED,
                'approved_by'  => $challenge->created_by,
                'approved_at'  => now(),
            ]);
        }

        // Beri XP langsung via history + update total_xp
        if ($challenge->reward_xp > 0) {
            \App\Models\EmployeeXpHistory::create([
                'id'          => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'user_id'     => $userId,
                'company_id'  => $challenge->company_id,
                'xp'          => $challenge->reward_xp,
                'source_type' => 'Challenge',
                'source_id'   => $challenge->id,
                'description' => 'Challenge reward: ' . $challenge->name,
            ]);
            \App\Models\User::where('id', $userId)->increment('total_xp', $challenge->reward_xp);
        }

        // Kirim Inbox Notifikasi
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $userId, 
            $challenge->created_by, 
            "Selamat! Anda telah berhasil menyelesaikan challenge <b>{$challenge->name}</b>. Anda mendapatkan +{$challenge->reward_point} Poin dan +{$challenge->reward_xp} XP.", 
            route('challenge.show', $challenge->id)
        );
    }

    /**
     * User Check and Give reward 
     */
    public static function userCheckAndGiveReward(string $userId): void
    {
        $dateString = \Carbon\Carbon::now()->toDateString();
        $challenges = Challenge::whereDate('start_date', '<=', $dateString)
            ->whereHas('challengeUsers', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('status', 'running')
            ->get();

        if(count($challenges) == 0) return;

        foreach ($challenges as $challenge) {
            self::checkAndGiveReward($challenge, $userId);

            if ($challenge->status !== 'finish') {
                $isEnded = $dateString > $challenge->end_date->toDateString();
                
                // Cek apakah semua member (minimal ada 1) sudah mendapatkan reward
                $totalMembers = ChallengeUser::where('challenge_id', $challenge->id)->count();
                $completedMembers = ChallengeUser::where('challenge_id', $challenge->id)->where('reward_given', true)->count();
                $isAllCompleted = $totalMembers > 0 && ($totalMembers === $completedMembers);

                if ($isEnded || $isAllCompleted) {
                    $challenge->update(['status' => 'finish']);
                }
            }
        }
    }

}
