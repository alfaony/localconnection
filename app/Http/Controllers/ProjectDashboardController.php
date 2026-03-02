<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Schemas\ParamSchema;
use App\Models\Vision;
use App\Models\Mission;
use App\Models\Objective;
use App\Models\ObjectiveKeyResult;
use App\Models\User;
use App\Models\DailyTask;
use App\Models\Division; // Add this line to import Division model

use App\Helpers\Access;

use Carbon\Carbon;
class ProjectDashboardController extends Controller
{
    // public function index(Request $request)
    // {
    //     $today = \Carbon\Carbon::today();
        // $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : NULL ;
        // $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : NULL ;
        // $defaultDivisi = Auth::user()->FirstDivision ? Auth::user()->FirstDivision->id : NULL;
    //     $divisionId = $request->get('division_id') ?? $defaultDivisi;  // Get the division filter from the request
    //     $overdueTasksQuery = User::select('id', 'slug', 'name')
    //         ->where('company_id', Auth::user()->company_id)
    //         ->withCount([
    //             'dailyTaskAssigns as daily_task_assigns_count' => function ($query) use ($today, $startDate, $endDate) {
    //                 // Tetap gunakan kondisi end_date < $today
    //                 $query->where('end_date', '<', $today);

    //                 // Jika start_date dan end_date diberikan, tambahkan whereBetween
    //                 if ($startDate && $endDate) {
    //                     $query->whereBetween('end_date', [
    //                         $startDate,
    //                         $endDate
    //                     ]);
    //                 }

    //                 // Filter berdasarkan status task
    //                 $query->whereHas('taskStatus', function ($query) {
    //                     $query->where('name', ParamSchema::DOING)
    //                         ->orWhere('name', ParamSchema::INREVIEW)
    //                         ->orWhere('name', ParamSchema::TODO)
    //                         ->orWhere('name', ParamSchema::NOTCOMPLATE);
    //                 });
    //             },
    //             'dailyTaskAssigns as doing_count' => function ($query) use ($today, $startDate, $endDate) {
    //                 $query->where('end_date', '<', $today);

    //                 if ($startDate && $endDate) {
    //                     $query->whereBetween('end_date', [
    //                         $startDate,
    //                         $endDate
    //                     ]);
    //                 }

    //                 $query->whereHas('taskStatus', function ($q) {
    //                     $q->where('name', ParamSchema::DOING);
    //                 });
    //             },
    //             'dailyTaskAssigns as in_review_count' => function ($query) use ($today, $startDate, $endDate) {
    //                 $query->where('end_date', '<', $today);

    //                 if ($startDate && $endDate) {
    //                     $query->whereBetween('end_date', [
    //                         $startDate,
    //                         $endDate
    //                     ]);
    //                 }

    //                 $query->whereHas('taskStatus', function ($q) {
    //                     $q->where('name', ParamSchema::INREVIEW);
    //                 });
    //             },
    //             'dailyTaskAssigns as todo_count' => function ($query) use ($today, $startDate, $endDate) {
    //                 $query->where('end_date', '<', $today);

    //                 if ($startDate && $endDate) {
    //                     $query->whereBetween('end_date', [
    //                         $startDate,
    //                         $endDate
    //                     ]);
    //                 }

    //                 $query->whereHas('taskStatus', function ($q) {
    //                     $q->where('name', ParamSchema::TODO);
    //                 });
    //             },
    //             'dailyTaskAssigns as not_complate_count' => function ($query) use ($today, $startDate, $endDate) {
    //                 $query->where('end_date', '<', $today);

    //                 if ($startDate && $endDate) {
    //                     $query->whereBetween('end_date', [
    //                         $startDate,
    //                         $endDate
    //                     ]);
    //                 }

    //                 $query->whereHas('taskStatus', function ($q) {
    //                     $q->where('name', ParamSchema::NOTCOMPLATE);
    //                 });
    //             }
    //         ])
    //         ->orderBy('daily_task_assigns_count', 'desc');



    //     // Apply division filter if provided
    //     if ($divisionId) {
    //         $overdueTasksQuery->whereHas('divisions', function ($query) use ($divisionId) {
    //             $query->where('division_id', $divisionId);
    //         });
    //     }

    //     $overdueTasks = $overdueTasksQuery->get();
        

    //     // Inisialisasi variabel untuk menyimpan total
    //     $totalCounts = [
    //         'todo' => 0,
    //         'doing' => 0,
    //         'in_review' => 0,
    //         'not_complate' => 0,
    //     ];
        
    //     // Melakukan iterasi atas semua hasil untuk mengagregasi jumlahnya
    //     foreach ($overdueTasks as $user) {
    //         $totalCounts['doing'] += $user->doing_count;
    //         $totalCounts['in_review'] += $user->in_review_count;
    //         $totalCounts['todo'] += $user->todo_count;
    //         $totalCounts['not_complate'] += $user->not_complate_count;
    //     }

    //     // Get users with their tasks due today or upcoming
    //     $upcomingTasksQuery = User::where('company_id', Auth::user()->company_id)
    //         ->withCount(['dailyTaskAssigns' => function ($query) use ($today) {
    //             $query->where('end_date', '>=', $today);
    //             $query->whereHas('taskStatus', function ($query) {
    //                 $query->where('name', ParamSchema::DOING)
    //                     ->orWhere('name', ParamSchema::INREVIEW)
    //                     ->orWhere('name', ParamSchema::TODO)
    //                     ->orWhere('name', ParamSchema::NOTCOMPLATE);
    //             });
    //         }])
    //         ->orderBy('daily_task_assigns_count', 'desc');

    //     // Apply division filter if provided
    //     if ($divisionId) {
    //         $upcomingTasksQuery->whereHas('divisions', function ($query) use ($divisionId) {
    //             $query->where('division_id', $divisionId);
    //         });
    //     }

    //     $upcomingTasks = $upcomingTasksQuery->get();

    //     $visions = Vision::where('company_id', Auth::user()->company_id)
    //         ->with(['missions.objectives.keyResults.dailyTasks'])
    //         ->get();

        // $user = Auth::user();
        // $divisions = $user->divisions()->paginate(10);

        // $beforeAday = Carbon::now()->subDays(1)->format('d/m/Y');

    //     return view('report_project_tree.index', compact('visions', 'overdueTasks', 'upcomingTasks', 'divisions','totalCounts','beforeAday','startDate','endDate'));
    // }
    public function index(Request $request)
    {
        $user = Auth::user();
        $divisions = $user->divisions()->get();
        $beforeAday = Carbon::now()->subDays(1)->format('d/m/Y');
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : NULL ;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : NULL ;
        $defaultDivisi = Auth::user()->FirstDivision ? Auth::user()->FirstDivision->id : NULL;

        return view('report_project_tree.indexv2', compact('divisions','beforeAday','startDate','endDate','defaultDivisi'));
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
                    ->orWhere('name', ParamSchema::NOTCOMPLATE)
                    ->orWhere('name', ParamSchema::BACKLOG);
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
            // Show tasks with end_date >= today OR backlog tasks without end_date
            $query->where(function ($q) use ($today) {
                $q->where('end_date', '>=', $today)
                    ->orWhere(function ($subQ) {
                        $subQ->whereNull('end_date')
                            ->whereHas('taskStatus', function ($statusQ) {
                                $statusQ->where('name', ParamSchema::BACKLOG);
                            });
                    });
            });
        }

        $tasks = $query->get()->map(function ($task) {
            $url = NULL;
            if (Access::can('show', 'dailytasks')) {
                $url = route('dailytask.show', $task->slug);
            }
            
            $headName = $task->head ? "< ". Str::limit($task->head->name, 40) : '';

            return [
                'is_overdue' => $task->isOverdue(),
                'name_show' => $task->nameShow . ' ' . $headName,
                'task_status' => $task->taskStatus->name,
                'date_show' => $task->date_show,
                'user_create' => $task->user ? $task->user->name : '',
                'user_assign' => $task->assign ? $task->assign->name : '',
                'url' => $url,
                'main_project' => $task->project ? $task->project->name : '',
                'data_project' => $task->dataProject ? $task->dataProject->title : '',
                'start_date' => $task->start_date,
                'slug' => $task->slug, // Add slug
                'task_id' => $task->id, // Add task ID
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
        
        // Add next task slug logic
        $sortedTasks->each(function (&$task, $key) use ($sortedTasks) {
            $nextTask = $sortedTasks->get($key + 1); // Get next task
            $task['next_task_slug'] = $nextTask ? $nextTask['slug'] : null; // Add next task slug
        });

        return response()->json($sortedTasks);
    }

    // This function calculates the total counts of tasks based on their status for a given company and optional date range and division.
    public function getTotalCounts(Request $request)
    {
        $today = Carbon::today();
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : null;
        $divisionId = $request->get('division_id');

        $overdueTasksQuery = User::where('company_id', Auth::user()->company_id)
            ->withCount([
                'dailyTaskAssigns as todo_count' => function ($query) use ($today, $startDate, $endDate) {
                    $query->where('end_date', '<', $today);
                    if ($startDate && $endDate) {
                        $query->whereBetween('end_date', [$startDate, $endDate]);
                    }
                    $query->whereHas('taskStatus', function ($q) {
                        $q->where('name', ParamSchema::TODO);
                    });
                },
                'dailyTaskAssigns as doing_count' => function ($query) use ($today, $startDate, $endDate) {
                    $query->where('end_date', '<', $today);
                    if ($startDate && $endDate) {
                        $query->whereBetween('end_date', [$startDate, $endDate]);
                    }
                    $query->whereHas('taskStatus', function ($q) {
                        $q->where('name', ParamSchema::DOING);
                    });
                },
                'dailyTaskAssigns as in_review_count' => function ($query) use ($today, $startDate, $endDate) {
                    $query->where('end_date', '<', $today);
                    if ($startDate && $endDate) {
                        $query->whereBetween('end_date', [$startDate, $endDate]);
                    }
                    $query->whereHas('taskStatus', function ($q) {
                        $q->where('name', ParamSchema::INREVIEW);
                    });
                },
                'dailyTaskAssigns as not_complate_count' => function ($query) use ($today, $startDate, $endDate) {
                    $query->where('end_date', '<', $today);
                    if ($startDate && $endDate) {
                        $query->whereBetween('end_date', [$startDate, $endDate]);
                    }
                    $query->whereHas('taskStatus', function ($q) {
                        $q->where('name', ParamSchema::NOTCOMPLATE);
                    });
                },
            ]);

        if ($divisionId) {
            $overdueTasksQuery->whereHas('divisions', function ($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
            });
        }

        $overdueTasks = $overdueTasksQuery->get();

        $totalCounts = [
            'todo' => 0,
            'doing' => 0,
            'in_review' => 0,
            'not_complate' => 0,
        ];

        foreach ($overdueTasks as $user) {
            $totalCounts['todo'] += $user->todo_count;
            $totalCounts['doing'] += $user->doing_count;
            $totalCounts['in_review'] += $user->in_review_count;
            $totalCounts['not_complate'] += $user->not_complate_count;
        }

        return response()->json($totalCounts);
    }

    public function getOverdueTasks(Request $request)
    {
        $today = Carbon::today();
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : null;
        $defaultDivisi = Auth::user()->FirstDivision ? Auth::user()->FirstDivision->id : NULL;
        $divisionId = $request->get('division_id') ?? $defaultDivisi;

        $overdueTasksQuery = User::select('id', 'slug', 'name')
            ->byCompany(Auth::user()->company_id)
            ->withCount(['dailyTaskAssigns as daily_task_assigns_count' => function ($query) use ($today, $startDate, $endDate) {
                $query->where('end_date', '<', $today);
                if ($startDate && $endDate) {
                    $query->whereBetween('end_date', [$startDate, $endDate]);
                }
                $query->whereHas('taskStatus', function ($query) {
                    $query->where('name', ParamSchema::DOING)
                        ->orWhere('name', ParamSchema::INREVIEW)
                        ->orWhere('name', ParamSchema::TODO)
                        ->orWhere('name', ParamSchema::NOTCOMPLATE);
                });
            }])
            ->withCount(['dailyTasks as daily_task_assigns_count_all' => function ($query) use ($today, $startDate, $endDate) {
                $query->where('end_date', '<', $today);
                if ($startDate && $endDate) {
                    $query->whereBetween('end_date', [$startDate, $endDate]);
                }
                $query->whereHas('taskStatus', function ($query) {
                    $query->where('name', ParamSchema::DOING)
                        ->orWhere('name', ParamSchema::INREVIEW)
                        ->orWhere('name', ParamSchema::TODO)
                        ->orWhere('name', ParamSchema::NOTCOMPLATE);
                })
                ;
            }])
            ->orderBy('daily_task_assigns_count', 'desc');

        if ($divisionId) {
            $overdueTasksQuery->whereHas('divisions', function ($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
            });
        }
        
        $overdueTasks = $overdueTasksQuery->having('daily_task_assigns_count', '>', 0)->get();

        return response()->json($overdueTasks);
    }

    public function getUpcomingTasks(Request $request)
    {
        $today = Carbon::today();
        $defaultDivisi = Auth::user()->FirstDivision ? Auth::user()->FirstDivision->id : NULL;
        $divisionId = $request->get('division_id') ?? $defaultDivisi;

        $upcomingTasksQuery = User::byCompany(Auth::user()->company_id)
            ->withCount(['dailyTaskAssigns' => function ($query) use ($today) {
                // Show tasks with end_date >= today OR tasks without end_date (NULL) that have backlog status
                $query->where(function ($q) use ($today) {
                    $q->where('end_date', '>=', $today)
                        ->orWhere(function ($subQ) {
                            $subQ->whereNull('end_date')
                                ->whereHas('taskStatus', function ($statusQ) {
                                    $statusQ->where('name', ParamSchema::BACKLOG);
                                });
                        });
                });
                
                $query->whereHas('taskStatus', function ($query) {
                    $query->where('name', ParamSchema::DOING)
                        ->orWhere('name', ParamSchema::INREVIEW)
                        ->orWhere('name', ParamSchema::TODO)
                        ->orWhere('name', ParamSchema::NOTCOMPLATE)
                        ->orWhere('name', ParamSchema::BACKLOG);
                });
            }])->orderBy('daily_task_assigns_count', 'desc');

        if ($divisionId) {
            $upcomingTasksQuery->whereHas('divisions', function ($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
            });
        }

        $upcomingTasks = $upcomingTasksQuery->having('daily_task_assigns_count', '>', 0)->get();

        return response()->json($upcomingTasks);
    }

    public function getVisions(Request $request)
    {
        // Get pagination parameters
        $perPage = $request->get('per_page', 10); // Default 10 items per page
        
        // Only load visions with counts, no nested data
        $visions = Vision::select('visions.id', 'visions.vision')
            ->withCount('missions as total_missions')
            ->paginate($perPage);

        return response()->json($visions);
    }

    public function getMissions(Request $request, $visionId)
    {
        // Find the vision and load its missions with counts
        $vision = Vision::findOrFail($visionId);
        
        $missions = $vision->missions()
            ->select('missions.id', 'missions.vision_id', 'missions.mission')
            ->withCount('objectives as total_objectives')
            ->get()
            ->map(function ($mission) {
                // Calculate total tasks for this mission using database aggregation
                $totalTasks = DB::table('objectives')
                    ->join('objective_key_results', 'objectives.id', '=', 'objective_key_results.objective_id')
                    ->join('daily_task_objective_key_result', 'objective_key_results.id', '=', 'daily_task_objective_key_result.objective_key_result_id')
                    ->where('objectives.mission_id', $mission->id)
                    ->count();
                
                $mission->total_tasks = $totalTasks;
                return $mission;
            });

        return response()->json($missions);
    }

    public function getObjectives(Request $request, $missionId)
    {
        $mission = Mission::findOrFail($missionId);
        
        $objectives = $mission->objectives()
            ->select('objectives.id', 'objectives.mission_id', 'objectives.name')
            ->withCount('keyResults as total_key_results')
            ->get()
            ->map(function ($objective) {
                // Calculate total tasks for this objective
                $totalTasks = DB::table('objective_key_results')
                    ->join('daily_task_objective_key_result', 'objective_key_results.id', '=', 'daily_task_objective_key_result.objective_key_result_id')
                    ->where('objective_key_results.objective_id', $objective->id)
                    ->count();
                
                $objective->total_tasks = $totalTasks;
                return $objective;
            });

        return response()->json($objectives);
    }

    public function getKeyResults(Request $request, $objectiveId)
    {
        $objective = Objective::findOrFail($objectiveId);
        
        $keyResults = $objective->keyResults()
            ->select('objective_key_results.id', 'objective_key_results.objective_id', 'objective_key_results.result')
            ->withCount('dailyTasks as total_tasks')
            ->get();

        return response()->json($keyResults);
    }

    public function getDailyTasks(Request $request, $keyResultId)
    {
        $keyResult = ObjectiveKeyResult::findOrFail($keyResultId);
        
        $dailyTasks = $keyResult->dailyTasks()
            ->select(
                'daily_tasks.id',
                'daily_tasks.name',
                'daily_tasks.start_date',
                'daily_tasks.end_date',
                'daily_tasks.task_status_id',
                'daily_tasks.assignment_user_id'
            )
            ->with([
                'taskStatus:id,name',
                'assign:id,name'
            ])
            ->get()
            ->map(function ($task) {
                $task->date_show = $task->getDateShowAttribute();
                $task->is_overdue = $task->isOverdue();
                return $task;
            });

        return response()->json($dailyTasks);
    }
}