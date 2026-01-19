<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerDashboardController extends Controller
{
    public function dashboard(Partner $partner, Request $request)
    {
        $selectedYear = $request->input('year', date('Y'));
        
        $target = $partner->targets()
            ->where('year', $selectedYear)
            ->with(['targetValues.parameterType', 'targetValues.monthlyReports'])
            ->first();

        $availableYears = $partner->targets()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->unique()
            ->toArray();

        if (!$target) {
            return view('partners.dashboard', [
                'partner' => $partner,
                'target' => null,
                'selectedYear' => $selectedYear,
                'availableYears' => $availableYears,
                'chartData' => null,
            ]);
        }

        $chartData = $this->prepareChartData($target);

        return view('partners.dashboard', compact('partner', 'target', 'selectedYear', 'availableYears', 'chartData'));
    }

    private function prepareChartData($target)
    {
        $months = config('partners.months');
        $chartData = [];

        foreach ($target->targetValues as $targetValue) {
            $parameterName = $targetValue->parameterType->name;
            $monthlyData = [];
            $cumulativeData = [];
            $cumulative = 0;

            for ($month = 1; $month <= 12; $month++) {
                $report = $targetValue->monthlyReports()
                    ->where('month', $month)
                    ->where('year', $target->year)
                    ->first();

                $achievement = $report ? (float) $report->achievement_value : 0;
                $monthlyData[] = $achievement;
                
                $cumulative += $achievement;
                $cumulativeData[] = $cumulative;
            }

            $chartData[$parameterName] = [
                'target' => (float) $targetValue->target_value,
                'unit' => $targetValue->parameterType->unit,
                'monthly' => $monthlyData,
                'cumulative' => $cumulativeData,
                'total_achievement' => $cumulative,
                'achievement_percentage' => $targetValue->target_value > 0 
                    ? ($cumulative / $targetValue->target_value) * 100 
                    : 0,
            ];
        }

        return [
            'labels' => array_values($months),
            'parameters' => $chartData,
        ];
    }

    public function api(Partner $partner, Request $request)
    {
        $year = $request->input('year', date('Y'));
        
        $target = $partner->targets()
            ->where('year', $year)
            ->with(['targetValues.parameterType', 'targetValues.monthlyReports'])
            ->first();

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'No target found for this year'
            ], 404);
        }

        $chartData = $this->prepareChartData($target);

        return response()->json([
            'success' => true,
            'data' => $chartData
        ]);
    }
}