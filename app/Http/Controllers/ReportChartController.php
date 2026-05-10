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

        $reports = WeeklyReport::where('division_id', $divisionId)
            ->whereBetween('date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->get();

        $period = CarbonPeriod::create(Carbon::parse($start)->startOfWeek(), Carbon::parse($end)->endOfWeek());

        $weeks = collect($period)->map(function ($date) {
            return [
                'year' => $date->isoWeekYear(),
                'week' => $date->isoWeek(),
                'label' => $date->isoWeekYear() . '-W' . str_pad($date->isoWeek(), 2, '0', STR_PAD_LEFT)
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
                $value = $reports->first(fn($r) => $r->week == $week['week'] && $r->year == $week['year'])?->$field ?? 0;
                $datasets[$field][] = $value;
            }
        }

        return response()->json([
            'labels' => $weeks->pluck('label'),
            'datasets' => $datasets,
            'details' => $weeks->map(function ($week) use ($reports) {
                $report = $reports->first(fn($r) => $r->week == $week['week'] && $r->year == $week['year']);
                return [
                    'week_label' => $week['label'],
                    'key_activities' => $report?->key_activities,
                    'problems' => $report?->problems,
                    'targets' => $report?->targets,
                    'number_of_customers' => $report?->number_of_customers ?? 0,
                    'number_of_users' => $report?->number_of_users ?? 0,
                    'number_of_products' => $report?->number_of_products ?? 0,
                    'number_of_projects' => $report?->number_of_projects ?? 0,
                    'number_of_homepasses' => $report?->number_of_homepasses ?? 0,
                    'number_of_leads' => $report?->number_of_leads ?? 0,
                    'number_of_views' => $report?->number_of_views ?? 0,
                    'number_of_profit' => $report?->number_of_profit ?? 0,
                ];
            }),
        ]);
    }
}
