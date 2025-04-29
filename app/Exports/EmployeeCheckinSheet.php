<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class EmployeeCheckinSheet implements FromView, WithTitle
{
    protected $company_id;
    protected $userId;
    protected $start;
    protected $end;
    protected $today;
    protected $sort;
    protected $role;

    public function __construct($company_id, $userId, $start, $end, $today, $sort, $role)
    {
        $this->company_id = $company_id;
        $this->userId = $userId;
        $this->start = $start;
        $this->end = $end;
        $this->today = $today;
        $this->sort = $sort;
        $this->role = $role;
    }

    public function title(): string
    {
        return 'Presentase Employee Checkin';
    }

    public function view(): View
    {
        $users = User::where('is_checkin', true)->withCheckinCountsJob($this->company_id, $this->userId, $this->start, $this->end, $this->today, $this->role)->get();

        $users = $users->map(function ($user) {
            $user->point_percentage = $user->point_percentage;
            return $user;
        })->sortBy([
            ['point_percentage', $this->sort]
        ]);

        return view('export.employee_checkin', [
            'users' => $users
        ]);
    }
}
