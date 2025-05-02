<?php

namespace App\Jobs;

use App\Exports\EmployeeCheckingExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EmployeeCheckingExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $exportFormat;
    protected $company_id;
    protected $userId;
    protected $start;
    protected $end;
    protected $today;
    protected $sort;
    protected $role;

    public function __construct($filePath, $exportFormat, $company_id, $userId, $start, $end, $today, $sort, $role)
    {
        $this->filePath = $filePath;
        $this->exportFormat = $exportFormat;
        $this->company_id = $company_id;
        $this->userId = $userId;
        $this->start = $start;
        $this->end = $end;
        $this->today = $today;
        $this->sort = $sort;
        $this->role = $role;
    }

    public function handle()
    {
        $exportFormat = $this->exportFormat === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        try {
            Excel::store(new EmployeeCheckingExport($this->company_id, $this->userId, $this->start, $this->end, $this->today, $this->sort, $this->role), $this->filePath, "public", $exportFormat);
        } catch (\Exception $e) {
            // dd($e);
            Log::error("Error storing file: " . $e->getMessage());
        }
    }
}
