<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Http\Requests\DailyTaskStoreApiRequest;
use App\Http\Requests\DailyTaskRequest;
use App\Http\Requests\DailyTaskSubTaskRequest;

use App\Http\Resources\DailyTaskResource;
use App\Http\Resources\DailyTaskCollection;
use App\Http\Resources\DailyTaskEditResource;

use Carbon\Carbon;
use App\Helpers\Access;
use App\Schemas\ParamSchema;

use App\Models\User;
use App\Models\DailyTask;
use App\Models\DailyTaskCategory;
use App\Models\DailyTaskType;
use App\Models\TaskStatus;
use App\Models\DailyTaskMedia;
use App\Models\DailyTaskExtend;
use App\Models\DailyTaskMessage;
use App\Models\DailyTaskProject;
use App\Models\DailyTaskCustomFieldValue;
use App\Models\Objective;
use App\Models\DailyTaskStatusRecord;
use App\Models\SettingCompany;
use App\Models\Division;
use App\Models\DivisionQuotaLock;
use App\Models\RecurringRule;
use App\Models\Project;

use App\Helpers\InboxHelper;
use Ramsey\Uuid\Uuid;

class DailyTaskController extends Controller
{
    public function index(Request $request)
    {
        $authUser = Auth::user();

        // Helper: normalize input to array (supports array or comma-separated string)
        $toArray = function ($val) {
            if (is_null($val) || $val === '') return [];
            if (is_array($val)) return array_values(array_filter($val, fn($v) => $v !== '' && $v !== null));
            return array_values(array_filter(array_map('trim', explode(',', (string)$val)), fn($v) => $v !== ''));
        };

        // ===== Filters from request (support arrays) =====
        $taskFilterArr        = $toArray($request->input('task', 'all')); // ['today','overdue','upcoming','all']
        $userCreateArr        = $toArray($request->input('user_create'));   // creator (users.name or users.id)
        $userAssignArr        = $toArray($request->input('user_assign'));   // assignee (assign.name or assign.id)
        $statusFilterArr      = $toArray($request->input('status'));
        $divisionArr          = $toArray($request->input('division'));
        $dailyTaskProjectsArr = $toArray($request->input('daily_task_project'));// DailyTaskProject: name or id
        $projectArr           = $toArray($request->input('project'));       // Project: name or id

        $start_date = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $end_date   = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $search     = trim((string) $request->input('search', ''));
        $perPage    = (int) $request->input('per_page', 10);
        $sortDir    = $request->input('sort', 'desc') === 'asc' ? 'asc' : 'desc';

        $query = DailyTask::orderBy('created_at', $sortDir);

        // ===== Default status filter when not provided and task != all and search empty =====
        $taskFilterIncludesAll = in_array('all', array_map('strtolower', $taskFilterArr), true);
        if (empty($statusFilterArr) && $search === '' && !$taskFilterIncludesAll) {
            $query->whereHas('taskStatus', function ($q) {
                $q->whereIn('name', [
                    ParamSchema::DOING,
                    ParamSchema::INREVIEW,
                    ParamSchema::TODO,
                    ParamSchema::NOTCOMPLATE,
                ]);
            });
        }

        // dd($taskFilterArr, $userCreateArr, $userAssignArr, $statusFilterArr, $divisionArr, $dailyTaskProjectsArr, $projectArr);
        // ===== Task timeframe filter (supports multiple values; OR logic across selections) =====
        if (!empty($taskFilterArr)) {
            // Normalize to lowercase keywords
            $filters = array_map('strtolower', $taskFilterArr);
            if (!in_array('all', $filters, true)) {
                $query->where(function ($q) use ($filters) {
                    if (in_array('overdue', $filters, true)) {
                        $q->orWhere(function ($qq) {
                            $qq->whereDate('start_date', '<', now())
                               ->whereDate('end_date', '<', now());
                        });
                    }
                    if (in_array('today', $filters, true)) {
                        $q->orWhere(function ($qq) {
                            $qq->whereDate('start_date', '<=', now())
                               ->whereDate('end_date', '>=', now());
                        });
                    }
                    if (in_array('upcoming', $filters, true)) {
                        $q->orWhere(function ($qq) {
                            $qq->where('start_date', '>', now());
                        });
                    }
                });
            }
            // if 'all' present -> no-op
        }

        // ===== User filters =====
        // Creator (user relationship)
        if (!empty($userCreateArr)) 
            {
            $query->whereHas('user', function ($q) use ($userCreateArr) {
                // Support both IDs and names
                $q->whereIn('id', $userCreateArr);
            });
        }

        // Assignee (assign relationship)
        if (!empty($userAssignArr)) {
            $query->whereIn('assignment_user_id', $userAssignArr);
        }

        // Fallback: if no userCreate/userAssign provided, limit to tasks relevant to current user
        if (empty($userCreateArr) && empty($userAssignArr)) 
            {
            $query->UserTasks($authUser->id);
        }

        // ===== Status filter =====
        if (!empty($statusFilterArr)) {
            $query->whereHas('taskStatus', function ($q) use ($statusFilterArr) {
                $q->whereIn('id', $statusFilterArr);
            });
        } 

        // ===== Date range =====
        if ($start_date && $end_date) {
            $query->byDateRange($start_date, $end_date);
        }

        // ===== Search (split by space; AND across terms; match name) =====
        if ($search !== '') {
            $terms = array_values(array_filter(explode(' ', $search), fn($t) => $t !== ''));
            $query->where(function ($q) use ($terms) {
                foreach ($terms as $t) {
                    $q->where('name', 'like', "%{$t}%");
                }
            });
        }

        // ===== Division filter (support ids or names) =====
        if (!empty($divisionArr)) {
            $query->whereHas('user.divisions', function ($q) use ($divisionArr) {
                $q->whereIn('division_id', $divisionArr);
            });
        }

        // ===== DailyTaskProject filter (relation `project` - support ids or names) =====
        if (!empty($dailyTaskProjectsArr)) {
            $query->whereHas('project', function ($q) use ($dailyTaskProjectsArr) {
                $q->whereIn('id', $dailyTaskProjectsArr);
            });
        }

        // ===== Project filter (Project model via column project_id or relation daily Project) =====
        if (!empty($projectArr)) {
           $query->whereIn('project_id', $projectArr);
        }

        // ===== Company scope + pagination =====
        $dailyTasks = $query->byCompany($authUser->company_id)->paginate($perPage);

        // ===== Build filters payload for client =====
        $divisions = $authUser->divisions()->get();
        $taskTimeFrame = [ 'overdue' => 'Overdue', 'today' => 'Today', 'upcoming' => 'Upcoming', 'all' => 'All' ];
        $users = User::select('id', 'name')->byCompany($authUser->company_id)->get();
        $taskStatuss = TaskStatus::bySort()->get();
        $dailyTaskProjects = DailyTaskProject::select('id', 'name')
            ->byCompany($authUser->company_id)
            ->whereHas('tasks')
            ->get();
        $projects = Project::byCompany($authUser->company_id)
            ->select('id', 'title')
            ->whereHas('dailyTasks')
            ->get();

        return (new DailyTaskCollection($dailyTasks))
            ->additional([
                'filters' => [
                    'divisions'           => $divisions,
                    'task_time_frame'     => $taskTimeFrame,
                    'users'               => $users,
                    'task_statuses'       => $taskStatuss,
                    'daily_task_projects' => $dailyTaskProjects,
                    'projects'            => $projects,
                ],
            ]);
    }

    public function create()
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;
            
            // Get data for dropdowns
            $users = User::byCompany($companyId)
                ->select('id', 'name', 'email')
                ->get()
                ;
                
            $categories = DailyTaskCategory::byCompany($companyId)
                ->select('id', 'name')
                ->get();
                
            $types = DailyTaskType::select('id', 'name')->get();
            
            $projects = DailyTaskProject::byCompany($companyId)
                ->select('id', 'name')
                ->get();
                
            $today = strtolower(Carbon::now()->format('l'));
            $days = config('custom.day_name_code');
            $minDate = Carbon::now()->format('Y-m-d');
            
            $taskRecurring = DailyTaskType::where('name', ParamSchema::RECURRING)
                ->select('id', 'name')
                ->first();
    
            $divisionIds = $user->divisions->pluck('id');

            if ($divisionIds->isEmpty()) 
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak tergabung dalam divisi manapun. Hubungi admin atau manager Anda.'
                ], 400);
            }
            
            $objectives = Objective::whereHas('division', function($query) use ($divisionIds) {
                    $query->whereIn('id', $divisionIds);
                })
                ->with(['keyResults'])
                ->select('id', 'name')
                // ->with(['division:id,name'])
                ->get();
                
                
            // $divisions = $user->divisions()
            // ->select('divisions.id', 'name')
            // ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'categories' => $categories,
                    'types' => $types,
                    'daily_task_projects' => $projects,
                    'objectives' => $objectives,
                    'days' => $days,
                    'today' => $today,
                    'min_date' => $minDate,
                    'task_recurring' => $taskRecurring,
                    // 'divisions' => $divisions
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get create task data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get create task data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(DailyTaskStoreApiRequest $request)
    {
        DB::beginTransaction();
        try {
            $startDates = $request->start_date;
            $endDates = $request->end_date;
            $assignmentUserIds = $request->assignment_user_id;
            $categoryIds = $request->category_id;
            $typeIds = $request->type_id;
            $projectIds = $request->daily_task_project_id;
            $dataProjects = $request->project_id;
            $names = $request->name;
            $descriptions = $request->description ?? [];
            $objectives = $request->objective ?? [];
            $recurring_days = $request->input('days') ? json_encode($request->input('days')) : NULL;
            
            $createdTasks = [];
            
                if($startDates && $endDates && $assignmentUserIds) 
                {
                    $status = TaskStatus::where('name',ParamSchema::TODO)->firstOrFail();
                } else {
                    $status = TaskStatus::where('name',ParamSchema::BACKLOG)->firstOrFail();
                }

                $dailyTask = new DailyTask();
                $dailyTask->task_status_id = $status->id;
                $dailyTask->start_date = $startDates ?? NULL; 
                $dailyTask->end_date = $endDates ?? NULL;
                $dailyTask->user_id = $request->user_id ?? Auth::id();
                $dailyTask->assignment_user_id = $assignmentUserIds ?? NULL;
                $dailyTask->daily_task_category_id = $categoryIds;
                $dailyTask->daily_task_type_id = $typeIds;
                $dailyTask->daily_task_project_id = $projectIds ?? NULL;
                $dailyTask->project_id = $dataProjects ?? NULL;
                $dailyTask->name = $names;
                $dailyTask->description = $descriptions ?? null;
                $dailyTask->point = 0;
                $dailyTask->objective_id = $objectives ?? NULL;
                $dailyTask->recurring_days = $recurring_days;
                
                if (DailyTaskType::find($typeIds)->name == ParamSchema::RECURRING) {
                    $recurring = $request->input("recurring");

                    $recurringRule = RecurringRule::create([
                        'frequency' => $recurring['frequency'],
                        'interval' => 1,
                        'by_day' => $recurring['by_day'] ?? null,
                        'by_month_day' => $recurring['by_month_day'] ?? null,
                        'by_month' => $recurring['by_month'] ?? null,
                        'until' => $recurring['until'] ?? null,
                        'start_date' => $dailyTask->start_date,
                    ]);

                    $dailyTask->recurring_rule_id = $recurringRule->id;
                }

                $dailyTask->save();

                if (isset($request->custom_field_values)) {
                    foreach ($request->custom_field_values as $customFieldId => $customFieldValueId) {
                        if(is_array($customFieldValueId)) {
                            foreach($customFieldValueId as $valueId) {
                                DailyTaskCustomFieldValue::create([
                                    'daily_task_id' => $dailyTask->id,
                                    'custom_field_id' => $customFieldId,
                                    'custom_field_value_id' => $valueId,
                                ]);
                            }
                        } else {
                            DailyTaskCustomFieldValue::create([
                                'daily_task_id' => $dailyTask->id,
                                'custom_field_id' => $customFieldId,
                                'custom_field_value_id' => $customFieldValueId,
                            ]);
                        }
                    }
                }

                $keyResults = $request->input('key_result');
                if (!empty($keyResults)) {
                    $dailyTask->keyResults()->attach($keyResults);
                }

                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $timestamp = time();
                        $randomString = rand(100, 999);
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $fileName = $originalName . '_' . $timestamp . '_' . $randomString . '.' . $extension;
    
                        $path = $file->storeAs('media', $fileName);
                        $mediaType = $file->getClientMimeType();
    
                        DailyTaskMedia::create([
                            'daily_task_id' => $dailyTask->id,
                            'file_path' => $path,
                            'file_type' => $mediaType,
                            'status' => ParamSchema::FILETASK,
                        ]);
                    }
                }

                $this->message($dailyTask->id,'create',' Membuat Tugas '.$dailyTask->name);
                $this->statusrecord($dailyTask, $status);

                // $directUrl = route('dailytask.show', ['dailytask' => $dailyTask->slug]);
                $directUrl = url('dailytask/'.$dailyTask->slug);
        
                $inboxHelper = new InboxHelper();
                $inboxHelper->sent(
                    $dailyTask->assignment_user_id, 
                    Auth::id(), 
                    Auth::user()->name.' Menugaskan ' . $dailyTask->name, 
                    $directUrl
                );

                $createdTasks[] = $dailyTask;
            // }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tasks created successfully',
                'data' => new DailyTaskResource($dailyTask)
            ], 201);

        } catch (\Throwable $th) 
        {
            // dd($th);
            DB::rollback();
            Log::error($th->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create tasks',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function edit($slug)
    {
        try {
            $dailytask = DailyTask::byCompany(Auth::user()->company_id)->where('slug', $slug)->first();
            if(!$dailytask)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas tidak ditemukan',
                ], 404);
            }
            if(!$dailytask->isAction())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas tidak diizinkan untuk akses',
                ], 404);
            }
            $user = Auth::user();
            $companyId = $user->company_id;
            
            // Get data for dropdowns
            $users = User::byCompany($companyId)
                ->select('id', 'name', 'email')
                ->get()
                ;
                
            $categories = DailyTaskCategory::byCompany($companyId)
                ->select('id', 'name')
                ->get();
                
            $types = DailyTaskType::select('id', 'name')->get();
            
            $projects = DailyTaskProject::byCompany($companyId)
                ->select('id', 'name')
                ->get();
                
            $today = strtolower(Carbon::now()->format('l'));
            $days = config('custom.day_name_code');
            $minDate = Carbon::now()->format('Y-m-d');
            
            $taskRecurring = DailyTaskType::where('name', ParamSchema::RECURRING)
                ->select('id', 'name')
                ->first();
    
            $divisionIds = $user->divisions->pluck('id');

            if ($divisionIds->isEmpty()) 
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak tergabung dalam divisi manapun. Hubungi admin atau manager Anda.'
                ], 400);
            }
            
            $objectives = Objective::whereHas('division', function($query) use ($divisionIds) {
                    $query->whereIn('id', $divisionIds);
                })
                ->select('id', 'name')
                // ->with(['division:id,name'])
                ->get();
                
                
            // $divisions = $user->divisions()
            // ->select('divisions.id', 'name')
            // ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    // Use a dedicated resource so the payload matches the expected edit payload shape
                    'form' => new DailyTaskEditResource($dailytask),

                    // dropdown data
                    'users' => $users,
                    'categories' => $categories,
                    'types' => $types,

                    // Separate the two "project" concepts to avoid name collisions
                    'daily_task_projects' => $projects, // previously $projects is DailyTaskProject list
                    'projects' => Project::byCompany($companyId)->select('id', 'title')->whereHas('dailyTasks')->get(),

                    'objectives' => $objectives,
                    'days' => $days,
                    'today' => $today,
                    'min_date' => $minDate,
                    'task_recurring' => $taskRecurring,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get create task data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get create task data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($slug)
    {
        try {
            $dailytask = DailyTask::byCompany(Auth::user()->company_id)->where('slug', $slug)->first();
            if(!$dailytask){
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas tidak ditemukan',
                ], 404);
            }
            return new DailyTaskResource($dailytask);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Task not found.'
            ], 404);
        }
    }

    public function update(DailyTaskStoreApiRequest $request, $slug)
    {
        
        DB::beginTransaction();
        try {
            $dailyTask = DailyTask::byCompany(Auth::user()->company_id)
            ->where('slug', $slug)
            ->first();
            if(!$dailyTask)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas tidak ditemukan',
                ], 404);
            }
            if(!$dailyTask->isAction())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus tugas ini'
                ], 403);
            }

            $message = "";
            if($request->start_date && $request->end_date && $request->assignment_user_id && ($dailyTask->taskStatus->name == ParamSchema::BACKLOG)) {
                $todo = TaskStatus::where('name',ParamSchema::TODO)->firstOrFail();
                $dailyTask->task_status_id = $todo->id;
            }
            
            if($dailyTask->assignment_user_id != $request->assignment_user_id) {
                $message = "Mengubah Tugas ".$dailyTask->name." dari ".User::find($dailyTask->assignment_user_id)->name." menjadi ".User::find($request->assignment_user_id)->name;
            } elseif ($dailyTask->end_date != $request->end_date) {
                $message = "Mengubah Tugas ".$dailyTask->name." dari ".Carbon::parse($dailyTask->end_date)->format('d-m-Y')." menjadi ".Carbon::parse($request->end_date)->format('d-m-Y');
            }
        
            if($message) {
                $directUrl = route('dailytask.show', ['dailytask' => $dailyTask->slug]);
                if(Auth::id() == $request->assignment_user_id) {
                    $userTo = $dailyTask->user_id;
                } elseif(Auth::id() == $dailyTask->user_id) {
                    $userTo = $request->assignment_user_id;
                } else {
                    $userTo = $request->assignment_user_id;
                    $this->sentInbox($dailyTask->user_id, $message, $directUrl);
                }
    
                $this->sentInbox($userTo, $message, $directUrl);
            }  
            
            if($request->point > 0) {
                $request->validate([
                    'point' => 'required|integer|min:1',
                    'division_id' => 'required|exists:divisions,id',
                ]);

                $check = $this->checkDivisionQuota(new Request([
                    'division_id' => $request->division_id,
                    'point' => $request->point,
                    'exclude_task_id' => $dailyTask->id
                ]));

                if ($check->original['status'] !== 'ok') {
                    return response()->json([
                        'success' => false,
                        'message' => $check->original['message']
                    ], 400);
                }

                $dailyTask->division_id = $request->division_id;
                $dailyTask->division_quota_lock_id = $check->original['quota_lock_id'];
            } 

            // Handle Recurring Task Creation
            $oldType = $dailyTask->daily_task_type_id;
            $newType = $request->type_id;
            
            $dailyTask->start_date = $request->start_date;
            $dailyTask->end_date = $request->end_date;
            $dailyTask->assignment_user_id = $request->assignment_user_id;
            $dailyTask->daily_task_category_id = $request->category_id;
            $dailyTask->daily_task_type_id = $request->type_id;
            $dailyTask->point = $request->point ?? 0;
            $dailyTask->name = $request->name;
            $dailyTask->description = $request->description;
            $dailyTask->daily_task_project_id = $request->daily_task_project ?? NULL;
            $dailyTask->project_id = $request->project_id ?? NULL;
            $dailyTask->daily_task_category_id = $request->category_id;
            $dailyTask->objective_id = $request->objective;
            
            $dailyTask->save();
    
            $dailyTask->customFieldValues()->delete();
    
            if (isset($request->custom_field_values)) {
                foreach ($request->custom_field_values as $customFieldId => $customFieldValueId) {
                    if(is_array($customFieldValueId)) {
                        foreach($customFieldValueId as $valueId) {
                            DailyTaskCustomFieldValue::create([
                                'daily_task_id' => $dailyTask->id,
                                'custom_field_id' => $customFieldId,
                                'custom_field_value_id' => $valueId,
                            ]);
                        }
                    } else {
                        DailyTaskCustomFieldValue::create([
                            'daily_task_id' => $dailyTask->id,
                            'custom_field_id' => $customFieldId,
                            'custom_field_value_id' => $customFieldValueId,
                        ]);
                    }
                }
            }

            $keyResults = $request->input('key_result_0');
            if($keyResults) {
                $dailyTask->keyResults()->sync($keyResults);
            }

            if($dailyTask->children) {
                foreach ($dailyTask->children as $dailyTaskChild) {
                    $dailyTaskChild->objective_id = $request->objective;
                    $dailyTaskChild->keyResults()->sync($keyResults);
                    $dailyTaskChild->save();
                }
            }

            if (DailyTaskType::find($newType)->name == ParamSchema::RECURRING) {
                $rec = $request->input('recurring');
                $rule = $dailyTask->recurringRule;

                $recurringData = [
                    'frequency' => $rec['frequency'],
                    'interval' => 1,
                    'by_day' => $rec['by_day'] ?? null,
                    'by_month_day' => $rec['by_month_day'] ?? null,
                    'by_month' => $rec['by_month'] ?? null,
                    'until' => $rec['until'] ?? null,
                    'start_date' => $dailyTask->start_date,
                    'description' => $dailyTask->description ?? null,
                ];

                switch ($rec['frequency']) {
                    case 'DAILY':
                    case 'WEEKLY':
                        $recurringData['by_day'] = $rec['by_day'] ?? null;
                        break;
                    case 'MONTHLY':
                        $recurringData['by_month_day'] = $rec['by_month_day'] ?? null;
                        break;
                    case 'YEARLY':
                        $recurringData['by_month_day'] = $rec['by_month_day'] ?? null;
                        $recurringData['by_month'] = $rec['by_month'] ?? null;
                        break;
                }

                if ($rule) {
                    $rule->update($recurringData);
                } else {
                    $newRule = RecurringRule::create($recurringData);
                    $dailyTask->recurring_rule_id = $newRule->id;
                    $dailyTask->save();
                }
            } else {
                if ($dailyTask->recurringRule) {
                    $this->setrecurringToNull($dailyTask, true);
                }
            }
            
            $this->message($dailyTask->id,'edit','Mengubah Task '.$dailyTask->name);

            DB::commit();

            return new DailyTaskResource($dailyTask);

        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update task',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($slug)
    {
        try {
            $dailytask = DailyTask::byCompany(Auth::user()->company_id)->where('slug', $slug)
            ->firstOrFail();

            if(!$dailytask->isAction())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus tugas ini'
                ], 403);
            }

            if($dailytask->head)
            {
                $redirectTo = $dailytask->head->slug;
                if($dailytask->head->user_id == $dailytask->head->assignment_user_id)
                {
                    $this->sentInbox($dailytask->head->user_id,Auth::user()->name.' Menghapus Sub Tugas ' . $dailytask->name, null);
                }else
                {
                    $this->sentInbox($dailytask->head->user_id,Auth::user()->name.' Menghapus Sub Tugas ' . $dailytask->name, null);
                    $this->sentInbox($dailytask->head->assignment_user_id,Auth::user()->name.' Menghapus Sub Tugas ' . $dailytask->name, null);
                }
            }
        
            $dailytask->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());

            // dd($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task'
            ], 500);
        }
    }

    public function report(Request $request, $slug)
    {
        $request->validate([
            'note' => 'required|string',
            'media.*' => 'nullable|file|max:10240'
        ]);

        DB::beginTransaction();
        try {
            $dailytask = DailyTask::byCompany(Auth::user()->company_id)
                          ->where('slug', $slug)
                          ->firstOrFail();
                          
            $inReview = TaskStatus::where('name', ParamSchema::INREVIEW)->firstOrFail();

            $dailytask->report_note = $request->note;
            $dailytask->task_status_id = $inReview->id;

            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $timestamp = time();
                    $randomString = rand(100, 999);
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileName = $originalName . '_' . $timestamp . '_' . $randomString . '.' . $extension;

                    $path = $file->storeAs('media', $fileName);
                    $mediaType = $file->getClientMimeType();

                    DailyTaskMedia::create([
                        'daily_task_id' => $dailytask->id,
                        'file_path' => $path,
                        'file_type' => $mediaType,
                    ]);
                }
            }

            $submitDate = Carbon::now();
            $dailytask->submit = $submitDate;

            $endDate = Carbon::parse($dailytask->end_date)->endOfDay();
            $submitDate = Carbon::parse($dailytask->submit)->startOfDay();

            $dailytask->status_submit = ($submitDate->lessThanOrEqualTo($endDate)) ? ParamSchema::ONTIME : ParamSchema::LATE;

            $this->message($dailytask->id, 'report', ' Membuat Laporan Tugas ' . $dailytask->name);
            $this->statusrecord($dailytask, $inReview);
            $dailytask->save();

            $directUrl = route('dailytask.show', ['dailytask' => $dailytask->slug]);
            
            if(Auth::id() == $dailytask->assignment_user_id) {
                $userTo = $dailytask->user_id;
            } elseif(Auth::id() == $dailytask->user_id) {
                $userTo = $dailytask->assignment_user_id;
            } else {
                $userTo = $dailytask->assignment_user_id;
                $this->sentInbox($dailytask->user_id, 'Membuat Laporan pada Tugas ' . $dailytask->name, $directUrl);
            }
            
            $this->sentInbox($userTo, 'Membuat Laporan pada Tugas ' . $dailytask->name, $directUrl);

            if($dailytask->head) {
                if($dailytask->head->user_id == $dailytask->head->assignment_user_id) {
                    $this->sentInbox($dailytask->head->user_id, Auth::user()->name.' Membuat Laporan pada Tugas ' . $dailytask->name, $directUrl);
                } else {
                    $this->sentInbox($dailytask->head->user_id, Auth::user()->name.' Membuat Laporan pada Sub Tugas ' . $dailytask->name, $directUrl);
                    $this->sentInbox($dailytask->head->assignment_user_id, Auth::user()->name.' Membuat Laporan pada Sub Tugas ' . $dailytask->name, $directUrl);
                }
            }

            DB::commit();

            return new DailyTaskResource($dailytask);

        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit report',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function statuschange(Request $request, $slug)
    {
        try {
            $dailyTask = DailyTask::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            if(!$dailyTask->taskStatus->name == ParamSchema::TODO || !$dailyTask->taskStatus->name == ParamSchema::BACKLOG)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Task update failed!, Status hanya dizinkan untuk TODO dan BACKLOG',
                ]);
            }
            $doing = TaskStatus::where('name', ParamSchema::DOING)->firstOrFail();
            $dailyTask->task_status_id = $doing->id;
            $dailyTask->save();

            // Record the action and update status
            $this->message($dailyTask->id, 'report', 'Tugas ' . $dailyTask->name . ' dikerjakan');
            $this->statusrecord($dailyTask, $doing);

            // If the request is an Ajax call, return a JSON response
            return response()->json([
                'success' => true,
                'data' => new DailyTaskResource($dailyTask),
                'message' => 'Task status updated successfully!',
            ]);
        } catch (\Exception $e) {
            // Handle the exception and return error response
            return response()->json([
                'success' => false,
                'message' => 'Task update failed!',
            ]);
        }
    }

    protected function message($dailyTaskId, $template, $message, $filePath = null)
    {
        switch ($template) 
        {
            case 'create':
                $message = 
                '
                <div class="alert alert-primary d-flex align-items-center" role="alert" style="background-color: #cce5ff; border-color: #004085; color: #004085;">
                    <i class="fa fa-plus-circle mr-2" style="color: #004085;"></i>
                    <div>
                        '.$message.' 
                    </div>
                </div>
                ';
                break;
            case 'edit':
                $message = 
                '
                <div class="alert alert-warning d-flex align-items-center" role="alert" style="background-color: #fff3cd; border-color: #856404; color: #856404;">
                    <i class="fa fa-edit mr-2" style="color: #856404;"></i>
                    <div>
                        '.$message.' 
                    </div>
                </div>
                ';
                break;
            case 'report':
                $message = 
                '
                <div class="alert alert-primary d-flex align-items-center" role="alert" style="background-color: #cce5ff; border-color: #004085; color: #004085;">
                    <i class="fa fa-plus-circle mr-2" style="color: #004085;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            case 'approvement':
                $message = 
                '
                <div class="alert alert-success d-flex align-items-center" role="alert" style="background-color: #d4edda; border-color: #155724; color: #155724;">
                    <i class="fa fa-thumbs-up mr-2" style="color: #155724;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            case 'extend':
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #383d41; color: #383d41;">
                    <i class="fa fa-clock mr-2" style="color: #383d41;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;

            case 'reject':
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #ae2121; color: #ae2121;">
                    <i class="fa fa-times-circle mr-2" style="color: #ae2121;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            case 'trash':
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #ae2121; color: #ae2121;">
                    <i class="fa fa-trash mr-2" style="color: #ae2121;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            default:
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #383d41; color: #383d41;">
                    <i class="fa fa-comment mr-2" style="color: #383d41;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
        }

        $dailyTaskMessage = new DailyTaskMessage();
        $dailyTaskMessage->user_id = Auth::user()->id;
        $dailyTaskMessage->daily_task_id = $dailyTaskId;
        $dailyTaskMessage->message = $message;
        $dailyTaskMessage->file_path = $filePath ?? NULL;
        $dailyTaskMessage->save();

        return true;
    } 

    protected function statusrecord($dailyTask, $status)
    {
        DailyTaskStatusRecord::create([
            'daily_task_id' => $dailyTask->id,
            'task_status_id' => $status->id,
            'date' => now(),
        ]);

        return true;
    }

    public function sentInbox($to,$message,$directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $to, 
            Auth::user()->id, 
            $message, 
            $directUrl
        );

        return;
    }


    public function checkDivisionQuota(Request $request)
    {
        $request->validate([
            'point' => 'required|integer',
            'division_id' => 'required|exists:divisions,id',
            'exclude_task_id' => 'nullable|uuid|exists:daily_tasks,id',
        ]);

        $point = (int) $request->point;
        $divisionId = $request->division_id;
        $month = now()->month;
        $year = now()->year;

        if ($point <= 0) 
        {
            return response()->json([
                'status' => 'ok',
                'message' => 'Poin nol atau negatif, tidak perlu cek kuota.',
                'remaining' => null
            ]);
        }
        $checkDailyTask = $request->exclude_task_id ? DailyTask::where('id',$request->exclude_task_id)->first() : NULL;


        if($checkDailyTask && $checkDailyTask->division_id == $divisionId && $checkDailyTask->division_quota_lock_id)
        {
            $quota = DivisionQuotaLock::where('division_id', $divisionId)
                ->where('id', $checkDailyTask->division_quota_lock_id)
                ->first();
        }else
        {
            $quota = DivisionQuotaLock::where('division_id', $divisionId)
                ->where('month', $month)
                ->where('year', $year)
                ->first();
        }

        if (!$quota) 
        {
            return response()->json([
                'status' => 'fail',
                'message' => 'Kuota Tidak Tersedia, Silahkan untuk menambahkan kuota edit divisi pada menu Divisi atau Hubungi Admin.',
            ]);
        }

        $used = DailyTask::where('division_quota_lock_id', $quota->id)
            ->when($request->exclude_task_id, function ($query) use ($request) {
                $query->where('id', '!=', $request->exclude_task_id);
            })
            ->sum('point');

        $sisa = $quota->locked_quota - $used;

        if ($point > $sisa) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Kuota tidak cukup. Sisa: ' . $sisa . ' poin.',
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'remaining' => $sisa,
            'quota_lock_id' => $quota->id, // kalau ingin langsung assign
        ]);
    }
}