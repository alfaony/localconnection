<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\SettingCompany;

class QuotaHelper
{
    /**
     * Get current quota period (month and year)
     * 
     * @return array ['month' => int, 'year' => int]
     */
    public static function getCurrentPeriod($company_id)
    {
        $now = Carbon::now();
        $setting = SettingCompany::byCompany($company_id)->get()->pluck('field_value','field_title');
        $periodStartDay = $setting && $setting['range_start_date'] ? (int) $setting['range_start_date'] : 21;
        
        if ($now->day >= $periodStartDay) {
            return [
                'month' => $now->month,
                'year' => $now->year,
            ];
        } else {
            return [
                'month' => $now->copy()->subMonth()->month,
                'year' => $now->copy()->subMonth()->year,
            ];
        }
    }
    
    /**
     * Get period start and end dates
     * 
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    public static function getPeriodDates($company_id)
    {
        $now = Carbon::now();
        $setting = SettingCompany::first();
        $setting = SettingCompany::byCompany($company_id)->get()->pluck('field_value','field_title');
        $periodStartDay = $setting && $setting['range_start_date'] ? (int) $setting['range_start_date'] : 21;
        
        if ($now->day >= $periodStartDay) {
            $start = Carbon::create($now->year, $now->month, $periodStartDay)->startOfDay();
            $end = Carbon::create($now->year, $now->month, $periodStartDay)->addMonth()->subDay()->endOfDay();
        } else {
            $start = Carbon::create($now->year, $now->month, $periodStartDay)->subMonth()->startOfDay();
            $end = Carbon::create($now->year, $now->month, $periodStartDay)->subDay()->endOfDay();
        }
        
        return [
            'start' => $start,
            'end' => $end,
        ];
    }
}