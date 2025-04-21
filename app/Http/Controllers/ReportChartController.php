<?php

namespace App\Http\Controllers;

use App\Models\WeeklyReport;
use App\Models\Division;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportChartController extends Controller
{
    public function index()
    {
        $divisions = auth()->user()->divisions()->orderBy('name')->get();
        return view('weekly_report.chart', compact('divisions'));
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        $divisionId = $request->division_id;

        $request->validate([
            'division_id' => 'required|uuid|exists:divisions,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        // Default date range: bulan ini
        $start = $request->start_date ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $end = $request->end_date ? Carbon::parse($request->end_date) : now()->endOfMonth();

        $reports = WeeklyReport::where('user_id', $user->id)
            ->where('division_id', $divisionId)
            ->whereBetween('date', [$start, $end])
            ->get();

        $period = CarbonPeriod::create(Carbon::parse($start)->startOfWeek(), Carbon::parse($end)->endOfWeek());

            $weeks = collect($period)->map(function ($date) {
                return [
                    'year' => $date->year,
                    'week' => $date->isoWeek(),
                    'label' => $date->year . '-W' . str_pad($date->isoWeek(), 2, '0', STR_PAD_LEFT)
                ];
            })->unique('label')->values();

        $fields = [
            'number_of_customers',
            'number_of_users',
            'number_of_products',
            'number_of_projects',
            'number_of_homepasses',
            'number_of_leads',
            'number_of_views',
            'number_of_profit',
        ];

        $datasets = [];
        foreach ($fields as $field) {
            $datasets[$field] = [];
        }

        foreach ($weeks as $week) {
            foreach ($fields as $field) {
                $value = $reports->firstWhere('week', $week['week'])?->$field ?? 0;
                $datasets[$field][] = $value;
            }
        }

        return response()->json([
            'labels' => $weeks->pluck('label'),
            'datasets' => $datasets,
            'details' => $weeks->map(function ($week) use ($reports) {
                $report = $reports->firstWhere('week', $week['week']);
                return [
                    'week_label' => $week['label'],
                    'key_activities' => $report?->key_activities,
                    'problems' => $report?->problems,
                    'targets' => $report?->targets,
                ];
            }),
        ]);
    }
}
