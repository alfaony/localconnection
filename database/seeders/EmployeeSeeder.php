<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userUuid = User::first();

        for ($i = 1; $i <= 20; $i++) 
        {
            $employee = new Employee();
            $employee->user_id = $userUuid->id;
            $employee->name = 'Employee ' . $i;
            $employee->slug = 'Employee ' . $i;
            $employee->phone = '08564247778'.$i;
            $employee->salary_monthly = rand(1000, 5000);
            $employee->salary_daily = rand(50, 200);
            $employee->save();
        }
    }
}
