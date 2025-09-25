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
        $divisions = $user->divisions()->paginate(10);
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
                $query->where('end_date', '>=', $today);
                $query->whereHas('taskStatus', function ($query) {
                    $query->where('name', ParamSchema::DOING)
                        ->orWhere('name', ParamSchema::INREVIEW)
                        ->orWhere('name', ParamSchema::TODO)
                        ->orWhere('name', ParamSchema::NOTCOMPLATE);
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
        $visions = Vision::select('visions.id', 'visions.vision') // Qualify the id with table name
            ->with([
                'missions' => function ($query) {
                    $query->select('missions.id', 'missions.vision_id', 'missions.mission') // Qualify the id
                        ->with([
                            'objectives' => function ($query) {
                                $query->select('objectives.id', 'objectives.mission_id', 'objectives.name') // Qualify the id
                                    ->with([
                                        'keyResults' => function ($query) {
                                            $query->select('objective_key_results.id', 'objective_key_results.objective_id', 'objective_key_results.result') // Qualify the id
                                                ->with([
                                                    'dailyTasks' => function ($query) {
                                                        $query->select(
                                                            'daily_tasks.id',
                                                            // 'daily_tasks.key_result_id',
                                                            'daily_tasks.name',
                                                            'daily_tasks.start_date',
                                                            'daily_tasks.end_date',
                                                            'daily_tasks.task_status_id',
                                                            'daily_tasks.assignment_user_id'
                                                        ) // Qualify the id
                                                        ->with([
                                                            'taskStatus:id,name', // Task Status: explicitly select id and name
                                                            'assign:id,name' // Assign: explicitly select id and name
                                                        ]);
                                                    }
                                                ]);
                                        }
                                    ]);
                            }
                        ]);
                }
            ])->get()
            ->map(function ($vision) {
                // Calculate total tasks for vision
                $vision->total_tasks = $vision->missions->sum(function ($mission) {
                    return $mission->objectives->sum(function ($objective) {
                        return $objective->keyResults->sum(function ($keyResult) {
                            return $keyResult->dailyTasks->count();
                        });
                    });
                });

                // Process each mission
                $vision->missions->each(function ($mission) {
                    $mission->total_tasks = $mission->objectives->sum(function ($objective) {
                        return $objective->keyResults->sum(function ($keyResult) {
                            return $keyResult->dailyTasks->count();
                        });
                    });

                    // Process each objective
                    $mission->objectives->each(function ($objective) {
                        $objective->total_tasks = $objective->keyResults->sum(function ($keyResult) {
                            return $keyResult->dailyTasks->count();
                        });

                        // Process each key result
                        $objective->keyResults->each(function ($keyResult) {
                            $keyResult->total_tasks = $keyResult->dailyTasks->count();

                            // Add `date_show` for each daily task
                            $keyResult->dailyTasks->transform(function ($task) {
                                // Manually add the `date_show` attribute
                                $task->date_show = $task->getDateShowAttribute();
                                $task->is_overdue = $task->isOverdue();
                                return $task;
                            });
                        });
                    });
                });

                return $vision;
            });

        return response()->json($visions);
    }
}