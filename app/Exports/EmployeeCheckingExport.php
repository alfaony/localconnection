<?php

namespace App\Exports;

use App\Models\User;
use App\Models\EmployeeChecking;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class EmployeeCheckingExport implements WithMultipleSheets
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

    public function sheets(): array
    {
        return [
            // new EmployeeCheckinSheet($this->user,$this->company_id, $this->userId, $this->start, $this->end, $this->today, $this->sort, $this->role),
            new DayoffSheet($this->company_id, $this->userId, $this->start, $this->end, $this->today, $this->sort, $this->role),
        ];
    }
}
