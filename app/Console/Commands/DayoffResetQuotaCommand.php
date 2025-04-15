<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\DayoffType;
use App\Models\DayoffQuota;

class DayoffResetQuotaCommand extends Command
{
    protected $signature = 'dayoff:reset-quota';
    protected $description = 'Reset kuota semua user berdasarkan default_quota dari dayoff_types';

    public function handle()
    {
        $types = DayoffType::where('is_limited', true)->get();
        $users = User::where('dayoff_active',true)->all();

        foreach ($users as $user) {
            foreach ($types as $type) {
                $quota = DayoffQuota::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'dayoff_type_id' => $type->id,
                    ],
                    [
                        'quota' => $type->default_quota,
                        'used' => 0,
                    ]
                );

                $this->info("Kuota {$type->code} di-set untuk {$user->name} : {$type->default_quota} hari");
            }
        }

        $this->info("Selesai reset kuota untuk tahun ");
    }
}