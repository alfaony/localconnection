<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;
use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;
use App\Helpers\Access;

use App\Models\Project;
use App\Models\Suplier;
use App\Models\Manager;
use App\Models\Employee;
use App\Models\Job;
use App\Models\Quote;
use App\Models\WorkOrder;
use App\Models\Equipment;
use App\Models\Meeting;

use App\Models\Training;
use App\Models\IpRight;
use App\Models\SalesAchievement;
use App\Models\TaskStatus;
use App\Models\DailyTask;
use App\Models\ScheduleOb;
use App\Models\OfficeMedia;
use App\Models\SettingCompany;

// Change to API
use App\Models\User;
use App\Models\Dayoff;
use App\Models\CustomerSubscription;
use App\Models\Software;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth','ip.restriction']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {   
        $schedules = NULL;

        $totalActiveProjects = Project::byCompany(Auth::user()->company_id)->byDateRange()->count() ?? 0;

        // $activeProjectsBudget = Project::byDateRange()->sum('budget') ?? 0;
        $activeProjectsBudget = Project::byCompany(Auth::user()->company_id)->byDateRange()
                                ->join('work_orders', 'projects.work_order_id', '=', 'work_orders.id')
                                ->sum('work_orders.total') ?? 0;

        $totalPurchaseBudget = Suplier::byCompany(Auth::user()->company_id)->byProjectActive()->sum('total_price') ?? 0;

        $activeEmployeeBudget = Manager::byCompany(Auth::user()->company_id)->byProjectActive()->sum('total_job') ?? 0;

        // $totalActiveWorkers = Employee::byActiveEmployee()->count() ?? 0;
        $totalActiveWorkersGet = Job::byCompany(Auth::user()->company_id)->where('end_date','>=',Carbon::now()->format('Y-m-d'))->distinct('user_id')->get();
        $totalActiveWorkers = count($totalActiveWorkersGet) ?? 0 ;

        $totalQuote = Quote::byCompany(Auth::user()->company_id)->count() ?? 0;
        $totalWorkOrder = WorkOrder::byCompany(Auth::user()->company_id)->count() ?? 0;

        // Quote
        $searchQuote = $request->input('search_quote');

        $quotesQuery = Quote::byCompany(Auth::user()->company_id)->doesntHave('workOrder');

        if ($searchQuote) {
            $quotesQuery->where('number_result', 'like', "%{$searchQuote}%");
        }
        $quotesWithoutWorkOrder = $quotesQuery->paginate(10);
        



        $equipments = Equipment::byCompany(Auth::user()->company_id)->where('total_stock', ParamSchema::LIMIT)->get();


        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $todo = TaskStatus::select('id')->where('name',ParamSchema::TODO)->firstOrFail()->id;
        $doing = TaskStatus::select('id')->where('name',ParamSchema::DOING)->firstOrFail()->id;
        $inReivew = TaskStatus::select('id')->where('name',ParamSchema::INREVIEW)->firstOrFail()->id;
        $complate = TaskStatus::select('id')->where('name',ParamSchema::COMPLATE)->firstOrFail()->id;
        $notComplate = TaskStatus::select('id')->where('name',ParamSchema::NOTCOMPLATE)->firstOrFail()->id;

        // Retrieve the date range from the request if provided
        if ($request->has('start_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();       ;
        }

        $trainingPoints = Training::where('user_id', Auth::user()->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('point');

        $ipRightPoints = IpRight::where('user_id', Auth::user()->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('point');

        $salesAchievementPoints = SalesAchievement::where('user_id', Auth::user()->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('points');
        
        $dailyTask = DailyTask::where('assignment_user_id', Auth::user()->id)->whereBetween('submit', [$startDate, $endDate]);

        $dailyTaskPoints = $dailyTask->where('task_status_id', $complate)->sum('point');

        
        $dailyTaskCounts = DailyTask::where('assignment_user_id', Auth::user()->id)
        ->whereBetween('start_date', [$startDate, $endDate])
        ->selectRaw('
            COUNT(CASE WHEN task_status_id = ? THEN 1 END) as dailyTaskTodoCount,
            COUNT(CASE WHEN task_status_id = ? THEN 1 END) as dailyTaskDoingCount,
            COUNT(CASE WHEN task_status_id = ? THEN 1 END) as dailyTaskInreviewCount,
            COUNT(CASE WHEN task_status_id = ? THEN 1 END) as dailyTaskNotCompleteCount,
            COUNT(CASE WHEN task_status_id = ? THEN 1 END) as dailyTaskCompleteCount
        ', [$todo, $doing, $inReivew, $notComplate, $complate])
        ->first();
    
        $dailyTaskTodoCount = $dailyTaskCounts->dailyTaskTodoCount;
        $dailyTasDoingCount = $dailyTaskCounts->dailyTaskDoingCount;
        $dailyTaskInreviewCount = $dailyTaskCounts->dailyTaskInreviewCount;
        $dailyTaskNotComplateCount = $dailyTaskCounts->dailyTaskNotCompleteCount;
        $dailyTaskCompleteCount = $dailyTaskCounts->dailyTaskCompleteCount;

        $dailyTaskCountOverdue = DailyTask::where('assignment_user_id', Auth::user()->id)
        ->whereHas('taskStatus', function ($query)
        {
            $query->where(function($query) 
            {
                $query->where('name',ParamSchema::DOING)->orWhere('name',ParamSchema::INREVIEW)->orWhere('name',ParamSchema::TODO)->orWhere('name',ParamSchema::NOTCOMPLATE);
            });
        })
        ->whereDate('start_date', '<', now())->whereDate('end_date', '<', now())->count()
        ;

        $dailyTaskCountUpcoming = DailyTask::where('assignment_user_id', Auth::user()->id)->whereHas('taskStatus', function ($query)
        {
            $query->where(function($query) 
            {
                $query->where('name',ParamSchema::DOING)->orWhere('name',ParamSchema::INREVIEW)->orWhere('name',ParamSchema::TODO)->orWhere('name',ParamSchema::NOTCOMPLATE);
            });
        })
        ->where('start_date', '>', now())->count()
        ;

        $dailyTaskCountToday = DailyTask::where('assignment_user_id', Auth::user()->id)->whereHas('taskStatus', function ($query)
        {
            $query->where(function($query) 
            {
                $query->where('name',ParamSchema::DOING)->orWhere('name',ParamSchema::INREVIEW)->orWhere('name',ParamSchema::TODO)->orWhere('name',ParamSchema::NOTCOMPLATE);
            });
        })
        ->whereDate('start_date', '<=', now())->whereDate('end_date', '>=', now())->count();
        
        if(Access::can('showScheduleOb','homes'))
        {
            if(Auth::user()->role->name == RoleSchema::OB)
            {
                $schedules = ScheduleOb::byCompany(Auth::user()->company_id)->where('user_id',Auth::user()->id)->with('user', 'shiftingOb')->get();
            }
            else
            {
                $schedules = ScheduleOb::byCompany(Auth::user()->company_id)->with('user', 'shiftingOb')->get();
            }
        }

        return view('home',compact('totalActiveProjects','activeProjectsBudget','totalPurchaseBudget','activeEmployeeBudget','totalActiveWorkers', 'totalQuote', 'totalWorkOrder', 'equipments', 'trainingPoints', 'ipRightPoints', 'salesAchievementPoints', 'dailyTaskPoints', 'dailyTaskCompleteCount', 'dailyTaskCountOverdue', 'dailyTaskCountUpcoming', 'dailyTaskCountToday', 'dailyTaskTodoCount', 'dailyTasDoingCount', 'dailyTaskInreviewCount', 'dailyTaskNotComplateCount', 'quotesWithoutWorkOrder','startDate','endDate','schedules'));
    }

    /**
     * Function task summary untuk Flutter App.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexSummary(Request $request)
    {
        $userId = Auth::id();
        
        try {
            $todo = TaskStatus::where('name', ParamSchema::TODO)->firstOrFail()->id;
            $doing = TaskStatus::where('name', ParamSchema::DOING)->firstOrFail()->id;
            $inReview = TaskStatus::where('name', ParamSchema::INREVIEW)->firstOrFail()->id;
            $complete = TaskStatus::where('name', ParamSchema::COMPLATE)->firstOrFail()->id;
            $notComplete = TaskStatus::where('name', ParamSchema::NOTCOMPLATE)->firstOrFail()->id;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task statuses (TODO, DOING, etc.) not found.'
            ], 500);
        }

        $now = now();
        $todayStart = $now->startOfDay();
        $todayEnd = $now->endOfDay();

        $dailyTaskStatusCounts = DailyTask::where(function ($q) use ($userId) {
                $q->where('assignment_user_id', $userId)
                ->orWhere('user_id', $userId);
            })
            ->selectRaw('
                COUNT(CASE WHEN task_status_id = ? THEN 1 END) as todo,
                COUNT(CASE WHEN task_status_id = ? THEN 1 END) as doing,
                COUNT(CASE WHEN task_status_id = ? THEN 1 END) as in_review,
                COUNT(CASE WHEN task_status_id = ? THEN 1 END) as not_complete,
                COUNT(CASE WHEN task_status_id = ? THEN 1 END) as complete
            ', [$todo, $doing, $inReview, $notComplete, $complete])
            ->first()
            ->toArray();

        $incompleteStatusIds = [$todo, $doing, $inReview, $notComplete];

        $baseQueryIncomplete = DailyTask::where(function ($q) use ($userId) {
                $q->where('assignment_user_id', $userId)
                ->orWhere('user_id', $userId);
            })
            ->whereIn('task_status_id', $incompleteStatusIds);

        $overdueCount = (clone $baseQueryIncomplete)
            ->whereDate('end_date', '<', $todayStart)
            ->count();

        $todayCount = (clone $baseQueryIncomplete)
            ->whereDate('start_date', '<=', $todayEnd)
            ->whereDate('end_date', '>=', $todayStart)
            ->count();

        $upcomingCount = (clone $baseQueryIncomplete)
            ->whereDate('start_date', '>', $todayEnd)
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Daily task summary retrieved successfully.',
            'data' => array_merge($dailyTaskStatusCounts, [
                'overdue' => $overdueCount,
                'today' => $todayCount,
                'upcoming' => $upcomingCount,
            ]),
        ]);
    }

    public function dashboardReport()
    {
        // Cut off date
        $now = Carbon::now();
        $setting = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        $periodStartDay = $setting && $setting['range_start_date'] ? (int) $setting['range_start_date'] : 21;
        
        // Calculate period start and end dates
        if ($now->day >= $periodStartDay) {
            // Current period: dari cutoff day bulan ini sampai (cutoff day - 1) bulan depan
            $startDate = Carbon::create($now->year, $now->month, $periodStartDay)->startOfDay();
            $endDate = Carbon::create($now->year, $now->month, $periodStartDay)->addMonth()->subDay()->endOfDay();
        } else {
            // Previous period: dari cutoff day bulan lalu sampai (cutoff day - 1) bulan ini
            $startDate = Carbon::create($now->year, $now->month, $periodStartDay)->subMonth()->startOfDay();
            $endDate = Carbon::create($now->year, $now->month, $periodStartDay)->subDay()->endOfDay();
        }
        // End cut off date

        $checkins = User::where('is_checkin', true)->withCheckinCounts(Auth::user()->id)->first();
        $dailyTasksQuery = DailyTask::whereHas('taskStatus', function ($query) {
            $query->where('name', ParamSchema::COMPLATE);
        })
        ->where('assignment_user_id', Auth::id());

        $totalTasksComplete = $dailyTasksQuery->count();

        // 2. Total poin dari Task COMPLATE tersebut
        $totalPoints = intval($dailyTasksQuery->sum('point'));

        // $currentScore = $checkins ? round($totalTasksComplete + ($totalPoints * $checkins->point_percentage / 100 )) : 0;
        
        $userId= Auth::user()->id;

        // New Method Currcy Score
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

        $currentScore = $trainings->sum('point') + $ipRights->sum('point') + $salesAchievements->sum('point') + $dailyTasks->sum('point') + $punishmentTasks->sum('point') + $directPoints->sum('point');

        return response()->json([
            'status' => 'success',
            'message' => 'Dashboard report retrieved successfully',
            'data' => [
                'checkin_point_percentage' => $checkins ? $checkins->point_percentage ."%" : 0 ."%",
                'totalTasksComplete' => $totalTasksComplete,
                'totalPoints' => $totalPoints,
                'currentScore' => $currentScore
            ]
        ]);
    }

    public function leaderboard()
    {
        // Get period dates from SettingCompany
        $now = Carbon::now();
        $setting = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        $periodStartDay = $setting && $setting['range_start_date'] ? (int) $setting['range_start_date'] : 21;
        
        // Calculate period start and end dates
        if ($now->day >= $periodStartDay) {
            // Current period: dari cutoff day bulan ini sampai (cutoff day - 1) bulan depan
            $startDate = Carbon::create($now->year, $now->month, $periodStartDay)->startOfDay();
            $endDate = Carbon::create($now->year, $now->month, $periodStartDay)->addMonth()->subDay()->endOfDay();
        } else {
            // Previous period: dari cutoff day bulan lalu sampai (cutoff day - 1) bulan ini
            $startDate = Carbon::create($now->year, $now->month, $periodStartDay)->subMonth()->startOfDay();
            $endDate = Carbon::create($now->year, $now->month, $periodStartDay)->subDay()->endOfDay();
        }

        $complateStatus = TaskStatus::where('name', ParamSchema::COMPLATE)->first();

        $users = User::byCompany(Auth::user()->company_id)->with('role')->get();

        $result = $users->map(function ($user) use ($complateStatus, $startDate, $endDate) {
            // $checkins = User::where('is_checkin', true)->withCheckinCounts($user->id)->first();

            // $totalTasks = DailyTask::where('assignment_user_id', $user->id)
            //     ->where('task_status_id', $complateStatus->id)
            //     ->whereBetween('updated_at', [$startDate, $endDate]) // Filter by period
            //     ->count();

            // $totalPoints = DailyTask::where('assignment_user_id', $user->id)
            //     ->where('task_status_id', $complateStatus->id)
            //     ->whereBetween('updated_at', [$startDate, $endDate]) // Filter by period
            //     ->sum('point');

            // $checkinPercentage = $user->is_checkin ? ($checkins->point_percentage ?? 0) : 0;

            // $currentScore = round($totalTasks + ($totalPoints * $checkinPercentage / 100));

            $userId = $user->id;

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

            $currentScore = $trainings->sum('point') + $ipRights->sum('point') + $salesAchievements->sum('point') + $dailyTasks->sum('point') + $punishmentTasks->sum('point') + $directPoints->sum('point');
            if ($currentScore > 0) {
                return [
                    'name' => $user->name,
                    'currentScore' => $currentScore
                ];
            }
        })->filter()->values();

        $sorted = $result->sortByDesc('currentScore')->values();

        return response()->json([
            'status' => 'success',
            'data' => $sorted
        ]);
    }

    public function overdueRanking()
    {
        $today = Carbon::today();
        $currentUserDivisionIds = Auth::user()->divisions->pluck('id');

        // Ambil 10 user dengan jumlah overdue task terbanyak
        $overdueUsers = User::byCompany(Auth::user()->company_id)->whereHas('divisions', function ($query) use ($currentUserDivisionIds) {
            $query->whereIn('division_id', $currentUserDivisionIds);
        })
        ->select('name')->withCount(['dailyTaskAssigns as overdue_count' => function ($query) use ($today) {
            $query->whereHas('taskStatus', function ($q) {
                $q->whereIn('name', [
                    ParamSchema::BACKLOG,
                    ParamSchema::DOING,
                    ParamSchema::NOTCOMPLATE,
                    ParamSchema::TODO,
                ]);
            })->whereDate('end_date', '<', $today);
        }])
        ->having('overdue_count', '>', 0)
        ->orderByDesc('overdue_count')
        ->get(['id', 'name']);


        $overdueInReviewUsers = User::byCompany(Auth::user()->company_id)->whereHas('divisions', function ($query) use ($currentUserDivisionIds) {
            $query->whereIn('division_id', $currentUserDivisionIds);
        })
        ->select('name')->withCount(['dailyTaskAssigns as overdue_count' => function ($query) use ($today) {
            $query->whereHas('taskStatus', function ($q) {
                $q->whereIn('name', [
                    ParamSchema::INREVIEW,
                ]);
            })->whereDate('end_date', '<', $today);
        }])
        ->having('overdue_count', '>', 0)
        ->orderByDesc('overdue_count')
        ->get(['id', 'name']);

        
        return response()->json([
            'status' => 'success',
            'message' => 'Overdue rankings retrieved successfully',
            'data' => 
            [
                'overdueUsers' => $overdueUsers,
                'overdueInReviewUsers' => $overdueInReviewUsers
            ]
        ]);
    }

    public function listDayoff()
    {
        $today = Carbon::today();

        $cutiToday = Dayoff::with('user', 'type')
            ->where('date_start', '<=', $today)
            ->where('date_end', '>=', $today)
            ->whereNull('rejected_at')
            ->where(function ($query) {
                $query->whereNotNull('approval_finance_user_id')
                      ->orWhereNotNull('approved_finance_at')
                      ->orWhereNotNull('approval_hr_user_id')
                      ->orWhereNotNull('approved_hr_at');
            })
            ->whereHas('user', fn($q) => $q->where('dayoff_active', true))
            ->get();

        $html = view('partials.dayoffs_today_list', compact('cutiToday'))->render();

        return response()->json(['html' => $html]);
    }

    public function meetingAgenda(Request $request)
    {
        $user = Auth::user();
        $scope = $request->get('scope'); // 'today' or 'week'

        $meetings = Meeting::with('participants')
            ->where(function ($query) use ($user) {
                $query->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
                    ->orWhere('user_id', $user->id);
            });

        // Filter berdasarkan scope
        if ($scope === 'today') {
            $meetings->whereDate('start_date', now()->toDateString());
        } elseif ($scope === 'week') {
            $meetings->whereBetween('start_date', [now()->startOfWeek(), now()->endOfWeek()]);
        }

        $meetings = $meetings->orderBy('start_date')->orderBy('start_time')->get();

        return response()->json($meetings);
    }

    public function softwareSharing(Request $request)
    {
        $user = auth()->user();

        // Statistics
        $activeSubscriptions = CustomerSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->count();

        $expiringSoon = CustomerSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->whereBetween('tanggal_expired', [now(), now()->addDays(7)])
            ->count();

        $expiredSubscriptions = CustomerSubscription::where('user_id', $user->id)
            ->where('status', 'expired')
            ->count();

        $totalSoftwares = Software::where('status', 'active')->count();

        // Active Subscriptions
        $mySubscriptions = CustomerSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->with(['software', 'package'])
            ->latest()
            ->get()
            ->map(function($sub) {
                return [
                    'id' => $sub->id,
                    'order_number' => $sub->order_number,
                    'software' => [
                        'id' => $sub->software->id,
                        'nama' => $sub->software->nama_software,
                        'logo_url' => $sub->software->logo_url ? asset('storage/' . $sub->software->logo_url) : null,
                    ],
                    'package' => [
                        'nama' => $sub->package->nama_paket,
                        'durasi' => $sub->package->durasi_hari,
                    ],
                    'tanggal_mulai' => Carbon::parse($sub->tanggal_mulai)->format('d M Y'),
                    'tanggal_expired' => Carbon::parse($sub->tanggal_expired)->format('d M Y'),
                    'days_until_expiry' => $sub->days_until_expiry,
                    'is_expiring_soon' => $sub->isExpiringSoon(7),
                    'status' => $sub->status,
                    'payment_status' => $sub->payment_status,
                    'detail_url' => route('customer-subscription.show', $sub->id),
                    'renew_url' => route('customer-subscription.renew', $sub->id),
                ];
            });

        // Recently Expired
        $recentExpired = CustomerSubscription::where('user_id', $user->id)
            ->where('status', 'expired')
            ->with(['software', 'package'])
            ->latest('tanggal_expired')
            ->take(5)
            ->get()
            ->map(function($sub) {
                return [
                    'id' => $sub->id,
                    'software' => [
                        'id' => $sub->software->id,
                        'nama' => $sub->software->nama_software,
                    ],
                    'package' => [
                        'nama' => $sub->package->nama_paket,
                    ],
                    'tanggal_expired' => Carbon::parse($sub->tanggal_expired)->format('d M Y'),
                    'software_url' => route('customer-software.show', $sub->software->slug),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'active_subscriptions' => $activeSubscriptions,
                    'expiring_soon' => $expiringSoon,
                    'expired_subscriptions' => $expiredSubscriptions,
                    'total_softwares' => $totalSoftwares,
                ],
                'subscriptions' => $mySubscriptions,
                'recent_expired' => $recentExpired,
            ]
        ]);
    }
}