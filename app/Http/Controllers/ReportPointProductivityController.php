<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $allUsers = User::byCompany(Auth::user()->company_id)->get();

        // Retrieve the date range from the request if provided
        if ($request->has('start_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $endDate = Carbon::parse($request->input('end_date'));
        }

        $query = User::query();
        if ($request->has('user_id') && $request->input('user_id') != '') 
        {
            $query->where('id', $request->input('user_id'));
        }
        // Retrieve all users
        $users = $query->byCompany(Auth::user()->company_id)->paginate(10);

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

            $dailyTaskPoints = DailyTask::where('assignment_user_id', $user->id)
                ->whereBetween('submit', [$startDate, $endDate])
                ->where('task_status_id', $complate)
                ->sum('point');

            return [
                'name' => $user->name,
                'training_points' => $trainingPoints,
                'ip_right_points' => $ipRightPoints,
                'sales_achievement_points' => $salesAchievementPoints,
                'daily_task_point' => $dailyTaskPoints,
                'total_points' => $trainingPoints + $ipRightPoints + $salesAchievementPoints + $dailyTaskPoints,
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
}
