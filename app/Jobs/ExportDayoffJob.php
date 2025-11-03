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
use App\Models\Dayoff;
use App\Models\User;
use App\Models\DayoffType;
use App\Models\DayoffQuota;
use App\Exports\DayoffExport;
use App\Exports\DayoffMultiSheetExport;
use Carbon\Carbon;

class ExportDayoffJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filters, $fileName, $company_id,$start_date,$end_date, $user, $hrApprovement, $financeApprovement, $exportFormat;

    public function __construct(array $filters, $fileName, $company_id, $user, $hrApprovement, $financeApprovement, $exportFormat, $start_date, $end_date)
    {
        $this->filters = $filters;
        $this->fileName = $fileName;
        $this->company_id = $company_id;
        $this->user = $user;
        $this->hrApprovement = $hrApprovement;
        $this->financeApprovement = $financeApprovement;
        $this->exportFormat = $exportFormat;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function handle()
    {
        try 
        {
            $query = Dayoff::with(['user', 'type']);

            $query->byCompanyJob($this->company_id, $this->user, $this->hrApprovement, $this->financeApprovement);
            
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
    
            $recapData = $this->generateRecap($this->company_id, $this->user, $this->hrApprovement, $this->financeApprovement, $this->start_date, $this->end_date);

            $exportFormat = $this->exportFormat === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
            Excel::store(new DayoffMultiSheetExport($data, $recapData),$this->fileName);
        } catch (\Throwable $th) {
            // throw $th;
            // dd($th);
            Log::error($th->getMessage());
        }
    }

    protected function generateRecap($companyId, $userLogin, $hrApprovement, $financeApprovement, $startDate, $endDate)
    {
        $year = $startDate ? Carbon::parse($startDate)->year : now()->year;

        if($financeApprovement || $hrApprovement)
        {
            $users = User::where('company_id', $companyId)
                        ->where('dayoff_active', true)
                        ->with(['dayoffQuotas.type'])
                        ->get();
        }else
        {
            $users = User::where('company_id', $companyId)
            ->where('id', $userLogin->id)
            ->where('dayoff_active', true)
            ->with(['dayoffQuotas.type'])
            ->get();
        }

        $reports = [];

        foreach ($users as $user) {
            foreach ($user->dayoffQuotas as $quote) 
            {
                $used = Dayoff::where('user_id', $user->id)
                    ->where('dayoff_type_id', $quote->dayoff_type_id)
                    ->whereNotNull('approval_hr_user_id')
                    ->whereNotNull('approval_finance_user_id')
                    ->whereYear('date_start', $year)
                    ->get();

                $monthlyUsage = array_fill(1, 12, 0);

                foreach ($used as $dayoff) {
                    $start = Carbon::parse($dayoff->date_start);
                    $end = Carbon::parse($dayoff->date_end);
                    $period = Carbon::parse($start)->daysUntil($end);

                    foreach ($period as $date) {
                        if ($date->year == $year) {
                            $month = $date->month;
                            $monthlyUsage[$month]++;
                        }
                    }
                }

                $reports[] = [
                    'user' => $user->name,
                    'type' => $quote->type->name ?? '',
                    'quote' => $quote->quota,
                    'remaining' => $quote->quota - $quote->used, // ✔️ hitung manual
                    'used' => array_sum($monthlyUsage),
                    'months' => $monthlyUsage,
                ];
            }
        }

        return $reports;
    }
}
