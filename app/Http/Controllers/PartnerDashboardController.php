<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerDashboardController extends Controller
{
    public function dashboard(Partner $partner, Request $request)
    {
        $mode = $request->input('mode', 'single'); // 'single' or 'compare'
        $selectedYear = $request->input('year', date('Y'));
        $compareYear = $request->input('compare_year', null);
        
        // Get all available years for this partner
        $availableYears = $partner->targets()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->unique()
            ->toArray();

        // Single year mode
        if ($mode == 'single' || !$compareYear) {
            return $this->singleYearView($partner, $selectedYear, $availableYears);
        }
        
        // Comparison mode
        return $this->comparisonView($partner, $selectedYear, $compareYear, $availableYears);
    }

    private function singleYearView($partner, $selectedYear, $availableYears)
    {
        $target = $partner->targets()
            ->where('year', $selectedYear)
            ->with(['targetValues.parameterType', 'targetValues.monthlyReports'])
            ->first();

        if (!$target) {
            return view('partners.dashboard', [
                'partner' => $partner,
                'target' => null,
                'selectedYear' => $selectedYear,
                'availableYears' => $availableYears,
                'chartData' => null,
                'mode' => 'single',
            ]);
        }

        $chartData = $this->prepareChartData($target);

        return view('partners.dashboard', [
            'partner' => $partner,
            'target' => $target,
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'chartData' => $chartData,
            'mode' => 'single',
        ]);
    }

    private function comparisonView($partner, $year1, $year2, $availableYears)
    {
        $target1 = $partner->targets()
            ->where('year', $year1)
            ->with(['targetValues.parameterType', 'targetValues.monthlyReports'])
            ->first();
            
        $target2 = $partner->targets()
            ->where('year', $year2)
            ->with(['targetValues.parameterType', 'targetValues.monthlyReports'])
            ->first();

        if (!$target1 || !$target2) {
            return view('partners.dashboard-compare', [
                'partner' => $partner,
                'target1' => $target1,
                'target2' => $target2,
                'year1' => $year1,
                'year2' => $year2,
                'availableYears' => $availableYears,
                'chartData' => null,
                'comparisonData' => null,
            ]);
        }

        $chartData1 = $this->prepareChartData($target1);
        $chartData2 = $this->prepareChartData($target2);
        $comparisonData = $this->prepareComparisonData($target1, $target2);

        return view('partners.dashboard-compere', [
            'partner' => $partner,
            'target1' => $target1,
            'target2' => $target2,
            'year1' => $year1,
            'year2' => $year2,
            'availableYears' => $availableYears,
            'chartData1' => $chartData1,
            'chartData2' => $chartData2,
            'comparisonData' => $comparisonData,
        ]);
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

    private function prepareComparisonData($target1, $target2)
    {
        $comparisonData = [];
        
        // Get all unique parameters from both targets
        $allParameters = collect($target1->targetValues)
            ->merge($target2->targetValues)
            ->unique('parameter_type_id')
            ->map(function($tv) {
                return [
                    'id' => $tv->parameter_type_id,
                    'name' => $tv->parameterType->name,
                    'unit' => $tv->parameterType->unit,
                ];
            });

        foreach ($allParameters as $param) {
            $tv1 = $target1->targetValues->firstWhere('parameter_type_id', $param['id']);
            $tv2 = $target2->targetValues->firstWhere('parameter_type_id', $param['id']);

            $target1Value = $tv1 ? $tv1->target_value : 0;
            $target2Value = $tv2 ? $tv2->target_value : 0;
            $achievement1 = $tv1 ? $tv1->getTotalAchievement() : 0;
            $achievement2 = $tv2 ? $tv2->getTotalAchievement() : 0;
            $percentage1 = $tv1 ? $tv1->getAchievementPercentage() : 0;
            $percentage2 = $tv2 ? $tv2->getAchievementPercentage() : 0;

            // Calculate growth
            $targetGrowth = $target1Value > 0 
                ? (($target2Value - $target1Value) / $target1Value) * 100 
                : 0;
            $achievementGrowth = $achievement1 > 0 
                ? (($achievement2 - $achievement1) / $achievement1) * 100 
                : 0;

            $comparisonData[$param['name']] = [
                'unit' => $param['unit'],
                'year1' => [
                    'target' => $target1Value,
                    'achievement' => $achievement1,
                    'percentage' => $percentage1,
                ],
                'year2' => [
                    'target' => $target2Value,
                    'achievement' => $achievement2,
                    'percentage' => $percentage2,
                ],
                'growth' => [
                    'target' => $targetGrowth,
                    'achievement' => $achievementGrowth,
                    'percentage' => $percentage2 - $percentage1,
                ],
            ];
        }

        return $comparisonData;
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