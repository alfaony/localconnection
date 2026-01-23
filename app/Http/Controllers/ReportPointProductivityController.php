<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Jobs\ExportReportPointProductivityJob;

use Carbon\Carbon;
use App\Schemas\ParamSchema;

use App\Models\Training;
use App\Models\IpRight;
use App\Models\SalesAchievement;
use App\Models\User;
use App\Models\TaskStatus;
use App\Models\DailyTask;

class ReportPointProductivityController extends Controller
{
    public function index(Request $request)
    {
        // Set default date range from the start of the current month to today
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $allUsers = User::isActive()->byCompany(Auth::user()->company_id)->get();

        // Retrieve the date range from the request if provided
        if ($request->has('start_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();       ;
        }

        $query = User::query();
        if ($request->has('user_id') && $request->input('user_id') != '' && $request->input('user_id') != "all_user_checkin") 
        {
            $query->where('id', $request->input('user_id'));
        }
        elseif ($request->input('user_id') === 'all_user_checkin') {
            // Show all users who checked in
            $query->where(function($q){
                $q->where('wfo_check_in', true)
                ->orWhere('is_checkin', true);
            });
        }

        // Retrieve all users
        $users = $query->isActive()->byCompany(Auth::user()->company_id)->paginate(10);

        $complate = TaskStatus::select('id')->where('name',ParamSchema::COMPLATE)->firstOrFail()->id;
        // Map the user data to include points from each model within the date range
        $reports = $users->map(function ($user) use ($startDate, $endDate, $complate) {
            $trainingPoints = Training::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('point');

            $ipRightPoints = IpRight::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('point');

            $salesAchievementPoints = SalesAchievement::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('points');

           // Regular daily task points (NOT in punishment_users)
            $dailyTaskPoints = DailyTask::where('assignment_user_id', $user->id)
                ->whereHas('statusRecords', function ($query) use ($startDate, $endDate) {
                    $query
                        ->whereBetween('date', [$startDate, $endDate])
                        ->whereHas('taskStatus', function ($q) {
                            $q->where('name', ParamSchema::COMPLATE);
                        });
                })
                ->whereDoesntHave('punishmentUser')
                ->sum('point');

            // Punishment points (IN punishment_users)
            $punishmentPoints = DailyTask::where('assignment_user_id', $user->id)
                ->whereHas('statusRecords', function ($query) use ($startDate, $endDate) {
                    $query
                        ->whereBetween('date', [$startDate, $endDate])
                        ->whereHas('taskStatus', function ($q) {
                            $q->where('name', ParamSchema::COMPLATE);
                        });
                })
                ->whereHas('punishmentUser')
                ->sum('point');

            return [
                'name' => $user->name,
                'training_points' => $trainingPoints,
                'ip_right_points' => $ipRightPoints,
                'sales_achievement_points' => $salesAchievementPoints,
                'daily_task_points' => $dailyTaskPoints,
                'punishment_points' => $punishmentPoints,
                'total_points' => $trainingPoints + $ipRightPoints + $salesAchievementPoints + $dailyTaskPoints + $punishmentPoints,
            ];
        });

        return view('report_point_productivity.index', [
            'reports' => $reports,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'users' => $users,
            'allUsers' => $allUsers
        ]);
    }

    public function export(Request $request)
    {
        // Set default date range
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Retrieve the date range from the request if provided
        if ($request->has('start_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        }

        $query = User::query();
        if ($request->has('user_id') && $request->input('user_id') != '' && $request->input('user_id') != "all_user_checkin") 
        {
            $query->where('id', $request->input('user_id'));
        }
        elseif ($request->input('user_id') === 'all_user_checkin') {
            // Show all users who checked in
            $query->where(function($q){
                $q->where('wfo_check_in', true)
                ->orWhere('is_checkin', true);
            });
        }

        // Retrieve all users (tanpa pagination untuk export)
        $users = $query->byCompany(Auth::user()->company_id)->get();

        // Map the user data
        $reports = $users->map(function ($user) use ($startDate, $endDate) {
            $trainingPoints = Training::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('point');

            $ipRightPoints = IpRight::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('point');

            $salesAchievementPoints = SalesAchievement::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('points');

            // Regular daily task points (NOT in punishment_users)
            $dailyTaskPoints = DailyTask::where('assignment_user_id', $user->id)
                ->whereHas('statusRecords', function ($query) use ($startDate, $endDate) {
                    $query
                        ->whereBetween('date', [$startDate, $endDate])
                        ->whereHas('taskStatus', function ($q) {
                            $q->where('name', ParamSchema::COMPLATE);
                        });
                })
                ->whereDoesntHave('punishmentUser')
                ->sum('point');

            // Punishment points (IN punishment_users)
            $punishmentPoints = DailyTask::where('assignment_user_id', $user->id)
                ->whereHas('statusRecords', function ($query) use ($startDate, $endDate) {
                    $query
                        ->whereBetween('date', [$startDate, $endDate])
                        ->whereHas('taskStatus', function ($q) {
                            $q->where('name', ParamSchema::COMPLATE);
                        });
                })
                ->whereHas('punishmentUser')
                ->sum('point');
            $divisions = $user->divisions->isNotEmpty()
            ? $user->divisions->pluck('name')->implode(', ')
            : '-';

            return [
                'company' => $user->company ? $user->company->name : '',
                'division' => $divisions,
                'name' => $user->name,
                'training_points' => $trainingPoints,
                'ip_right_points' => $ipRightPoints,
                'sales_achievement_points' => $salesAchievementPoints,
                'daily_task_points' => $dailyTaskPoints,
                'punishment_points' => $punishmentPoints,
                'total_points' => $trainingPoints + $ipRightPoints + $salesAchievementPoints + $dailyTaskPoints + $punishmentPoints,
            ];
        });

        // Dispatch  job
        ExportReportPointProductivityJob::dispatch(
            $reports->toArray(),
            $startDate,
            $endDate,
            Auth::user()
        );

        return redirect()->back()->with('success', 'Export sedang diproses. Anda akan menerima notifikasi setelah selesai.');
    }

}
