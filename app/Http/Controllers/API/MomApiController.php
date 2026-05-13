<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Http\Requests\MomStoreRequest;
use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

use App\Models\Mom;
use App\Models\User;
use App\Models\MomTask;
use App\Models\Company;
use App\Models\Project;
use App\Models\Meeting;
use App\Models\MomAgenda;
use App\Models\Objective;
use App\Models\DailyTask;
use App\Models\TaskStatus;
use App\Models\DailyTaskType;
use App\Models\DailyTaskMessage;
use App\Models\DailyTaskStatusRecord;

use App\Helpers\InboxHelper;

class MomApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $moms = Mom::byCompany(Auth::user()->company_id)
                ->with(['meeting', 'project', 'user', 'agendas.tasks'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data MoM berhasil diambil',
                'data' => $moms
            ], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data MoM',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get data for creating new MoM
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        try {
            $projects = Project::with(['meetings.participants:id,name'])
                ->byCompany(Auth::user()->company_id)
                ->get();
            $objectives = Objective::byCompany(Auth::user()->company_id)->get();
            $users = User::byCompany(Auth::user()->company_id)->get();
            $meetings = Meeting::byCompany(Auth::user()->company_id)->get();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diambil',
                'data' => [
                    'projects' => $projects,
                    'objectives' => $objectives,
                    'users' => $users,
                    'meetings' => $meetings
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'meeting_id' => 'nullable|exists:meetings,id',
            'project_id' => 'nullable|uuid|exists:projects,id',
            'mom_date' => 'required|date',
            'notes' => 'nullable|string',
            'agendas' => 'required|array',
            'agendas.*.title' => 'required|string',
            'agendas.*.discussion_notes' => 'nullable|string',
            'agendas.*.tasks' => 'nullable|array',
            'agendas.*.tasks.*.title' => 'required|string',
            'agendas.*.tasks.*.start_date' => 'nullable|date',
            'agendas.*.tasks.*.end_date' => 'nullable|date',
            'agendas.*.tasks.*.user_id' => 'nullable|uuid|exists:users,id',
            'agendas.*.tasks.*.objective_id' => 'nullable|uuid|exists:objectives,id',
            'agendas.*.tasks.*.key_result_ids' => 'nullable|array',
            'agendas.*.tasks.*.external_email' => 'nullable|email',
        ]);

        DB::beginTransaction();
        try {
            $mom = Mom::create([
                'name' => $request->name,
                'company_id' => auth()->user()->company_id,
                'user_id' => auth()->id(),
                'meeting_id' => $request->meeting_id,
                'project_id' => $request->project_id,
                'mom_date' => $request->mom_date,
                'notes' => $request->notes,
            ]);

            foreach ($request->agendas as $agendaData) {
                $agenda = $mom->agendas()->create([
                    'title' => $agendaData['title'],
                    'discussion_notes' => $agendaData['discussion_notes'] ?? null,
                ]);

                foreach ($agendaData['tasks'] ?? [] as $taskData) {
                    $dailyTask = null;
                    if(isset($taskData['user_id']) && $taskData['user_id']) 
                    {
                        $requestDaily = new Request([
                            'user_id' => Auth::id(),
                            'assignment_user_id' => $taskData['user_id'],
                            'start_date' => $taskData['start_date'] ?? Carbon::now(),
                            'end_date' => $taskData['end_date'] ?? Carbon::now(),
                            'project_id' => $request->project_id,
                            'name' => $taskData['title'],
                            'description' => $agendaData['discussion_notes'],
                            'objective_id' => $taskData['objective_id'] ?? null,
                            'key_results' => $taskData['key_result_ids'] ?? [],
                        ]);

                        $dailyTask = $this->storeDailytask($requestDaily);
                    }

                    $agenda->tasks()->create([
                        'task_status_id' => TaskStatus::where('name', ParamSchema::TODO)->firstOrFail()->id,
                        'title' => $taskData['title'],
                        'description' => $agendaData['discussion_notes'],
                        'start_date' => $taskData['start_date'] ?? null,
                        'end_date' => $taskData['end_date'] ?? null,
                        'external_email' => $taskData['external_email'] ?? null,
                        'token' => isset($taskData['user_id']) && $taskData['user_id'] ? null : Str::uuid(),
                        'daily_task_id' => $dailyTask ? $dailyTask->id : null
                    ]);
                }
            }

            \App\Helpers\ChallengeProgressHelper::userCheckAndGiveReward(auth()->id());

            DB::commit();
            
            $mom->load(['agendas.tasks', 'meeting', 'project']);
            
            return response()->json([
                'success' => true,
                'message' => 'MoM berhasil disimpan!',
                'data' => $mom
            ], 201);
        } catch (\Exception $e) {
            // dd($e);
            Log::error($e);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan MoM',
                'error' => $e->getMessage()
            ], 422);
        }
    }


    public function storeCustomMoM(Request $request)
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'name' => 'required|string',
            'meeting_id' => 'nullable|exists:meetings,id',
            'project_id' => 'nullable|uuid|exists:projects,id',
            'mom_date' => 'required|date',
            'notes' => 'nullable|string',
            'agendas' => 'required|array',
            'agendas.*.title' => 'required|string',
            'agendas.*.discussion_notes' => 'nullable|string',
            'agendas.*.tasks' => 'nullable|array',
            'agendas.*.tasks.*.title' => 'required|string',
            'agendas.*.tasks.*.start_date' => 'nullable|date',
            'agendas.*.tasks.*.end_date' => 'nullable|date',
            'agendas.*.tasks.*.user_id' => 'nullable|uuid|exists:users,id',
            'agendas.*.tasks.*.objective_id' => 'nullable|uuid|exists:objectives,id',
            'agendas.*.tasks.*.key_result_ids' => 'nullable|array',
            'agendas.*.tasks.*.external_email' => 'nullable|email',
        ]);

        $targetUser = User::findOrFail($request->user_id);

        DB::beginTransaction();
        try {
            $mom = Mom::create([
                'name' => $request->name,
                'company_id' => $targetUser->company_id,
                'user_id' => $targetUser->id,
                'meeting_id' => $request->meeting_id,
                'project_id' => $request->project_id,
                'mom_date' => $request->mom_date,
                'notes' => $request->notes,
            ]);

            foreach ($request->agendas as $agendaData) {
                $agenda = $mom->agendas()->create([
                    'title' => $agendaData['title'],
                    'discussion_notes' => $agendaData['discussion_notes'] ?? null,
                ]);

                foreach ($agendaData['tasks'] ?? [] as $taskData) {
                    $dailyTask = null;
                    if(isset($taskData['user_id']) && $taskData['user_id']) 
                    {
                        $requestDaily = new Request([
                            'user_id' => $targetUserId,
                            'assignment_user_id' => $taskData['user_id'],
                            'start_date' => $taskData['start_date'] ?? Carbon::now(),
                            'end_date' => $taskData['end_date'] ?? Carbon::now(),
                            'project_id' => $request->project_id,
                            'name' => $taskData['title'],
                            'description' => $agendaData['discussion_notes'],
                            'objective_id' => $taskData['objective_id'] ?? null,
                            'key_results' => $taskData['key_result_ids'] ?? [],
                        ]);

                        $dailyTask = $this->storeDailytask($requestDaily);
                    }

                    $agenda->tasks()->create([
                        'task_status_id' => TaskStatus::where('name', ParamSchema::TODO)->firstOrFail()->id,
                        'title' => $taskData['title'],
                        'description' => $agendaData['discussion_notes'],
                        'start_date' => $taskData['start_date'] ?? null,
                        'end_date' => $taskData['end_date'] ?? null,
                        'external_email' => $taskData['external_email'] ?? null,
                        'token' => isset($taskData['user_id']) && $taskData['user_id'] ? null : Str::uuid(),
                        'daily_task_id' => $dailyTask ? $dailyTask->id : null
                    ]);
                }
            }

            \App\Helpers\ChallengeProgressHelper::userCheckAndGiveReward($targetUser->id);

            DB::commit();
            
            $mom->load(['agendas.tasks', 'meeting', 'project']);
            
            return response()->json([
                'success' => true,
                'message' => 'MoM berhasil disimpan!',
                'data' => $mom
            ], 201);
        } catch (\Exception $e) {
            // dd($e);
            Log::error($e);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan MoM',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $mom = Mom::byCompany(Auth::user()->company_id)
                ->where('id', $id)
                ->with(['agendas.tasks', 'meeting.participants', 'project', 'user'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Data MoM berhasil diambil',
                'data' => $mom
            ], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'MoM tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get data for editing MoM
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        try {
            $projects = Project::with(['meetings.participants:id,name'])
                ->byCompany(Auth::user()->company_id)
                ->get();
            $meetings = Meeting::byCompany(Auth::user()->company_id)->get();
            $users = User::byCompany(Auth::user()->company_id)->get();
            $objectives = Objective::byCompany(Auth::user()->company_id)->get();
            $mom = Mom::byCompany(Auth::user()->company_id)
                ->where('id', $id)
                ->with(['agendas.tasks', 'meeting.participants', 'project'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diambil',
                'data' => [
                    'mom' => $mom,
                    'projects' => $projects,
                    'meetings' => $meetings,
                    'users' => $users,
                    'objectives' => $objectives
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'MoM tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'project_id' => 'nullable|uuid|exists:projects,id',
            'meeting_id' => 'nullable|exists:meetings,id',
            'mom_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $mom = Mom::byCompany(Auth::user()->company_id)
                ->where('id', $id)
                ->firstOr(function () use ($id) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException("MoM with id $id not found");
                });

            $mom->update($request->all());
            
            $mom->load(['agendas.tasks', 'meeting', 'project']);

            return response()->json([
                'success' => true,
                'message' => 'MoM berhasil diupdate',
                'data' => $mom
            ], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate MoM',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $mom = Mom::byCompany(Auth::user()->company_id)
                ->where('id', $id)
                ->firstOrFail();

            // Delete mom tasks and their daily tasks
            $mom->tasks()->whereHas('dailyTask', function ($query) {
                $query->whereNotNull('daily_task_id');
            })->with('dailyTask')->get()->each(function ($task) {
                $task->dailyTask->delete();
            });
            
            $mom->agendas()->delete();
            $mom->delete();

            return response()->json([
                'success' => true,
                'message' => 'MoM berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus MoM',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    // ==================== MOM TASK ENDPOINTS ====================

    /**
     * Store a new task for MoM
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeTask(Request $request, $id)
    {
        $request->validate([
            'agenda_id' => 'required|exists:mom_agendas,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'user_id' => 'nullable|uuid|exists:users,id',
            'objective_id' => 'nullable|uuid|exists:objectives,id',
            'key_result_0' => 'nullable|array',
            'key_result_0.*' => 'nullable|uuid|exists:objective_key_results,id',
            'external_email' => 'nullable|email',
        ]);

        DB::beginTransaction();
        try {
            $mom = Mom::byCompany(Auth::user()->company_id)
                ->where('id', $id)
                ->firstOrFail();
            $agenda = MomAgenda::findOrFail($request->agenda_id);

            $dailyTask = null;
            if($request->user_id) 
            {
                $requestDaily = new Request([
                    'user_id' => Auth::id(),
                    'assignment_user_id' => $request->user_id,
                    'start_date' => $request->start_date ?? Carbon::now(),
                    'end_date' => $request->end_date ?? Carbon::now(),
                    'project_id' => $mom->project_id ?? null, 
                    'name' => $request->title,
                    'description' => $request->description,
                    'objective_id' => $request->objective_id ?? null,
                    'key_results' => $request->key_result_0 ?? [],
                ]);
    
                $dailyTask = $this->storeDailytask($requestDaily);
            }
            
            $task = $agenda->tasks()->create([
                'task_status_id' => TaskStatus::where('name', ParamSchema::TODO)->firstOrFail()->id,
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'external_email' => $request->external_email,
                'token' => $request->user_id ? null : Str::uuid(),
                'daily_task_id' => $dailyTask ? $dailyTask->id : null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task berhasil disimpan!',
                'data' => $task->load('dailyTask')
            ], 201);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan task',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update MoM task
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTask(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'user_id' => 'nullable|uuid|exists:users,id',
            'objective_id' => 'nullable|uuid|exists:objectives,id',
            'key_result_0' => 'nullable|array',
            'key_result_0.*' => 'nullable|uuid|exists:objective_key_results,id',
            'external_email' => 'nullable|email',
        ]);

        try {
            $task = MomTask::findOrFail($id);
            
            if(!$task->isAction())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Task ini sedang berjalan!'
                ], 422);
            }

            DB::beginTransaction();
            
            $dailyTaskId = $task->daily_task_id;
            $mom = $task->agenda->mom;
            
            if($request->user_id) 
            {
                $requestDaily = new Request([
                    'user_id' => Auth::id(),
                    'assignment_user_id' => $request->user_id,
                    'start_date' => $request->start_date ?? Carbon::now(),
                    'end_date' => $request->end_date ?? Carbon::now(),
                    'project_id' => $mom->project_id ?? null, 
                    'name' => $request->title,
                    'description' => $request->description,
                    'objective_id' => $request->objective_id ?? null,
                    'key_results' => $request->key_result_0 ?? [],
                    'daily_task_id' => $dailyTaskId
                ]);
    
                $dailyTask = $this->storeDailytask($requestDaily, $dailyTaskId != null ? 'update' : 'create');
                $dailyTaskId = $dailyTask->id;
            }

            if($task->dailyTask && !$request->user_id) 
            {
                $task->dailyTask->delete();
                $dailyTaskId = null;
            }
            
            $task->update([
                'task_status_id' => TaskStatus::where('name', ParamSchema::TODO)->firstOrFail()->id,
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'external_email' => $request->external_email,
                'token' => $request->user_id ? null : Str::uuid(),
                'daily_task_id' => $dailyTaskId,
            ]);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Task berhasil diupdate!',
                'data' => $task->load('dailyTask')
            ], 200);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate task',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete MoM task
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTask($id)
    {
        try {
            $momTask = MomTask::findOrFail($id);
            
            if(!$momTask->isAction())
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Task ini sedang berjalan!'
                ], 422);
            }
            
            if($momTask->dailyTask)
            {
                $momTask->dailyTask->delete();
            }
            
            $momTask->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Task berhasil dihapus!'
            ], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus task',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    // ==================== EXTERNAL TASK ENDPOINTS ====================

    /**
     * View external task by token
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewExternalTask($token)
    {
        try {
            $task = MomTask::where('token', $token)
                ->with(['agenda.mom', 'taskStatus'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Data task berhasil diambil',
                'data' => $task
            ], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Task tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Submit external task
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitExternalTask(Request $request, $token)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'method' => 'required|in:todo,doing',
                'description' => 'nullable|string',
                'attachment' => 'nullable|file|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task = MomTask::where('token', $token)->firstOrFail();
            $action = $this->externalAction($request, $task);
            
            return response()->json([
                'success' => $action['status'] === 'success',
                'message' => $action['message']
            ], $action['status'] === 'success' ? 200 : 422);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Approve external task
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveExternalTask(Request $request, $token)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'status' => 'required|in:decline,approve',
                'reject_reason' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $task = MomTask::where('token', $token)->firstOrFail();
            $task->update([
                'task_status_id' => $request->status == "approve" 
                    ? TaskStatus::where('name', ParamSchema::COMPLATE)->firstOrFail()->id 
                    : TaskStatus::where('name', ParamSchema::NOTCOMPLATE)->firstOrFail()->id,
                'reject_reason' => $request->status == "decline" ? $request->reject_reason : null
            ]);
            
            $message = $request->status == "approve" ? 'Task berhasil disetujui!' : 'Task berhasil ditolak!';
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $task->load('taskStatus')
            ], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    // ==================== AGENDA ENDPOINTS ====================

    /**
     * Store new agenda
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAgenda(Request $request, $id)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'title' => 'required|string',
                'discussion_notes' => 'nullable|string',
                'tasks' => 'required|array',
                'tasks.*.title' => 'required|string',
                'tasks.*.start_date' => 'required|date',
                'tasks.*.end_date' => 'required|date',
                'tasks.*.user_id' => 'nullable|uuid|exists:users,id',
                'tasks.*.objective_id' => 'nullable|uuid|exists:objectives,id',
                'tasks.*.key_result_ids' => 'nullable|array',
                'tasks.*.key_result_ids.*' => 'nullable|uuid|exists:objective_key_results,id',
                'tasks.*.external_email' => 'nullable|email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $mom = Mom::byCompany(Auth::user()->company_id)
                ->where('id', $id)
                ->firstOrFail();

            DB::beginTransaction();

            $agenda = MomAgenda::create([
                'mom_id' => $id,
                'title' => $request->title,
                'discussion_notes' => $request->discussion_notes,
            ]);
   
            foreach ($request->tasks ?? [] as $taskData) {
                $dailyTask = null;
                if(isset($taskData['user_id']) && $taskData['user_id']) 
                {
                    $requestDaily = new Request([
                        'user_id' => Auth::id(),
                        'assignment_user_id' => $taskData['user_id'],
                        'start_date' => $taskData['start_date'] ?? Carbon::now(),
                        'end_date' => $taskData['end_date'] ?? Carbon::now(),
                        'project_id' => $mom->project_id,
                        'name' => $taskData['title'],
                        'description' => $request->discussion_notes,
                        'objective_id' => $taskData['objective_id'] ?? null,
                        'key_results' => $taskData['key_result_ids'] ?? [],
                    ]);

                    $dailyTask = $this->storeDailytask($requestDaily);
                }

                $agenda->tasks()->create([
                    'task_status_id' => TaskStatus::where('name', ParamSchema::TODO)->firstOrFail()->id,
                    'title' => $taskData['title'],
                    'description' => $request->discussion_notes,
                    'start_date' => $taskData['start_date'] ?? null,
                    'end_date' => $taskData['end_date'] ?? null,
                    'external_email' => $taskData['external_email'] ?? null,
                    'token' => isset($taskData['user_id']) && $taskData['user_id'] ? null : Str::uuid(),
                    'daily_task_id' => $dailyTask ? $dailyTask->id : null
                ]);
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Agenda berhasil disimpan!',
                'data' => $agenda->load('tasks')
            ], 201);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Agenda gagal disimpan!',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update agenda
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAgenda(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'discussion_notes' => 'nullable|string',
        ]);

        try {
            $agenda = MomAgenda::findOrFail($id);
            $agenda->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Agenda berhasil diupdate!',
                'data' => $agenda
            ], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate agenda',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete agenda
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAgenda($id)
    {
        try {
            $momAgenda = MomAgenda::findOrFail($id);
            
            if (!$momAgenda->is_delete)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Agenda ini sedang berjalan!'
                ], 422);
            }
            
            // Delete all related daily tasks
            foreach ($momAgenda->tasks as $task) 
            {
                if ($task->daily_task_id) {
                    DailyTask::where('id', $task->daily_task_id)->delete();
                }
                $task->delete();
            }

            $momAgenda->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Agenda berhasil dihapus!'
            ], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus agenda',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    // ==================== PROTECTED METHODS ====================

    protected function storeDailytask(Request $request, $status = "create")
    {
        try {
            if($status == "create")
            {
                $statusObj = TaskStatus::where('name', ParamSchema::TODO)->firstOrFail();
                $dailyTaskType = DailyTaskType::where('name', ParamSchema::DAILY)->first();
                
                $dailyTask = new DailyTask();
                $dailyTask->user_id = $request->user_id;
                $dailyTask->task_status_id = $statusObj->id;
                $dailyTask->start_date = $request->start_date;
                $dailyTask->end_date = $request->end_date;
                $dailyTask->assignment_user_id = $request->assignment_user_id;
                $dailyTask->daily_task_type_id = $dailyTaskType->id;
                $dailyTask->project_id = $request->project_id ?? NULL;
                $dailyTask->name = $request->name;
                $dailyTask->description = $request->description;
                $dailyTask->point = 0;
                $dailyTask->objective_id = $request->objective_id;
                
                $dailyTask->save();

                $keyResults = $request->key_results ?? [];
                if (!empty($keyResults)) 
                {
                    $dailyTask->keyResults()->attach($keyResults);
                }
                $message = ' Membuat Tugas '.$dailyTask->name;

                $this->statusrecord($dailyTask, $statusObj);
            }
            else
            {
                $dailyTask = DailyTask::find($request->daily_task_id);
                $dailyTask->start_date = $request->start_date;
                $dailyTask->end_date = $request->end_date;
                $dailyTask->assignment_user_id = $request->assignment_user_id;
                $dailyTask->project_id = $request->project_id ?? NULL;
                $dailyTask->name = $request->name;
                $dailyTask->description = $request->description;
                $dailyTask->objective_id = $request->objective_id;
                $dailyTask->save();

                $keyResults = $request->key_results ?? [];
                if (!empty($keyResults)) 
                {
                    $dailyTask->keyResults()->sync($keyResults);
                }
                $message = ' Membuat Perubahan '.$dailyTask->name;
                $statusObj = $status;
            }

            $this->message($dailyTask->id, $status, $message);

            $directUrl = route('dailytask.show', ['dailytask' => $dailyTask->slug]);
    
            $inboxHelper = new InboxHelper();
            $inboxHelper->sent(
                $dailyTask->assignment_user_id, 
                Auth::user()->id, 
                Auth::user()->name.' Menugaskan ' . $dailyTask->name, 
                $directUrl
            );

            return $dailyTask;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
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

    protected function externalAction(Request $request, $dailyTask)
    {
        try 
        {
            if($request->method == ParamSchema::TODO)
            {
                $dailyTask->update([
                    'task_status_id' => TaskStatus::where('name', ParamSchema::DOING)->firstOrFail()->id
                ]);
                
                $message = 'Melakukan Pekerjaan';

                $company = Company::where('id', $dailyTask->agenda->mom->company_id)->first();
                $from = $company->user()->whereHas('role', function ($query) {
                    $query->where('name', RoleSchema::ROOT)
                        ->orWhere('name', RoleSchema::ADMIN)
                        ->orWhere('name', RoleSchema::DIRECTOR);
                })->first();

                $to = $dailyTask->agenda->mom->meeting && $dailyTask->agenda->mom->meeting->participantRelasion 
                    ? $dailyTask->agenda->mom->meeting->participantRelasion->pluck('id')->push($dailyTask->agenda->mom->user_id)->unique() 
                    : [$dailyTask->agenda->mom->user_id]; 
                    
                $url = route('external.task.view', $dailyTask->token);
                $messageInbox = "Seseorang Melakukan Pekerjaan ".$dailyTask->title." Pada Mom ".$dailyTask->agenda->mom->name;
            }

            if($request->method == ParamSchema::DOING)
            {         
                $attachment = null;
                if($request->hasFile('attachment'))
                {
                    $attachment = $request->file('attachment')->store('mom/report/task/external');
                }

                $dailyTask->update([
                    'task_status_id' => TaskStatus::where('name', ParamSchema::INREVIEW)->firstOrFail()->id,
                    'external_note' => $request->description,
                    'attachment' => $attachment,
                ]);
                
                $message = 'Laporan Berhasil Dikirimkan';

                $company = Company::where('id', $dailyTask->agenda->mom->company_id)->first();
                $from = $company->user()->whereHas('role', function ($query) {
                    $query->where('name', RoleSchema::ROOT)
                        ->orWhere('name', RoleSchema::ADMIN)
                        ->orWhere('name', RoleSchema::DIRECTOR);
                })->first();

                $to = $dailyTask->agenda->mom->meeting && $dailyTask->agenda->mom->meeting->participantRelasion 
                    ? $dailyTask->agenda->mom->meeting->participantRelasion->pluck('id')->push($dailyTask->agenda->mom->user_id)->unique() 
                    : [$dailyTask->agenda->mom->user_id]; 
                    
                $url = route('external.task.view', $dailyTask->token);
                $messageInbox = "Seseorang Membuat Laporan Pekerjaan ".$dailyTask->title." Pada Mom ".$dailyTask->agenda->mom->name;
            }

            $status = "success";
            $this->sentMessage($to, $from->id, $messageInbox, $url);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $message = $e->getMessage();
            $status = "error";
        }

        return [
            'status' => $status,
            'message' => $message
        ];
    }

    protected function sentMessage($to, $from, $message, $directUrl)
    {
        foreach ($to as $key => $toId) 
        {
            $inboxHelper = new InboxHelper();
            $inboxHelper->sent(
                $toId,
                $from, 
                $message,
                $directUrl,
                false,
                'high'
            );
        }
    }
}