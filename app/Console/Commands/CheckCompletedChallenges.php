<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\Inbox;
use App\Helpers\ChallengeProgressHelper;
use Carbon\Carbon;

class CheckCompletedChallenges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:check-completed {--date= : Simulasi tanggal pengecekan (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek semua challenge aktif dan distribusikan reward serta kirim inbox ke user jika selesai';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dateParam = $this->option('date');
        $date = $dateParam ? Carbon::parse($dateParam) : Carbon::now();
        $dateString = $date->toDateString();

        $this->info("Menjalankan pengecekan challenge untuk tanggal: " . $dateString);

        // Ambil challenge yang start_date nya <= tanggal yang dicek dan berstatus running
        // Karena ada kemungkinan challenge selesai sebelum end_date-nya tercapai
        $challenges = Challenge::whereDate('start_date', '<=', $dateString)
            ->where('status', 'running')
            ->get();

        $totalCompleted = 0;

        foreach ($challenges as $challenge) {
            // Ambil peserta yang belum mendapatkan reward
            $challengeUsers = ChallengeUser::where('challenge_id', $challenge->id)
                ->where('reward_given', false)
                ->get();

            foreach ($challengeUsers as $cu) {
                // Hitung progress peserta saat ini
                $current = ChallengeProgressHelper::current($challenge, $cu->user_id);
                
                if ($current >= $challenge->target_count) {
                    // Jika progress sudah mencapai target, berikan reward
                    ChallengeProgressHelper::checkAndGiveReward($challenge, $cu->user_id);
                    $totalCompleted++;
                    $this->info("User {$cu->user_id} telah menyelesaikan challenge '{$challenge->name}'.");
                }
            }

            // Ubah running jadi finish ketika end_datenya sudah melwati atau semua member sudah terpenushi
            if ($challenge->status !== 'finish') {
                $isEnded = $dateString > $challenge->end_date->toDateString();
                
                // Cek apakah semua member (minimal ada 1) sudah mendapatkan reward
                $totalMembers = ChallengeUser::where('challenge_id', $challenge->id)->count();
                $completedMembers = ChallengeUser::where('challenge_id', $challenge->id)->where('reward_given', true)->count();
                $isAllCompleted = $totalMembers > 0 && ($totalMembers === $completedMembers);

                if ($isEnded || $isAllCompleted) {
                    $challenge->update(['status' => 'finish']);
                    $this->info("Challenge '{$challenge->name}' telah selesai sepenuhnya dan status diubah menjadi finish.");
                }
            }
        }

        $this->info("Check completed. Total challenge baru yang diselesaikan: {$totalCompleted}");
        return Command::SUCCESS;
    }
}
