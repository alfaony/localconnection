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
    protected $user;

    public function __construct($user, $company_id, $userId, $start, $end, $today, $sort, $role)
    {
        $this->company_id = $company_id;
        $this->userId = $userId;
        $this->start = $start;
        $this->end = $end;
        $this->today = $today;
        $this->sort = $sort;
        $this->role = $role;
        $this->user = $user;
    }

    public function title(): string
    {
        return 'Presentase Employee Checkin';
    }

    public function view(): View
    {
        $query = User::where('is_checkin', true)
            ->byCompanyAccess($this->user, $this->company_id, $this->role)
            ->with(['employeeCheckings' => function ($query) {
                $query->whereBetween('scheduled_time', [$this->start, $this->end])
                    ->orderBy('scheduled_time');
            }]);

        if ($this->userId) 
        {
            $query->where('id', $this->userId);
        }

        $users = $query->get();

        return view('export.employee_checkin', [
            'users' => $users
        ]);
    }
}