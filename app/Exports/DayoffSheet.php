<?php

namespace App\Exports;

use App\Models\EmployeeChecking;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
class DayoffSheet implements FromView, WithTitle
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
        return 'List Dayoff';
    }
    
    public function view(): View
    {
        $query = EmployeeChecking::query()
            ->byCompanyJob($this->company_id, $this->role)
            ->where('is_dayoff', true)
            ->when($this->start && $this->end, function ($q) {
                $q->whereBetween('scheduled_time', [$this->start, $this->end]);
            })
            ->when($this->userId, function ($q) 
            {
                $q->where('user_id', $this->userId);
            })
            ->groupBy('user_id', 'scheduled_time') // Kelompokkan berdasarkan tanggal dan user
            ->select('user_id', 'scheduled_time')
            ->distinct()
            ->get();

        return view('export.dayoff', [
            'dayoffs' => $query
        ]);
    }
}
