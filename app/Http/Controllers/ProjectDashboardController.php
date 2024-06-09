<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Schemas\ParamSchema;
use App\Models\Vision;
use App\Models\User;
use App\Models\DailyTask;

use App\Helpers\Access;

class ProjectDashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = \Carbon\Carbon::today();
    
        // Get users with their overdue tasks
        $overdueTasks = User::where('company_id',Auth::user()->company_id)->withCount(['dailyTaskAssigns' => function($query) use ($today) 
        {
            $query->where('end_date', '<', $today);
            $query->whereHas('taskStatus', function ($query)
            {
                $query->where('name',ParamSchema::DOING)->orWhere('name',ParamSchema::INREVIEW)->orWhere('name',ParamSchema::TODO)->orWhere('name',ParamSchema::NOTCOMPLATE);
            });
        }])
        ->orderBy('daily_task_assigns_count', 'desc')
        ->get();

        
        // Get users with their tasks due today or upcoming
        $upcomingTasks = User::where('company_id',Auth::user()->company_id)->withCount(['dailyTaskAssigns' => function($query) use ($today) {
            $query->where('end_date', '>=', $today);
            $query->whereHas('taskStatus', function ($query)
            {
                $query->where('name',ParamSchema::DOING)->orWhere('name',ParamSchema::INREVIEW)->orWhere('name',ParamSchema::TODO)->orWhere('name',ParamSchema::NOTCOMPLATE);
            });
        }])
        ->orderBy('daily_task_assigns_count', 'desc')
        ->get();


        $visions = Vision::where('company_id',Auth::user()->company_id)->with(['missions.objectives.keyResults.dailyTasks'])->get();

        return view('report_project_tree.index', compact('visions','overdueTasks','upcomingTasks'));
    }

    public function fetchusertask($userId,$filter)
    {
        $today = \Carbon\Carbon::today();
        
        $query = DailyTask::with('taskStatus')
            ->where('assignment_user_id', $userId)
            ->whereHas('taskStatus', function ($query) {
                $query->where('name', ParamSchema::DOING)
                    ->orWhere('name', ParamSchema::INREVIEW)
                    ->orWhere('name', ParamSchema::TODO)
                    ->orWhere('name', ParamSchema::NOTCOMPLATE);
            });

        if ($filter === 'overdue') {
            $query->where('end_date', '<', $today);
        } elseif ($filter === 'upcoming') {
            $query->where('end_date', '>=', $today);
        }
        
        $tasks = $query->get()->map(function ($task) {
            $url = NULL;
            if (Access::can('show', 'dailytasks')) {
                $url = route('dailytask.show', $task->slug);
            }
            
            $headName = $task->head ? "< ". Str::limit($task->head->name,40) : '';

            return [
                'is_overdue' => $task->isOverdue(),
                'name_show' => $task->nameShow.' '.$headName,
                'task_status' => $task->taskStatus,
                'date_show' => $task->date_show,
                'user_create' => $task->user ? $task->user->name : '',
                'url' => $url,
            ];
        });
        return response()->json($tasks);
    }


}
