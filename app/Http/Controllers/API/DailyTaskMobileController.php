<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\DailyTask; 
use Illuminate\Support\Facades\Validator;
use App\Models\TaskStatus;
use App\Models\DailyTaskProject;
use App\Models\DailyTaskCategory;
use App\Models\DailyTaskType;
use App\Models\DailyTaskMedia;
use App\Models\Project;
use App\Models\User;
use App\Models\Objective;
use App\Models\DivisionQuotaLock;
use App\Models\RecurringRule;
use App\Models\Division;
use App\Schemas\ParamSchema;
use Illuminate\Support\Facades\Log; 
use App\Helpers\InboxHelper;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\DailyTaskStatusRecord;



class DailyTaskMobileController extends BaseController
{
    /**
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();
            $tasks = DailyTask::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('assignment_user_id', $userId);
                })
                ->with([
                    'user:id,name',
                    'assign:id,name',
                ])
                ->addSelect([
                    'id',
                    'name',
                    'start_date',
                    'end_date',
                    'user_id',
                    'assignment_user_id',
                    'task_status_name' => TaskStatus::select('name')
                        ->whereColumn('task_statuses.id', 'daily_tasks.task_status_id')
                        ->limit(1),
                ])
                ->orderBy('start_date', 'desc')
                ->get();

            $formattedTasks = $tasks->map(function ($task) use ($userId) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date,
                    'task_status_name' => $task->task_status_name ?? 'unknown',

                    'access_id' => $userId,
                    'user_id' => $task->user_id,
                    'assignment_user_id' => $task->assignment_user_id,
                    'user_name' => $task->user->name ?? 'N/A',
                    'assignment_user_name' => $task->assign->name ?? 'N/A',
                ];
            });

            return $this->sendResponse($formattedTasks->toArray(), 'Daftar semua tugas berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar tugas.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int $divisionId
     * @return \Illuminate\Http\Response
     */
    public function indexTaskByDivision(Request $request, $divisionId)
    {
        try {
            $userId = Auth::id();

            $tasks = DailyTask::where('division_id', $divisionId)
                ->with([
                    'user:id,name',
                    'assign:id,name',
                    'division:id,name',
                    'project:id,name'
                ])
                ->addSelect([
                    'id',
                    'slug',
                    'name',
                    'start_date',
                    'end_date',
                    'user_id',
                    'assignment_user_id',
                    'division_id',
                    'daily_task_project_id',
                    'task_status_name' => TaskStatus::select('name')
                        ->whereColumn('task_statuses.id', 'daily_tasks.task_status_id')
                        ->limit(1),
                ])
                ->orderBy('start_date', 'desc')
                ->get();

            $formattedTasks = $tasks->map(function ($task) use ($userId) {
                return [
                    'id' => $task->id,
                    'slug' => $task->slug,
                    'name' => $task->name,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date,
                    'task_status_name' => $task->task_status_name ?? 'unknown',

                    'access_id' => $userId,
                    'user_id' => $task->user_id,
                    'assignment_user_id' => $task->assignment_user_id,
                    'user_name' => $task->user->name ?? 'N/A',
                    'assignment_user_name' => $task->assign->name ?? 'N/A',
                    'division_id' => $task->division->id ?? 'N/A',
                    'division_name' => $task->division->name ?? 'N/A',
                    'daily_task_project_id' => $task->project->id ?? 'N/A',
                    'daily_task_project_name' => $task->project->name ?? 'N/A'
                ];
            });

            return $this->sendResponse($formattedTasks->toArray(), 'Daftar tugas berdasarkan divisi berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar tugas divisi.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get Users by Division ID (UUID)
     * @param string $divisionId
     * @return \Illuminate\Http\Response
     */
    public function getUsersByDivision($divisionId)
    {
        try {
            // Cari divisi berdasarkan UUID
            $division = Division::find($divisionId);

            if (!$division) {
                return $this->sendError('Divisi tidak ditemukan.', [], 404);
            }

            // Ambil users melalui relasi belongsToMany (pivot division_users)
            $users = $division->users()
                ->select('users.id', 'users.name', 'users.email')
                ->get();

            return $this->sendResponse($users->toArray(), 'Daftar user berdasarkan divisi berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil data user.', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get Daily Tasks by User ID
     * * @param int $userIdParam
     * @return \Illuminate\Http\Response
     */
    public function indexTaskByUser($userIdParam)
    {
        try {
            $tasks = DailyTask::where(function ($q) use ($userIdParam) {
                    $q->where('user_id', $userIdParam)
                    ->orWhere('assignment_user_id', $userIdParam);
                })
                ->with([
                    'user:id,name',
                    'assign:id,name',
                    'division:id,name',
                    'project:id,name'
                ])
                ->addSelect([
                    'id',
                    'slug',
                    'name',
                    'start_date',
                    'end_date',
                    'user_id',
                    'assignment_user_id',
                    'task_status_name' => TaskStatus::select('name')
                        ->whereColumn('task_statuses.id', 'daily_tasks.task_status_id')
                        ->limit(1),
                ])
                ->orderBy('start_date', 'desc')
                ->get();

            $formattedTasks = $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'slug' => $task->slug,
                    'name' => $task->name,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date,
                    'task_status_name' => $task->task_status_name ?? 'unknown',
                    'user_name' => $task->user->name ?? 'N/A',
                    'assignment_user_name' => $task->assign->name ?? 'N/A',
                ];
            });

            return $this->sendResponse($formattedTasks->toArray(), 'Daftar tugas user berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar tugas user.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function indexToday(Request $request)
    {
        $allowedStatuses = [
            ParamSchema::TODO,
            ParamSchema::DOING,
            ParamSchema::INREVIEW,
            ParamSchema::NOTCOMPLATE
        ];

        try {
            $userId = Auth::id();
            $today = Carbon::today()->toDateString();

            $tasks = DailyTask::where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                    ->orWhere('assignment_user_id', $userId);
                })
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->whereIn('task_status_id', function ($query) use ($allowedStatuses) {
                    $query->select('id')
                        ->from('task_statuses')
                        ->whereIn('name', $allowedStatuses);
                })
                ->with([
                    'user:id,name',
                    'assign:id,name',
                ])
                ->addSelect([
                    'id',
                    'name',
                    'start_date',
                    'end_date',
                    'user_id',
                    'assignment_user_id',
                    'division_id',
                    'daily_task_project_id',
                    'task_status_name' => TaskStatus::select('name')
                        ->whereColumn('task_statuses.id', 'daily_tasks.task_status_id')
                        ->limit(1),
                ])
                ->orderBy('start_date', 'asc')
                ->get();

            $formattedTasks = $tasks->map(function ($task) use ($userId) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date,
                    'task_status_name' => $task->task_status_name ?? 'unknown',

                    
                    'access_id' => $userId,
                    'user_id' => $task->user_id,
                    'assignment_user_id' => $task->assignment_user_id,
                    'user_name' => $task->user->name ?? 'N/A',
                    'assignment_user_name' => $task->assign->name ?? 'N/A',
                    'division_id' => $task->division->id ?? 'N/A',
                    'division_name' => $task->division->name ?? 'N/A',
                    'daily_task_project_id' => $task->project->id ?? 'N/A',
                    'daily_task_project_name' => $task->project->name ?? 'N/A'
                ];
            });

            return $this->sendResponse($formattedTasks->toArray(), 'Daftar tugas hari ini berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar tugas hari ini.', ['error' => $e->getMessage()]);
        }
    }

    public function indexTomorrow(Request $request)
    {
        $allowedStatuses = [
            ParamSchema::TODO,
            ParamSchema::DOING,
            ParamSchema::INREVIEW,
            ParamSchema::NOTCOMPLATE
        ];

        try {
            $userId = Auth::id();
            $today = Carbon::today()->toDateString();

            $tasks = DailyTask::where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                    ->orWhere('assignment_user_id', $userId);
                })
                ->whereDate('start_date', '>', $today)
                ->whereDate('end_date', '>', $today)
                ->whereIn('task_status_id', function ($query) use ($allowedStatuses) {
                    $query->select('id')
                        ->from('task_statuses')
                        ->whereIn('name', $allowedStatuses);
                })
                ->with([
                    'user:id,name',
                    'assign:id,name',
                ])
                ->addSelect([
                    'id',
                    'name',
                    'start_date',
                    'end_date',
                    'user_id',
                    'assignment_user_id',
                    'division_id',
                    'daily_task_project_id',
                    'task_status_name' => TaskStatus::select('name')
                        ->whereColumn('task_statuses.id', 'daily_tasks.task_status_id')
                        ->limit(1),
                ])
                ->orderBy('start_date', 'asc')
                ->get();

            $formattedTasks = $tasks->map(function ($task) use ($userId) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date,
                    'task_status_name' => $task->task_status_name ?? 'unknown',

                    'access_id' => $userId,
                    'user_id' => $task->user_id,
                    'assignment_user_id' => $task->assignment_user_id,
                    'user_name' => $task->user->name ?? 'N/A',
                    'assignment_user_name' => $task->assign->name ?? 'N/A',
                    'division_id' => $task->division->id ?? 'N/A',
                    'division_name' => $task->division->name ?? 'N/A',
                    'daily_task_project_id' => $task->project->id ?? 'N/A',
                    'daily_task_project_name' => $task->project->name ?? 'N/A'
                ];
            });

            return $this->sendResponse($formattedTasks->toArray(), 'Daftar tugas untuk besok berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar tugas besok.', ['error' => $e->getMessage()]);
        }
    }


    public function indexOverdue(Request $request)
    {
        $allowedStatuses = [
            ParamSchema::TODO,
            ParamSchema::DOING,
            ParamSchema::INREVIEW,
            ParamSchema::NOTCOMPLATE
        ];

        try {
            $userId = Auth::id();
            $today = Carbon::today()->toDateString();

            $tasks = DailyTask::where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                    ->orWhere('assignment_user_id', $userId);
                })
                ->whereDate('end_date', '<', $today)
                ->whereIn('task_status_id', function ($query) use ($allowedStatuses) {
                    $query->select('id')
                        ->from('task_statuses')
                        ->whereIn('name', $allowedStatuses);
                })
                ->with([
                    'user:id,name',
                    'assign:id,name',
                ])
                ->addSelect([
                    'id',
                    'name',
                    'start_date',
                    'end_date',
                    'user_id',
                    'assignment_user_id',
                    'division_id',
                    'daily_task_project_id',
                    'task_status_name' => TaskStatus::select('name')
                        ->whereColumn('task_statuses.id', 'daily_tasks.task_status_id')
                        ->limit(1),
                ])
                ->orderBy('end_date', 'desc')
                ->get();

            $formattedTasks = $tasks->map(function ($task) use ($userId) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date,
                    'task_status_name' => $task->task_status_name ?? 'unknown',

                    'access_id' => $userId,
                    'user_id' => $task->user_id,
                    'assignment_user_id' => $task->assignment_user_id,
                    'user_name' => $task->user->name ?? 'N/A',
                    'assignment_user_name' => $task->assign->name ?? 'N/A',
                    'division_id' => $task->division->id ?? 'N/A',
                    'division_name' => $task->division->name ?? 'N/A',
                    'daily_task_project_id' => $task->project->id ?? 'N/A',
                    'daily_task_project_name' => $task->project->name ?? 'N/A'
                ];
            });

            return $this->sendResponse($formattedTasks->toArray(), 'Daftar tugas yang overdue berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar tugas overdue.', ['error' => $e->getMessage()]);
        }
    }


    /**
     * * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $userId = Auth::id();
            $task = DailyTask::userTasks($userId)
                ->with([
                'taskStatus:id,name',
                'user:id,name',
                'project:id,name',
                'category:id,name',
                'type:id,name',
                'assign:id,name',
                'head:id,name',
                'dataProject:id,title',
                'division:id,name',
                'objective:id,name',

                'media:id,daily_task_id,file_path,file_type,status', 
                'taskMedia:id,daily_task_id,file_path,file_type,status', 
                'keyResults:id,result', 

                'children:id,name,child_daily_task_id',

                'recurringRule:id,frequency,interval,by_day,by_month_day,by_month,until,start_date'

                ])
                ->where(function ($query) use ($id) {
                    $query->where('id', $id)
                          ->orWhere('slug', $id);
                })
                ->first();

            if (is_null($task)) {
                return $this->sendError('Tugas tidak ditemukan atau bukan milik Anda.', ['error' => 'Not Found'], 404);
            }
            
            $taskArray = $task->toArray();
            
            $taskArray['access_id'] = $userId;
            $taskArray['user_name'] = $task->user->name ?? 'N/A';
            $taskArray['project_name'] = $task->project->name ?? 'N/A';
            $taskArray['project_data_name'] = $task->dataProject->title ?? 'N/A';
            $taskArray['division_name'] = $task->division->name ?? 'N/A';
            $taskArray['objective_name'] = $task->objective->name ?? 'N/A';
            $taskArray['daily_task_category_name'] = $task->category->name ?? 'N/A';
            $taskArray['daily_task_type_name'] = $task->type->name ?? 'N/A';
            $taskArray['assignment_user_name'] = $task->assign->name ?? 'N/A'; 
            $taskArray['child_daily_task_name'] = $task->head->name ?? 'N/A'; 
            $taskArray['task_status_name'] = $task->taskStatus->name ?? 'N/A';

            $taskArray['media_files'] = $task->media->map(function ($file) {
            return [
                'id' => $file->id,
                'file_type' => $file->file_type,
                'file_path' => $file->file_path,
                'status' => $file->status,
            ];
        });

        $taskArray['task_media_files'] = $task->taskMedia->map(function ($file) {
            return [
                'id' => $file->id,
                'file_name' => $file->file_type,
                'file_path' => $file->file_path,
                'status' => $file->status,
            ];
        });

        $taskArray['key_results'] = $task->keyResults->map(function ($kr) {
            return [
                'key_result_id' => $kr->id,
                'key_result_name' => $kr->result,
            ];
        });

        if ($task->recurringRule) {
            $taskArray['recurring'] = [
                'frequency' => $task->recurringRule->frequency,
                'interval' => $task->recurringRule->interval,
                'by_day' => $task->recurringRule->by_day,
                'by_month_day' => $task->recurringRule->by_month_day,
                'by_month' => $task->recurringRule->by_month,
                'until' => $task->recurringRule->until,
                'start_date' => $task->recurringRule->start_date,
            ];
        } else {
            $taskArray['recurring'] = null;
        }

        $taskArray['children_tasks'] = $task->children->map(function ($child) {
            return [
                'id' => $child->id,
                'name' => $child->name,
            ];
        });
            
            unset($taskArray['user']);
            unset($taskArray['project']);
            unset($taskArray['data_project']);
            unset($taskArray['division']);
            unset($taskArray['objective']);
            unset($taskArray['category']);
            unset($taskArray['type']);
            unset($taskArray['assign']); 
            unset($taskArray['head']);
            unset($taskArray['task_status']); 

            unset($taskArray['media']);
            unset($taskArray['task_media']);
            // unset($taskArray['key_results']);
            unset($taskArray['children']);

            return $this->sendResponse($taskArray, 'Detail tugas berhasil diambil.');
            
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil detail tugas.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (!Auth::check()) {
            return $this->sendError('Akses Ditolak.', ['error' => 'Pengguna belum login.'], 401);
        }

        $input = $request->all();
        $userId = Auth::id();
        
        $validator = Validator::make($input, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date', 
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'daily_task_category_id' => 'required|exists:daily_task_categories,id',
            'daily_task_type_id' => 'required|exists:daily_task_types,id',
            'assignment_user_id' => 'nullable|exists:users,id',
            'project_id' => 'nullable|exists:projects,id', 
            'daily_task_project_id' => 'nullable|exists:daily_task_projects,id',
            'objective_id' => 'nullable|exists:objectives,id',
            // 'recurring_days' => 'nullable|json',
            'division_id' => 'nullable|exists:divisions,id',
            'child_daily_task_id' => 'nullable|exists:daily_tasks,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120', 
            //Recurring
            'recurring' => 'nullable|array',
            'recurring.frequency' => 'required_with:recurring|string|in:DAILY,WEEKLY,MONTHLY,YEARLY',
            'recurring.until' => 'nullable|date',
            'recurring.by_day' => 'nullable|array',
            'recurring.by_day.*' => 'string|in:MO,TU,WE,TH,FR,SA,SU',
            'recurring.by_month_day' => 'nullable|array',
            'recurring.by_month_day.*' => 'integer|min:1|max:31',
            'recurring.by_month' => 'nullable|array',
            'recurring.by_month.*' => 'integer|min:1|max:12',
        ]);

        if($validator->fails()){
            return $this->sendError('Validasi Gagal.', $validator->errors());     
        }
        
        if (!Route::has('dailytask.show')) {
             Log::warning("Route 'dailytask.show' tidak terdefinisi. Notifikasi akan dinonaktifkan.");
        }

        DB::beginTransaction();

        try {
            $startDate = $input['start_date'] ?? null;
            $endDate = $input['end_date'] ?? null;
            $assigneeId = $input['assignment_user_id'] ?? null;

            if ($startDate && $endDate && $assigneeId) {
                $status = TaskStatus::where('name', ParamSchema::TODO)->firstOrFail();
            } else {
                $status = TaskStatus::where('name', ParamSchema::BACKLOG)->firstOrFail();
            }

            $dailyTask = new DailyTask();
            $dailyTask->user_id = $userId;
            $dailyTask->task_status_id = $status->id;
            $dailyTask->start_date = $startDate; 
            $dailyTask->end_date = $endDate;
            $dailyTask->assignment_user_id = $assigneeId ?? $userId;
            $dailyTask->daily_task_category_id = $input['daily_task_category_id'];
            $dailyTask->daily_task_type_id = $input['daily_task_type_id'];
            $dailyTask->project_id = $input['project_id'] ?? NULL;
            $dailyTask->daily_task_project_id = $input['daily_task_project_id'] ?? NULL;
            $dailyTask->division_id = $input['division_id'] ?? NULL;
            $dailyTask->child_daily_task_id = $input['child_daily_task_id'] ?? NULL;
            $dailyTask->name = $input['name'];
            $dailyTask->description = $input['description'] ?? null;
            $dailyTask->point = 0;
            $dailyTask->objective_id = $input['objective_id'] ?? NULL;
            // $dailyTask->recurring_days = $input['recurring_days'] ?? NULL;

            //Recurring
            $type = DailyTaskType::find($input['daily_task_type_id']);
            if ($type && $type->name == ParamSchema::RECURRING && isset($input['recurring'])) {
                $recurring = $input['recurring'];

                $rule = RecurringRule::create([
                    'frequency' => $recurring['frequency'],
                    'interval' => 1,
                    'by_day' => $recurring['by_day'] ?? null,
                    'by_month_day' => $recurring['by_month_day'] ?? null,
                    'by_month' => $recurring['by_month'] ?? null,
                    'until' => $recurring['until'] ?? null,
                    'start_date' => $dailyTask->start_date,
                ]);

                $dailyTask->recurring_rule_id = $rule->id;
            }
            
            $dailyTask->save();

            //Key Result

            $keyResults = $request->input('key_results') ?? $request->input('key_result');

            if (!empty($keyResults)) {
                if (!is_array($keyResults)) {
                    $keyResults = [$keyResults];
                }
                $dailyTask->keyResults()->attach($keyResults);
            }

            if ($request->hasFile('attachments')) {
                 foreach ($request->file('attachments') as $file) 
                 {
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
            
            if (Route::has('dailytask.show')) {
                $inboxHelper = new InboxHelper();
                $directUrl = route('dailytask.show', ['dailytask' => $dailyTask->slug]);

                if ($dailyTask->assignment_user_id !== $userId) {
                    $inboxHelper->sent(
                        $dailyTask->assignment_user_id,
                        $userId,
                        Auth::user()->name . ' Menugaskan ' . $dailyTask->name,
                        $directUrl
                    );
                }
            }
            
            DB::commit();

            return $this->sendResponse($dailyTask->toArray(), 'Tugas berhasil ditambahkan!');
        } catch (\Throwable $th) {
            DB::rollback();

            return $this->sendError('Gagal menambahkan tugas.', ['error' => $th->getMessage()]);
        }
    }

    /**
     * * @param \Illuminate\Http\Request $request
     * @param string $id (UUID/Slug DailyTask)
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        if (!Auth::check()) {
            return $this->sendError('Akses Ditolak.', ['error' => 'Pengguna belum login.'], 401);
        }

        $input = $request->all();
        $userId = Auth::id();
        $user = Auth::user();

        DB::beginTransaction();
        try {
            $task = DailyTask::withTrashed()
                ->where(function ($query) use ($slug) {
                    $query->where('id', $slug)
                        ->orWhere('slug', $slug);
                })
                ->first();

            if (!$task) {
                return $this->sendError('Tugas tidak ditemukan.', ['error' => 'Not Found'], 404);
            }

            if ($task->deleted_at) {
                return $this->sendError('Tugas sudah dihapus.', ['error' => 'Deleted Task'], 404);
            }

            if ($task->user_id !== $userId) {
                return $this->sendError('Tidak diizinkan memperbarui tugas ini.', ['error' => 'Forbidden'], 403);
            }

            $validator = Validator::make($input, [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'daily_task_category_id' => 'required|exists:daily_task_categories,id',
                'daily_task_type_id' => 'required|exists:daily_task_types,id',
                'assignment_user_id' => 'nullable|exists:users,id',
                'project_id' => 'nullable|exists:projects,id',
                'daily_task_project_id' => 'nullable|exists:daily_task_projects,id',
                'objective_id' => 'nullable|exists:objectives,id',
                'task_status_id' => 'nullable|exists:task_statuses,id',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
                'point' => 'nullable|integer',
                'division_id' => 'nullable|exists:divisions,id',

                'recurring' => 'nullable|array',
                'recurring.frequency' => 'nullable|string|in:DAILY,WEEKLY,MONTHLY,YEARLY',
                'recurring.until' => 'nullable|date',
                'recurring.by_day' => 'nullable|array',
                'recurring.by_day.*' => 'string|in:MO,TU,WE,TH,FR,SA,SU',
                'recurring.by_month_day' => 'nullable|array',
                'recurring.by_month_day.*' => 'integer|min:1|max:31',
                'recurring.by_month' => 'nullable|array',
                'recurring.by_month.*' => 'integer|min:1|max:12',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validasi gagal.', $validator->errors(), 422);
            }

            $task->name = $input['name'];
            $task->description = $input['description'] ?? $task->description;
            $task->start_date = $input['start_date'] ?? $task->start_date;
            $task->end_date = $input['end_date'] ?? $task->end_date;
            $task->daily_task_category_id = $input['daily_task_category_id'];
            $task->daily_task_type_id = $input['daily_task_type_id'];
            $task->assignment_user_id = $input['assignment_user_id'] ?? $task->assignment_user_id;
            $task->project_id = $input['project_id'] ?? $task->project_id;
            $task->daily_task_project_id = $input['daily_task_project_id'] ?? $task->daily_task_project_id;
            $task->objective_id = $input['objective_id'] ?? $task->objective_id;

            if ($request->has('recurring')) {
                $rec = is_array($input['recurring']) ? $input['recurring'] : null;
                $type = DailyTaskType::find($task->daily_task_type_id);

                if ($type && $type->name == ParamSchema::RECURRING && !empty($rec)) {
                    $rule = null;
                    if (!empty($task->recurring_rule_id)) {
                        $rule = RecurringRule::withTrashed()->find($task->recurring_rule_id);
                    }

                    if (!$rule) {
                        $rule = new RecurringRule();
                    }

                    $recurringData = [
                        'frequency' => $rec['frequency'] ?? $rule->frequency ?? null,
                        'interval' => $rec['interval'] ?? $rule->interval ?? 1,
                        'by_day' => null,
                        'by_month_day' => null,
                        'by_month' => null,
                        'until' => $rec['until'] ?? $rule->until ?? null,
                        'start_date' => $task->start_date,
                    ];

                    $freq = $recurringData['frequency'];
                    if ($freq === 'WEEKLY') {
                        $recurringData['by_day'] = $rec['by_day'] ?? $rule->by_day ?? null;
                    } elseif ($freq === 'MONTHLY') {
                        $recurringData['by_month_day'] = $rec['by_month_day'] ?? $rule->by_month_day ?? null;
                    } elseif ($freq === 'YEARLY') {
                        $recurringData['by_month_day'] = $rec['by_month_day'] ?? $rule->by_month_day ?? null;
                        $recurringData['by_month'] = $rec['by_month'] ?? $rule->by_month ?? null;
                    }

                    $rule->fill($recurringData);
                    $rule->save();

                    $task->recurring_rule_id = $rule->id;
                } else {
                    $typeName = $type->name ?? null;
                    if ($typeName !== ParamSchema::RECURRING && $task->recurring_rule_id) {
                        RecurringRule::where('id', $task->recurring_rule_id)->delete();
                        $task->recurring_rule_id = null;
                    }
                }
            }

            if ($request->has('key_results')) {
                $keyResults = $request->input('key_results');
                if (!is_array($keyResults)) {
                    $keyResults = [$keyResults];
                }
                $task->keyResults()->sync($keyResults);
            }

            if (isset($input['task_status_id'])) {
                $oldStatus = $task->task_status_id;
                $task->task_status_id = $input['task_status_id'];

                if ($oldStatus != $task->task_status_id) {
                    Log::info("UPDATE STATUS: Task {$task->id} dari {$oldStatus} ke {$task->task_status_id}");
                    
                    $taskStatus = TaskStatus::find($input['task_status_id']);
                    
                    if ($taskStatus) {
                        if ($taskStatus->name == ParamSchema::COMPLATE || $taskStatus->name == ParamSchema::NOTCOMPLATE) {
                            
                            if ($taskStatus->name == ParamSchema::COMPLATE) {
                                $point = $request->point ?? 0;
                                
                                if ($point > 0) {
                                    $approvalValidator = Validator::make($input, [
                                        'division_id' => 'required|exists:divisions,id',
                                    ]);

                                    if ($approvalValidator->fails()) {
                                        return $this->sendError('Validasi kuota gagal.', $approvalValidator->errors(), 422);
                                    }

                                    $check = $this->checkDivisionQuota(new Request([
                                        'division_id' => $request->division_id,
                                        'point'       => $point
                                    ]));

                                    if (!($check->original['success'] ?? false)) {
                                        return response()->json([
                                            'success' => false,
                                            'message' => $check->original['message']
                                        ]);
                                    }

                                    $task->division_id = $request->division_id;
                                    $task->division_quota_lock_id = $check->original['quota_lock_id'];
                                }

                                if ($task->assign) {
                                    \App\Helpers\XpHelper::award($task->assign, $task, "Approval Dailytask via Update");
                                    \App\Helpers\ChallengeProgressHelper::userCheckAndGiveReward($task->assign->id);
                                }

                                $task->point = $point;
                                $task->approved = true;
                            } 
                            
                            if ($taskStatus->name == ParamSchema::NOTCOMPLATE) {
                                $task->approved = false;
                                $task->report_note = null;
                                $task->submit = null;
                                $task->status_submit = null;
                                $task->point = 0;
                            }

                            if (method_exists($this, 'statusrecord')) {
                                $this->statusrecord($task, $taskStatus);
                            }
                        }
                    }
                }
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
                        'daily_task_id' => $task->id,
                        'file_path' => $path,
                        'file_type' => $mediaType,
                        'status' => ParamSchema::FILETASK,
                    ]);
                }
            }

            $task->save();
            DB::commit();

            return $this->sendResponse($task->toArray(), 'Tugas berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Gagal update task: " . $th->getMessage());
            return $this->sendError('Gagal memperbarui tugas.', ['error' => $th->getMessage()]);
        }
    }


    /**
     * @param string $slug (Slug DailyTask)
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        if (!Auth::check()) {
            return $this->sendError('Akses Ditolak.', ['error' => 'Pengguna belum login.'], 401);
        }

        $userId = Auth::id();

        DB::beginTransaction();
        try {
            $query = DailyTask::withTrashed()->where(function ($query) use ($slug) {
                    $query->where('id', $slug)
                        ->orWhere('slug', $slug);
                });
            
            $task = $query->first();

            if (!$task) {
                DB::rollback();
                return $this->sendError('Tugas tidak ditemukan.', ['error' => 'Not Found'], 404);
            }
            
            if ($task->deleted_at !== null) {
                $task->forceDelete(); 
                DB::commit();
                return $this->sendResponse([], 'Tugas berhasil dihapus (Permanen)!');
            }
            
            if ($task->user_id !== $userId) {
                DB::rollback();
                return $this->sendError('Anda tidak diizinkan menghapus tugas ini.', ['error' => 'Forbidden'], 403);
            }
            
            $task->delete();
            
            DB::commit();

            return $this->sendResponse([], 'Tugas berhasil dihapus!');

        } catch (\Throwable $th) {
            DB::rollback();
            return $this->sendError('Gagal menghapus tugas.', ['error' => $th->getMessage()]);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function updateMedia(Request $request, $slug)
    {
        if (!Auth::check()) {
            return $this->sendError('Akses ditolak.', ['error' => 'Pengguna belum login.'], 401);
        }

        $request->validate([
            'media.*' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $userId = Auth::id();

            $task = DailyTask::where(function ($query) use ($slug) {
                    $query->where('slug', $slug)->orWhere('id', $slug);
                })
                ->where('user_id', $userId)
                ->first();

            if (!$task) {
                return $this->sendError('Tugas tidak ditemukan atau tidak diizinkan.', ['error' => 'Not Found'], 404);
            }

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
                        'daily_task_id' => $task->id,
                        'file_path' => $path,
                        'file_type' => $mediaType,
                        'status' => ParamSchema::FILETASK,
                    ]);
                }
            }

            DB::commit();
            return $this->sendResponse($task->toArray(), 'Media tugas berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Gagal update media: ' . $th->getMessage());
            return $this->sendError('Gagal memperbarui media.', ['error' => $th->getMessage()]);
        }
    }

    /**
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function deleteMedia($id)
    {
        if (!Auth::check()) {
            return $this->sendError('Akses ditolak.', ['error' => 'Pengguna belum login.'], 401);
        }

        try {
            $media = DailyTaskMedia::findOrFail($id);

            $task = DailyTask::find($media->daily_task_id);
            if (!$task || $task->user_id !== Auth::id()) {
                return $this->sendError('Tidak diizinkan menghapus media ini.', ['error' => 'Forbidden'], 403);
            }
            
            Storage::delete($media->file_path);

            $media->delete();

            return $this->sendResponse([], 'Media berhasil dihapus.');
        } catch (\Throwable $th) {
            Log::error('Gagal hapus media: ' . $th->getMessage());
            return $this->sendError('Gagal menghapus media.', ['error' => $th->getMessage()]);
        }
    }


    /**
     * @param \Illuminate\Http\Request $request
     * @param string $slug (UUID/Slug DailyTask)
     * @return \Illuminate\Http\Response
     */
    public function statusChange(Request $request, $slug)
    {
        $userId = Auth::id();
        DB::beginTransaction();
        try {
            $dailyTask = DailyTask::byCompany(Auth::user()->company_id)
                ->where(function ($query) use ($slug) {
                    $query->where('id', $slug)
                          ->orWhere('slug', $slug);
                })
                ->firstOrFail();

            if ($dailyTask->assignment_user_id !== $userId) {
            }

            $doingStatus = TaskStatus::where('name', ParamSchema::DOING)->firstOrFail();
            
            if ($dailyTask->task_status_id === $doingStatus->id) {
                DB::rollBack();
                return $this->sendResponse($dailyTask->toArray(), 'Status tugas sudah ' . ParamSchema::DOING . '. Tidak ada perubahan dilakukan.', 200);
            }

            $dailyTask->task_status_id = $doingStatus->id;
            $dailyTask->save();

            if (method_exists($this, 'message') && method_exists($this, 'statusrecord')) {
                $this->message($dailyTask->id, 'report', 'Tugas ' . $dailyTask->name . ' dikerjakan oleh ' . Auth::user()->name);
                $this->statusrecord($dailyTask, $doingStatus);
            } else {
                 Log::warning('Helper message() atau statusrecord() tidak ditemukan di controller.');
            }

            DB::commit();

            return $this->sendResponse($dailyTask->toArray(), 'Status tugas berhasil diperbarui menjadi ' . ParamSchema::DOING . '!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            return $this->sendError('Tugas atau Status tidak ditemukan.', ['error' => 'Not Found'], 404);
        } catch (\Throwable $th) {
            Log::error("Error saat mengubah status tugas mobile: " . $th->getMessage());
            DB::rollback();
            return $this->sendError('Gagal mengubah status tugas.', ['error' => $th->getMessage()]);
        }
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

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function report(Request $request, $slug)
    {
        if (!Auth::check()) {
            return $this->sendError('Akses ditolak.', ['error' => 'Pengguna belum login.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'note' => 'required|string',
            'media' => 'nullable',
            'media.*' => 'nullable|file|max:10240', // max 10 MB
        ], [
            'note.required' => 'Catatan wajib diisi.',
            'note.string' => 'Catatan harus berupa teks.',
            'media.*.file' => 'Setiap media harus berupa file.',
            'media.*.max' => 'Ukuran file media tidak boleh lebih dari 10 MB.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal.', $validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $dailytask = DailyTask::byCompany($user->company_id)
                ->where('slug', $slug)
                ->firstOrFail();

            $inReview = TaskStatus::where('name', ParamSchema::INREVIEW)->firstOrFail();
            $dailytask->report_note = $request->note;
            $dailytask->task_status_id = $inReview->id;

            if ($request->hasFile('media')) {
                $files = $request->file('media');

                if (!is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    $timestamp = time();
                    $randomString = rand(100, 999);
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileName = $originalName . '_' . $timestamp . '_' . $randomString . '.' . $extension;

                    $path = $file->storeAs('media', $fileName);
                    $mediaType = $file->getClientMimeType();

                    \App\Models\DailyTaskMedia::create([
                        'daily_task_id' => $dailytask->id,
                        'file_path' => $path,
                        'file_type' => $mediaType,
                    ]);
                }
            }
            $submitDate = Carbon::now();
            $dailytask->submit = $submitDate;

            $endDate = Carbon::parse($dailytask->end_date)->endOfDay();
            $submitDateCompare = Carbon::parse($dailytask->submit)->startOfDay();

            if($dailytask->status_submit != ParamSchema::PINALTY_NOT_PROGRESS){
                $dailytask->status_submit = ($submitDateCompare->lessThanOrEqualTo($endDate)) ? ParamSchema::ONTIME : ParamSchema::LATE;
            }

            if (method_exists($this, 'message')) {
                $this->message($dailytask->id, 'report', 'Membuat Laporan Tugas ' . $dailytask->name);
            }

            if (method_exists($this, 'statusrecord')) {
                $this->statusrecord($dailytask, $inReview);
            }

            $dailytask->save();
            $directUrl = route('dailytask.show', ['dailytask' => $dailytask->slug]);

            if ($user->id == $dailytask->assignment_user_id) {
                $userTo = $dailytask->user_id;
            } elseif ($user->id == $dailytask->user_id) {
                $userTo = $dailytask->assignment_user_id;
            } else {
                $userTo = $dailytask->assignment_user_id;
                if (method_exists($this, 'sentInbox')) {
                    $this->sentInbox($dailytask->user_id, 'Membuat Laporan pada Tugas ' . $dailytask->name, $directUrl);
                }
            }

            DB::commit();
            $mediaCount = 0;
            if ($request->hasFile('media')) {
                $mediaFiles = $request->file('media');
                $mediaCount = is_array($mediaFiles) ? count($mediaFiles) : 1;
            }

            return $this->sendResponse([
                'slug' => $dailytask->slug,
                'report_note' => $dailytask->report_note,
                'status_submit' => $dailytask->status_submit,
                'task_status' => $inReview->name,
                'submit_at' => $dailytask->submit,
                'media_uploaded' => $mediaCount,
            ], 'Laporan tugas berhasil dikirim.');
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->sendError('Gagal membuat laporan.', ['error' => $th->getMessage()]);
        }
    }

    /**
     * * @return \Illuminate\Http\Response
     */
    public function indexTaskStatuses()
    {
        try {
            $data = TaskStatus::all();
            return $this->sendResponse($data->toArray(), 'Daftar status tugas berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar status tugas.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * * @return \Illuminate\Http\Response
     */
    public function indexDailyTaskProjects()
    {
        try {
            $user = Auth::user();
            $data = DailyTaskProject::byCompany($user->company_id)->get();

            return $this->sendResponse($data->toArray(), 'Daftar project tugas harian berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar project tugas harian.', ['error' => $e->getMessage()]);
        }
    }


    /**
     * * @return \Illuminate\Http\Response
     */
    public function indexProjects()
    {
        try {
            $user = Auth::user();
            $data = Project::byCompany($user->company_id)->get();

            return $this->sendResponse($data->toArray(), 'Daftar project berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar project.', ['error' => $e->getMessage()]);
        }
    }


    /**
     * * @return \Illuminate\Http\Response
     */
    public function indexDailyTaskCategories()
    {
        try {
            $user = Auth::user();
            $data = DailyTaskCategory::byCompany($user->company_id)->get();

            return $this->sendResponse($data->toArray(), 'Daftar kategori tugas harian berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar kategori tugas harian.', ['error' => $e->getMessage()]);
        }
    }


    /**
     * * @return \Illuminate\Http\Response
     */
    public function indexDailyTaskTypes()
    {
        try {
            $data = DailyTaskType::all();
            return $this->sendResponse($data->toArray(), 'Daftar tipe tugas harian berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar tipe tugas harian.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function indexDailyTaskObjectives()
    {
        try {
            $user = Auth::user();
            $divisionIds = $user->divisions->pluck('id');

            $data = Objective::byCompany($user->company_id)
                // ->whereHas('division', function ($q) use ($divisionIds) {
                //     $q->whereIn('id', $divisionIds);
                // })
                ->get(['id', 'name']);

            return $this->sendResponse($data->toArray(), 'Daftar objektif berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar objektif.', ['error' => $e->getMessage()]);
        }
    }


    /**
     * @return \Illuminate\Http\Response
     */
    public function indexDailyTaskUsers()
    {
        try {
            $data = User::select('id', 'name')->get();
            return $this->sendResponse($data->toArray(), 'Daftar pengguna berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar pengguna.', ['error' => $e->getMessage()]);
        }
    }


    /**
     * @return \Illuminate\Http\Response
     */
    public function indexDivision()
    {
        try {
            $user = auth()->user();
            $data = \App\Models\Division::select('id', 'name')
            ->byCompany($user->company_id) 
            ->get();
            return $this->sendResponse($data->toArray(), 'Daftar divisi berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar divisi.', ['error' => $e->getMessage()]);
        }
    }

        /**
     * @param int $objectiveId
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexKeyResults($objectiveId)
    {
        try {
            $objective = Objective::find($objectiveId);
            if (!$objective) {
                return $this->sendError('Objektif tidak ditemukan.', ['objective_id' => $objectiveId], 404);
            }

            $keyResults = $objective->keyResults()
                ->select('id', 'result')
                ->get();

            return $this->sendResponse($keyResults->toArray(), 'Daftar key result berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar key result.', ['error' => $e->getMessage()]);
        }
    }


    /**
     * @param \Illuminate\Http\Request $request
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function approval(Request $request, $slug)
    {
        if (!Auth::check()) {
            return $this->sendError('Akses Ditolak.', ['error' => 'Pengguna belum login.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'point' => 'nullable|integer',
            'task_status' => 'required|exists:task_statuses,id',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal.', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $taskStatuss = TaskStatus::find($request->task_status);

            $dailytask = DailyTask::byCompany(Auth::user()->company_id)
                ->where(function($q) use ($slug){
                    $q->where('id',$slug)->orWhere('slug',$slug);
                })->firstOrFail();

            if ($request->point > 0) {
                $request->validate([
                    'point' => 'required|integer|min:1',
                    'division_id' => 'required|exists:divisions,id',
                ]);

                $check = $this->checkDivisionQuota(new Request([
                    'division_id' => $request->division_id,
                    'point'       => $request->point
                ]));

                if (!($check->original['success'] ?? false)) {
                    return response()->json([
                        'success' => false,
                        'message' => $check->original['message']
                    ]);
                }

                $dailytask->division_id = $request->division_id;
                $dailytask->division_quota_lock_id = $check->original['quota_lock_id'];
            }

            \App\Helpers\XpHelper::award($dailytask->assign, $dailytask, "Approval Dailytask");
            \App\Helpers\ChallengeProgressHelper::userCheckAndGiveReward($dailytask->assign->id);

            $dailytask->point = $request->point ?? 0;
            $dailytask->task_status_id = $taskStatuss->id;
            $dailytask->approved = $taskStatuss->name == ParamSchema::COMPLATE ? true : false;

            // record message
            $messageType = ($taskStatuss->name == ParamSchema::COMPLATE) 
                ? 'approvement' : 'reject';

            if (method_exists($this, 'statusrecord')) {
                $this->statusrecord($dailytask, $taskStatuss);
            }

            if ($taskStatuss->name == ParamSchema::NOTCOMPLATE) {
                $dailytask->report_note = null;
                $dailytask->submit = null;
                $dailytask->status_submit = null;
            }

            $dailytask->save();
            DB::commit();

            return $this->sendResponse($dailytask->toArray(), 'Task updated successfully!');

        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());

            return $this->sendError('Gagal Approve Task.', [
                'error' => $th->getMessage()
            ]);
        }
    }

    /**
     * GenerateS3 signed URL for viewing media file
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateMediaUrl(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'path' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validasi Gagal.', $validator->errors(), 422);
            }

            $path = $request->path;

            $url = s3_asset(true, 10, $path);

            return $this->sendResponse([
                'url' => $url,
                'expired_in_minutes' => 10,
            ], 'Signed URL berhasil dibuat.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal membuat signed URL.', [
                'error' => $e->getMessage(),
            ]);
        }
    }


    /**
     * Cek kuota divisi
     */
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

        if ($point <= 0) {
            return response()->json([
                'success' => true,
                'message' => 'Poin nol',
                'remaining' => null
            ]);
        }

        $checkDailyTask = $request->exclude_task_id
            ? DailyTask::where('id', $request->exclude_task_id)->first()
            : null;

        if ($checkDailyTask && $checkDailyTask->division_id == $divisionId && $checkDailyTask->division_quota_lock_id) {
            $quota = DivisionQuotaLock::where('division_id', $divisionId)
                ->where('id', $checkDailyTask->division_quota_lock_id)
                ->first();
        } else {
            $quota = DivisionQuotaLock::where('division_id', $divisionId)
                ->where('month', $month)
                ->where('year', $year)
                ->first();
        }

        if (!$quota) {
            return response()->json([
                'success' => false,
                'message' => 'Kuota tidak tersedia. Tambahkan kuota divisi atau hubungi admin.',
            ]);
        }

        $used = DailyTask::where('division_quota_lock_id', $quota->id)
            ->when($request->exclude_task_id, function ($query) use ($request) {
                $query->where('id', '!=', $request->exclude_task_id);
            })
            ->sum('point');

        $remaining = $quota->locked_quota - $used;

        if ($point > $remaining) {
            return response()->json([
                'success' => false,
                'message' => 'Kuota tidak cukup. Sisa: ' . $remaining . ' poin.',
            ]);
        }

        return response()->json([
            'success' => true,
            'remaining' => $remaining,
            'quota_lock_id' => $quota->id,
        ]);
    }



}