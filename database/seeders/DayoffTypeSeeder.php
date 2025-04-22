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
    $data = 
    [
        ['name' => 'Cuti Biasa', 'is_limited' => true, 'default_quota' => 12],
        ['name' => 'Cuti Sakit', 'is_limited' => true, 'permission_required' => true, 'default_quota' => 14],
        ['name' => 'Cuti Khusus', 'is_limited' => false, 'default_quota' => null],
        ['name' => 'Cuti Bersama', 'is_limited' => false, 'default_quota' => null],
    ];

    foreach ($data as $item) {
        DayoffType::updateOrCreate(['name' => $item['name']], $item);
    }
}
}
