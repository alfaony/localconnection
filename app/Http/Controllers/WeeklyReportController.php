<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\WeeklyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class WeeklyReportController extends Controller
{
    public function index()
    {
        $reports = WeeklyReport::with(['division'])
            ->where('user_id', auth()->id())
            ->orderByDesc('year')
            ->orderByDesc('week')
            ->paginate(10);

        return view('weekly_report.index', compact('reports'));
    }

    public function create()
    {
        $userDivisions = auth()->user()->divisions;

        return view('weekly_report.createOrEdit', [
            'mode' => 'create',
            'report' => null,
            'userDivisions' => $userDivisions,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'division_id' => 'required|uuid|exists:divisions,id',
            'file' => 'nullable|mimes:pdf|max:2048', // max 2MB
            // 'date' => 'required|date',
            'key_activities' => 'nullable|string',
            'problems' => 'nullable|string',
            'targets' => 'nullable|string',
            'number_of_customers' => 'nullable|integer|min:0',
            'number_of_users' => 'nullable|integer|min:0',
            'number_of_products' => 'nullable|integer|min:0',
            'number_of_projects' => 'nullable|integer|min:0',
            'number_of_homepasses' => 'nullable|integer|min:0',
            'number_of_leads' => 'nullable|integer|min:0',
            'number_of_views' => 'nullable|integer|min:0',
            'number_of_profit' => 'nullable|integer|min:0',
        ]);

        $date = Carbon::now();
        $year = $date->year;
        $week = $date->isoWeek();

        $exists = WeeklyReport::where('user_id', auth()->id())
            ->where('division_id', $request->division_id)
            ->where('year', $year)
            ->where('week', $week)
            ->exists();

        if ($exists) {
            return back()->withErrors(['date' => 'Laporan untuk minggu ini sudah ada.'])->withInput();
        }

        $path = null;
        if ($request->hasFile('file')) 
        {
            $path = $request->file('file')->store('weekly_reports', 'public');
        }

        WeeklyReport::create([
            'user_id' => auth()->id(),
            'division_id' => $request->division_id,
            'file' => $path,
            'date' => $date,
            'year' => $year,
            'week' => $week,
            'key_activities' => $request->key_activities,
            'problems' => $request->problems,
            'targets' => $request->targets,
            'number_of_customers' => $request->number_of_customers,
            'number_of_users' => $request->number_of_users,
            'number_of_products' => $request->number_of_products,
            'number_of_projects' => $request->number_of_projects,
            'number_of_homepasses' => $request->number_of_homepasses,
            'number_of_leads' => $request->number_of_leads,
            'number_of_views' => $request->number_of_views,
            'number_of_profit' => $request->number_of_profit,
        ]);

        return redirect()->route('weekly-report.index')->with('store', true);
    }

    public function edit(WeeklyReport $weeklyReport)
    {
        $this->authorize('update', $weeklyReport); // optional: jika pakai policy

        return view('weekly_report.createOrEdit', [
            'mode' => 'edit',
            'report' => $weeklyReport,
            'userDivisions' => auth()->user()->divisions,
        ]);
    }

    public function update(Request $request, WeeklyReport $weeklyReport)
    {
        $this->authorize('update', $weeklyReport);

        $request->validate([
            'division_id' => 'required|uuid|exists:divisions,id',
            'date' => 'required|date',
            'key_activities' => 'nullable|string',
            'problems' => 'nullable|string',
            'targets' => 'nullable|string',
            'number_of_customers' => 'nullable|integer|min:0',
            'number_of_users' => 'nullable|integer|min:0',
            'number_of_products' => 'nullable|integer|min:0',
            'number_of_projects' => 'nullable|integer|min:0',
            'number_of_homepasses' => 'nullable|integer|min:0',
            'number_of_leads' => 'nullable|integer|min:0',
            'number_of_views' => 'nullable|integer|min:0',
            'number_of_profit' => 'nullable|integer|min:0',
        ]);

        $date = Carbon::parse($request->date);
        $year = $date->year;
        $week = $date->isoWeek();

        // optional: validasi prevent duplicate minggu update
        $exists = WeeklyReport::where('user_id', auth()->id())
            ->where('division_id', $request->division_id)
            ->where('year', $year)
            ->where('week', $week)
            ->where('id', '!=', $weeklyReport->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['date' => 'Laporan untuk minggu ini sudah ada.'])->withInput();
        }

        $weeklyReport->update([
            'division_id' => $request->division_id,
            'date' => $request->date,
            'year' => $year,
            'week' => $week,
            'key_activities' => $request->key_activities,
            'problems' => $request->problems,
            'targets' => $request->targets,
            'number_of_customers' => $request->number_of_customers,
            'number_of_users' => $request->number_of_users,
            'number_of_products' => $request->number_of_products,
            'number_of_projects' => $request->number_of_projects,
            'number_of_homepasses' => $request->number_of_homepasses,
            'number_of_leads' => $request->number_of_leads,
            'number_of_views' => $request->number_of_views,
            'number_of_profit' => $request->number_of_profit,
        ]);

        return redirect()->route('weekly-report.index')->with('success', 'Laporan berhasil diperbarui.');
    }

    public function destroy(WeeklyReport $weeklyReport)
    {
        $this->authorize('delete', $weeklyReport); // optional policy
        $weeklyReport->delete();

        return redirect()->route('weekly-report.index')->with('success', 'Laporan berhasil dihapus.');
    }
}
