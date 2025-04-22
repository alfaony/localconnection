<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\DayoffType;
use App\Models\DayoffQuota;

class DayoffAddQuotaCommand extends Command
{
    protected $signature = 'dayoff:add-quota {type_code} {jumlah} {year?}';
    protected $description = 'Menambahkan sejumlah kuota ke semua user untuk jenis cuti tertentu';

    public function handle()
    {
        $code = $this->argument('type_code');
        $jumlah = (int) $this->argument('jumlah');
        $year = $this->argument('year') ?? now()->year;

        $type = DayoffType::where('code', $code)->first();

        if (!$type) {
            return $this->error("Jenis cuti `$code` tidak ditemukan.");
        }

        $users = User::all();

        foreach ($users as $user) {
            $quota = DayoffQuota::firstOrCreate([
                'user_id' => $user->id,
                'dayoff_type_id' => $type->id,
                'year' => $year
            ], [
                'quota' => 0,
                'used' => 0
            ]);

            $quota->quota += $jumlah;
            $quota->save();

            $this->info("Ditambahkan +{$jumlah} hari ke {$user->name} untuk jenis {$type->name} ($year)");
        }

        $this->info("Selesai tambah kuota ke semua user.");
    }
}
