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

use Carbon\Carbon;
class ProjectDashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = \Carbon\Carbon::today();
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : NULL ;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : NULL ;

        $divisionId = $request->get('division_id'); // Get the division filter from the request

        $overdueTasksQuery = User::select('id', 'slug', 'name')
            ->where('company_id', Auth::user()->company_id)
            ->withCount([
                'dailyTaskAssigns as daily_task_assigns_count' => function ($query) use ($today, $startDate, $endDate) {
                    // Tetap gunakan kondisi end_date < $today
                    $query->where('end_date', '<', $today);

                    // Jika start_date dan end_date diberikan, tambahkan whereBetween
                    if ($startDate && $endDate) {
                        $query->whereBetween('end_date', [
                            $startDate,
                            $endDate
                        ]);
                    }

                    // Filter berdasarkan status task
                    $query->whereHas('taskStatus', function ($query) {
                        $query->where('name', ParamSchema::DOING)
                            ->orWhere('name', ParamSchema::INREVIEW)
                            ->orWhere('name', ParamSchema::TODO)
                            ->orWhere('name', ParamSchema::NOTCOMPLATE);
                    });
                },
                'dailyTaskAssigns as doing_count' => function ($query) use ($today, $startDate, $endDate) {
                    $query->where('end_date', '<', $today);

                    if ($startDate && $endDate) {
                        $query->whereBetween('end_date', [
                            $startDate,
                            $endDate
                        ]);
                    }

                    $query->whereHas('taskStatus', function ($q) {
                        $q->where('name', ParamSchema::DOING);
                    });
                },
                'dailyTaskAssigns as in_review_count' => function ($query) use ($today, $startDate, $endDate) {
                    $query->where('end_date', '<', $today);

                    if ($startDate && $endDate) {
                        $query->whereBetween('end_date', [
                            $startDate,
                            $endDate
                        ]);
                    }

                    $query->whereHas('taskStatus', function ($q) {
                        $q->where('name', ParamSchema::INREVIEW);
                    });
                },
                'dailyTaskAssigns as todo_count' => function ($query) use ($today, $startDate, $endDate) {
                    $query->where('end_date', '<', $today);

                    if ($startDate && $endDate) {
                        $query->whereBetween('end_date', [
                            $startDate,
                            $endDate
                        ]);
                    }

                    $query->whereHas('taskStatus', function ($q) {
                        $q->where('name', ParamSchema::TODO);
                    });
                },
                'dailyTaskAssigns as not_complate_count' => function ($query) use ($today, $startDate, $endDate) {
                    $query->where('end_date', '<', $today);

                    if ($startDate && $endDate) {
                        $query->whereBetween('end_date', [
                            $startDate,
                            $endDate
                        ]);
                    }

                    $query->whereHas('taskStatus', function ($q) {
                        $q->where('name', ParamSchema::NOTCOMPLATE);
                    });
                }
            ])
            ->orderBy('daily_task_assigns_count', 'desc');



        // Apply division filter if provided
        if ($divisionId) {
            $overdueTasksQuery->whereHas('divisions', function ($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
            });
        }

        $overdueTasks = $overdueTasksQuery->get();

        // Inisialisasi variabel untuk menyimpan total
        $totalCounts = [
            'todo' => 0,
            'doing' => 0,
            'in_review' => 0,
            'not_complate' => 0,
        ];
        
        // Melakukan iterasi atas semua hasil untuk mengagregasi jumlahnya
        foreach ($overdueTasks as $user) {
            $totalCounts['doing'] += $user->doing_count;
            $totalCounts['in_review'] += $user->in_review_count;
            $totalCounts['todo'] += $user->todo_count;
            $totalCounts['not_complate'] += $user->not_complate_count;
        }

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

        $beforeAday = Carbon::now()->subDays(1)->format('d/m/Y');

        return view('report_project_tree.index', compact('visions', 'overdueTasks', 'upcomingTasks', 'divisions','totalCounts','beforeAday','startDate','endDate'));
    }

    public function fetchusertask($userId, $filter, Request $request)
    {
        $today = \Carbon\Carbon::today();
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date') ) :NULL;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date') ) :NULL;
        
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
            if ($startDate && $endDate) 
            {
                $query->whereBetween('end_date', [
                    $startDate,
                    $endDate
                ]);
            }
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
                'main_project' => $task->project ? $task->project->name : '',
                'data_project' => $task->dataProject ? $task->dataProject->title : '',
                'start_date' => $task->start_date, // Add start_date to use it in sorting
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

        // for each status, sort by start_date in descending order
        $sortedTasks = $tasks->groupBy('task_status')->map(function ($group) {
            return $group->sortByDesc('start_date');
        })->flatten(1)->values(); // Reindex the collection after flattening
    
        return response()->json($sortedTasks);
    }
}