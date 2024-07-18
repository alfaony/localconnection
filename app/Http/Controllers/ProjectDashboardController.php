<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Schemas\ParamSchema;
use App\Models\Vision;
use App\Models\User;
use App\Models\DailyTask;
use App\Models\Division; // Add this line to import Division model

use App\Helpers\Access;

class ProjectDashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = \Carbon\Carbon::today();
        $divisionId = $request->get('division_id'); // Get the division filter from the request

        // Get users with their overdue tasks
        $overdueTasksQuery = User::where('company_id', Auth::user()->company_id)
            ->withCount(['dailyTaskAssigns' => function ($query) use ($today) {
                $query->where('end_date', '<', $today);
                $query->whereHas('taskStatus', function ($query) {
                    $query->where('name', ParamSchema::DOING)
                        ->orWhere('name', ParamSchema::INREVIEW)
                        ->orWhere('name', ParamSchema::TODO)
                        ->orWhere('name', ParamSchema::NOTCOMPLATE);
                });
            }])
            ->orderBy('daily_task_assigns_count', 'desc');

        // Apply division filter if provided
        if ($divisionId) {
            $overdueTasksQuery->whereHas('divisions', function ($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
            });
        }

        $overdueTasks = $overdueTasksQuery->get();

        // Get users with their tasks due today or upcoming
        $upcomingTasksQuery = User::where('company_id', Auth::user()->company_id)
            ->withCount(['dailyTaskAssigns' => function ($query) use ($today) {
                $query->where('end_date', '>=', $today);
                $query->whereHas('taskStatus', function ($query) {
                    $query->where('name', ParamSchema::DOING)
                        ->orWhere('name', ParamSchema::INREVIEW)
                        ->orWhere('name', ParamSchema::TODO)
                        ->orWhere('name', ParamSchema::NOTCOMPLATE);
                });
            }])
            ->orderBy('daily_task_assigns_count', 'desc');

        // Apply division filter if provided
        if ($divisionId) {
            $upcomingTasksQuery->whereHas('divisions', function ($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
            });
        }

        $upcomingTasks = $upcomingTasksQuery->get();

        $visions = Vision::where('company_id', Auth::user()->company_id)
            ->with(['missions.objectives.keyResults.dailyTasks'])
            ->get();

        $user = Auth::user();
        $divisions = $user->divisions()->paginate(10);

        return view('report_project_tree.index', compact('visions', 'overdueTasks', 'upcomingTasks', 'divisions'));
    }

    public function fetchusertask($userId, $filter)
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
            
            $headName = $task->head ? "< ". Str::limit($task->head->name, 40) : '';

            return [
                'is_overdue' => $task->isOverdue(),
                'name_show' => $task->nameShow.' '.$headName,
                'task_status' => $task->taskStatus->name, // Change to 'name' to simplify sorting
                'date_show' => $task->date_show,
                'user_create' => $task->user ? $task->user->name : '',
                'user_assign' => $task->assign ? $task->assign->name : '',
                'url' => $url,
            ];
        });

        // Sort the tasks by status
        $tasks = $tasks->sortBy(function ($task) {
            switch ($task['task_status']) {
                case ParamSchema::DOING:
                    return 1;
                case ParamSchema::TODO:
                    return 2;
                case ParamSchema::NOTCOMPLATE:
                    return 3;
                case ParamSchema::INREVIEW:
                    return 4; // If you want to handle 'not complete' tasks as well
                default:
                    return 5; // Default sorting for any other statuses
            }
        })->values(); // Reindex the collection after sorting

        return response()->json($tasks);
    }
}