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

            // Direct Points received (approved only) - filter by approved_at
            $directPointsReceived = \App\Models\DirectPoint::where('to_user_id', $user->id)
                ->where('status', \App\Models\DirectPoint::STATUS_APPROVED)
                ->whereBetween('approved_at', [$startDate, $endDate])
                ->get()
                ->sum(function($dp) {
                    return $dp->approved_point ?? $dp->point;
                });

            return [
                'name' => $user->name,
                'training_points' => $trainingPoints,
                'ip_right_points' => $ipRightPoints,
                'sales_achievement_points' => $salesAchievementPoints,
                'daily_task_points' => $dailyTaskPoints,
                'punishment_points' => $punishmentPoints,
                'direct_points' => $directPointsReceived,
                'total_points' => $trainingPoints + $ipRightPoints + $salesAchievementPoints + $dailyTaskPoints + $punishmentPoints + $directPointsReceived,
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

            // Direct Points received (approved only) - filter by approved_at
            $directPointsReceived = \App\Models\DirectPoint::where('to_user_id', $user->id)
                ->where('status', \App\Models\DirectPoint::STATUS_APPROVED)
                ->whereBetween('approved_at', [$startDate, $endDate])
                ->get()
                ->sum(function($dp) {
                    return $dp->approved_point ?? $dp->point;
                });

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
                'direct_points' => $directPointsReceived,
                'total_points' => $trainingPoints + $ipRightPoints + $salesAchievementPoints + $dailyTaskPoints + $punishmentPoints + $directPointsReceived,
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

    public function details(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $userId = $request->input('user_id');
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        // Get user info
        $user = User::findOrFail($userId);

        // Get Training details
        $trainings = Training::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get(['name', 'point', 'created_at'])
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'point' => $item->point,
                    'date' => $item->created_at->format('d M Y'),
                ];
            });

        // Get IP Right details
        $ipRights = IpRight::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get(['name', 'point', 'created_at'])
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'point' => $item->point,
                    'date' => $item->created_at->format('d M Y'),
                ];
            });

        // Get Sales Achievement details
        $salesAchievements = SalesAchievement::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get(['period', 'points', 'created_at'])
            ->map(function($item) {
                return [
                    'name' => $item->period,
                    'point' => $item->points,
                    'date' => $item->created_at->format('d M Y'),
                ];
            });

        // Get Daily Task details (non-punishment)
        $dailyTasks = DailyTask::where('assignment_user_id', $userId)
            ->whereHas('statusRecords', function ($query) use ($startDate, $endDate) {
                $query
                    ->whereBetween('date', [$startDate, $endDate])
                    ->whereHas('taskStatus', function ($q) {
                        $q->where('name', ParamSchema::COMPLATE);
                    });
            })
            ->whereDoesntHave('punishmentUser')
            ->get(['name', 'point', 'id'])
            ->map(function($item) use ($startDate, $endDate) {
                $completedDate = $item->statusRecords()
                    ->whereBetween('date', [$startDate, $endDate])
                    ->whereHas('taskStatus', function ($q) {
                        $q->where('name', ParamSchema::COMPLATE);
                    })
                    ->first();
                
                return [
                    'name' => $item->name,
                    'point' => $item->point,
                    'date' => $completedDate ? Carbon::parse($completedDate->date)->format('d M Y') : '-',
                ];
            });

        // Get Punishment Task details
        $punishmentTasks = DailyTask::where('assignment_user_id', $userId)
            ->whereHas('statusRecords', function ($query) use ($startDate, $endDate) {
                $query
                    ->whereBetween('date', [$startDate, $endDate])
                    ->whereHas('taskStatus', function ($q) {
                        $q->where('name', ParamSchema::COMPLATE);
                    });
            })
            ->whereHas('punishmentUser')
            ->get(['name', 'point', 'id'])
            ->map(function($item) use ($startDate, $endDate) {
                $completedDate = $item->statusRecords()
                    ->whereBetween('date', [$startDate, $endDate])
                    ->whereHas('taskStatus', function ($q) {
                        $q->where('name', ParamSchema::COMPLATE);
                    })
                    ->first();
                
                return [
                    'name' => $item->name,
                    'point' => $item->point,
                    'date' => $completedDate ? Carbon::parse($completedDate->date)->format('d M Y') : '-',
                ];
            });

        // Get Direct Points details
        $directPoints = \App\Models\DirectPoint::where('to_user_id', $userId)
            ->where('status', \App\Models\DirectPoint::STATUS_APPROVED)
            ->whereBetween('approved_at', [$startDate, $endDate])
            ->with(['fromUser', 'division'])
            ->get()
            ->map(function($item) {
                return [
                    'name' => 'Direct Point dari ' . $item->fromUser->name . ' (' . $item->division->name . ')',
                    'point' => $item->approved_point ?? $item->point,
                    'date' => $item->approved_at->format('d M Y'),
                ];
        });

        return response()->json([
            'success' => true,
            'user_name' => $user->name,
            'data' => [
                'trainings' => [
                    'items' => $trainings,
                    'total' => $trainings->sum('point'),
                ],
                'ip_rights' => [
                    'items' => $ipRights,
                    'total' => $ipRights->sum('point'),
                ],
                'sales_achievements' => [
                    'items' => $salesAchievements,
                    'total' => $salesAchievements->sum('point'),
                ],
                'daily_tasks' => [
                    'items' => $dailyTasks,
                    'total' => $dailyTasks->sum('point'),
                ],
                'punishment_tasks' => [
                    'items' => $punishmentTasks,
                    'total' => $punishmentTasks->sum('point'),
                ],
                'direct_points' => [
                    'items' => $directPoints,
                    'total' => $directPoints->sum('point'),
                ],
            ],
        ]);
    }

}
