<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DayoffType;

class DayoffTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

public function run()
{
    $data = [
        ['name' => 'Cuti Biasa', 'code' => 'biasa', 'is_limited' => true, 'default_quota' => 12],
        ['name' => 'Cuti Sakit', 'code' => 'sakit', 'is_limited' => true, 'default_quota' => 14],
        ['name' => 'Cuti Khusus', 'code' => 'khusus', 'is_limited' => false, 'default_quota' => null],
        ['name' => 'Cuti Bersama', 'code' => 'bersama', 'is_limited' => false, 'default_quota' => null],
    ];

    foreach ($data as $item) {
        DayoffType::updateOrCreate(['code' => $item['code']], $item);
    }
}
}
