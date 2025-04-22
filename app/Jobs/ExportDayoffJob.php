<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DayoffExport;
use App\Models\Dayoff;
use Carbon\Carbon;

class ExportDayoffJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filters, $fileName, $company_id,$start_date,$end_date;

    public function __construct(array $filters, $fileName, $company_id, $start_date, $end_date)
    {
        $this->filters = $filters;
        $this->fileName = $fileName;
        $this->company_id = $company_id;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function handle()
    {
        try 
        {
            $query = Dayoff::with(['user', 'type']);
    
            if (!empty($this->filters['user_id'])) {
                $query->where('user_id', $this->filters['user_id']);
            }
    
            if (!empty($this->filters['type_id'])) {
                $query->where('dayoff_type_id', $this->filters['type_id']);
            }
    
            if (!empty($this->start_date) && !empty($this->end_date)) {
                $startDate = Carbon::parse($this->start_date);
                $endDate = Carbon::parse($this->end_date);
                $query->where(function ($q) use ($startDate, $endDate) 
                {
                    $q->whereBetween('date_start', [$startDate, $endDate])
                      ->orWhereBetween('date_end', [$startDate, $endDate]);
                });
            }
    
            $data = $query->get();
    
            Excel::store(new DayoffExport($data), 'public/exports/' . $this->fileName);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error($th->getMessage());
        }
    }
}
