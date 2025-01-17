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

use App\Models\Training;
use App\Models\IpRight;
use App\Models\SalesAchievement;
use App\Models\TaskStatus;
use App\Models\DailyTask;
use App\Models\ScheduleOb;

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
}
