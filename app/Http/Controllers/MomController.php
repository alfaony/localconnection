<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

class MomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('mom.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $projects = Project::with(['meetings.participants:id,name'])->byCompany(Auth::user()->company_id)->get();
        $objectives = Objective::byCompany(Auth::user()->company_id)->get();
        $users = User::byCompany(Auth::user()->company_id)->get();
        $meetings = Meeting::byCompany(Auth::user()->company_id)->get();

        return view('mom.createOrEdit', compact('projects', 'users', 'objectives' ,'meetings'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
                    if($taskData['user_id']) 
                    {
                        $request = new Request([
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

                        $dailyTask =  $this->storeDailytask($request);
                    }

                    $agenda->tasks()->create([
                        'task_status_id' => TaskStatus::where('name',ParamSchema::TODO)->firstOrFail()->id,
                        'title' => $taskData['title'],
                        'description' => $agendaData['discussion_notes'],
                        'start_date' => $taskData['start_date'] ?? null,
                        'end_date' => $taskData['end_date'] ?? null,
                        'external_email' => $taskData['external_email'] ?? null,
                        'token' => $taskData['user_id'] ?  null : Str::uuid(),
                        'daily_task_id' => $dailyTask ? $dailyTask->id : null
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'MoM berhasil disimpan!']);
        } catch (\Exception $e) {
            Log::error($e);
            // dd($e);
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan MoM.'], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mom  $mom
     * @return \Illuminate\Http\Response
     */
    public function show(Mom $mom)
    {
        $users = User::byCompany(Auth::user()->company_id)->get();
        $objectives = Objective::byCompany(Auth::user()->company_id)->get();

        $projects = Project::with(['meetings.participants:id,name'])->byCompany(Auth::user()->company_id)->get();
        $meetings = Meeting::byCompany(Auth::user()->company_id)->get();

        return view('mom.show', compact('mom','users', 'objectives', 'projects', 'meetings'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mom  $mom
     * @return \Illuminate\Http\Response
     */
    public function edit($mom)
    {
        $projects = Project::with(['meetings.participants:id,name'])->byCompany(Auth::user()->company_id)->get();
        $meetings = Meeting::byCompany(Auth::user()->company_id)->get();
        $users = User::byCompany(Auth::user()->company_id)->get();
        $mom = Mom::byCompany(Auth::user()->company_id)->where('id', $mom)->with(['agendas.tasks', 'meeting.participants', 'project'])->first();

        return view('mom.edit', compact('projects', 'users','mom','meetings'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mom  $mom
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mom $mom)
    {
        $request->validate([
            'name' => 'required|string',
            'project_id' => 'nullable|uuid|exists:projects,id',
            'meeting_id' => 'nullable|exists:meetings,id',
            'notes' => 'nullable|string',
        ]);

        $mom->update($request->all());
        return redirect()->route('mom.index')->with('update', true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mom  $mom
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mom $mom)
    {
        $mom->delete();
        return redirect()->route('mom.index')->with('delete', true);
    }

    // Mom Task
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
        ]);

        $mom = Mom::byCompany(Auth::user()->company_id)->where('id', $id)->first();
        $agenda = MomAgenda::find($request->agenda_id);

        // dd($agenda);
        // dd($request->all());

        DB::beginTransaction();
        try {
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
    
                $dailyTask =  $this->storeDailytask($requestDaily);
            }
            
            $agenda->tasks()->create([
                'task_status_id' => TaskStatus::where('name',ParamSchema::TODO)->firstOrFail()->id,
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'external_email' => $request->external_email,
                'token' => $request->user_id ?  null : Str::uuid(),
                'daily_task_id' => $dailyTask ? $dailyTask->id : null
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Task berhasil disimpan!');
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

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
        ]);


        $task = MomTask::find($id);
        
        if(!$task->isAction())
        {
            return redirect()->back()->with('error', 'Task ini sedang berjalan!');
        }

        DB::beginTransaction();
        try {
            $dailyTask = $task->daily_task_id;
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
                    'daily_task_id' => $dailyTask
                ]);
    
                $dailyTask =  $this->storeDailytask($requestDaily, $dailyTask != null ? 'update': 'create');
                $dailyTask = $dailyTask->id;
            }

            if($task->dailyTask && !$request->user_id) 
            {
                $task->dailyTask->delete();
                $dailyTask = null;
            }
            
            $task->update([
                'task_status_id' => TaskStatus::where('name',ParamSchema::TODO)->firstOrFail()->id,
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'external_email' => $request->external_email,
                'token' => $request->user_id ? null : Str::uuid(),
                'daily_task_id' => $dailyTask,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Task berhasil disimpan!');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            // dd($th);
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function deleteTask(MomTask $momTask)
    {
        if(!$momTask->isAction())
        {
            // dd("here");
            return redirect()->back()->with('error', 'Task ini sedang berjalan!');
        }

        // dd("here21");
        $momTask->delete();
        return redirect()->back()->with('success', 'Task berhasil dihapus!');
    }

    public function viewExternalTask($token)
    {
        $task = MomTask::where('token', $token)->firstOrFail();
        return view('mom.external_view', compact('task'));
    }

    public function submitExternalTask(Request $request, $token)
    {
        $this->validate($request, [
            'method' => 'required|in:todo,doing',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|max:2048',
        ]);

        $task = MomTask::where('token', $token)->firstOrFail();
        $action = $this->externalAction($request, $task);
        return redirect()->back()->with($action['status'], $action['message']);
    }

    public function approveExternalTask(Request $request, $token)
    {
        $this->validate($request, [
            'status' => 'required|in:decline,approve',
            'reject_reason' => 'nullable|string',
        ]);

        $task = MomTask::where('token', $token)->firstOrFail();
        $task->update([
            'task_status_id' => $request->status == "approve" ? TaskStatus::where('name',ParamSchema::COMPLATE)->firstOrFail()->id : TaskStatus::where('name',ParamSchema::NOTCOMPLATE)->firstOrFail()->id,
            'reject_reason' => $request->status == "decline" ? $request->reject_reason : null
        ]);

        return redirect()->back()->with('success', 'Task berhasil Melakukan Approvement!');
    }

    public function deleteAgenda(MomAgenda $momAgenda)
    {
        if ($momAgenda->tasks()->exists())
        {
            return redirect()->back()->with('error', 'Agenda ini sedang berjalan!');
        }

        $momAgenda->delete();

        return redirect()->back()->with('success', 'Agenda berhasil dihapus!');
    }

    public function storeAgenda(Request $request, $id)
    {
        // Implement the function logic here

        $this->validate($request, [
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
        ]);
        $mom = Mom::find($id);

        DB::beginTransaction();
        try {
            $agenda = MomAgenda::create([
               'mom_id' => $id,
               'title' => $request->title,
               'discussion_notes' => $request->discussion_notes,
           ]);
   
           foreach ($request->tasks ?? [] as $taskData) {
               $dailyTask = null;
                if($taskData['user_id']) 
                {
                    $request = new Request([
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

                    $dailyTask =  $this->storeDailytask($request);
                }

                $agenda->tasks()->create([
                    'task_status_id' => TaskStatus::where('name',ParamSchema::TODO)->firstOrFail()->id,
                    'title' => $taskData['title'],
                    'description' => $request->discussion_notes,
                    'start_date' => $taskData['start_date'] ?? null,
                    'end_date' => $taskData['end_date'] ?? null,
                    'external_email' => $taskData['external_email'] ?? null,
                    'token' => $taskData['user_id'] ?  null : Str::uuid(),
                    'daily_task_id' => $dailyTask ? $dailyTask->id : null
                ]);
           }

           DB::commit();
           return redirect()->back()->with('success', 'Agenda berhasil disimpan!');
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error($th);
            DB::rollBack();

            return redirect()->back()->with('error', 'Agenda gagal disimpan!');
        }
    }

    public function updateAgenda(Request $request, MomAgenda $id)
    {
        $request->validate([
            'title' => 'required|string',
            'discussion_notes' => 'nullable|string',
        ]);

        $id->update($request->all());

        return redirect()->back()->with('success', 'Agenda berhasil diupdate!');
    }


    // 
    protected function storeDailytask(Request $request, $status = "create")
    {
        try {
                if($status == "create")
                {

                    $status = TaskStatus::where('name',ParamSchema::TODO)->firstOrFail();
                    $dailyTaskType = DailyTaskType::where('name',ParamSchema::DAILY)->first();
                    
                    $dailyTask = new DailyTask();
                    $dailyTask->user_id = $request->user_id;
                    $dailyTask->task_status_id = $status->id;
                    $dailyTask->start_date = $request->start_date;
                    $dailyTask->end_date = $request->end_date;
                    $dailyTask->assignment_user_id = $request->assignment_user_id;
                    $dailyTask->daily_task_type_id = $dailyTaskType->id;
                    $dailyTask->project_id = $request->project_id ?? NULL;
                    $dailyTask->name = $request->name;
                    $dailyTask->description = $request->description;
                    $dailyTask->point = 0; // Assuming default value is 0
                    $dailyTask->objective_id = $request->objective_id;
                    
                    $dailyTask->save();
    
    
                    $keyResults = $request->key_results ?? [];
                    if (!empty($keyResults)) 
                    {
                        $dailyTask->keyResults()->attach($keyResults);
                    }
                     $message = ' Membuat Tugas '.$dailyTask->name;

                    $this->statusrecord($dailyTask, $status);
                }else
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
                }


                $this->message($dailyTask->id,$status,$message);

                $directUrl = route('dailytask.show', ['dailytask' => $dailyTask->slug]);
        
                // Call InboxHelper to send the notification
                $inboxHelper = new InboxHelper();
                $inboxHelper->sent(
                    $dailyTask->assignment_user_id, 
                    Auth::user()->id, 
                    Auth::user()->name.' Menugaskan ' . $dailyTask->name, 
                    $directUrl
                );

            return $dailyTask;
        } catch (\Throwable $th) {
            // dd($th);
            Log::error($th->getMessage());
            throw $th;
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
            if($request->method == \App\Schemas\ParamSchema::TODO)
            {
                $dailyTask->update([
                    'task_status_id' => TaskStatus::where('name',ParamSchema::DOING)->firstOrFail()->id
                ]);
                
                $message = 'Melakukan Pekerjaan';

                // Sent Message
                $company = Company::where('id', $dailyTask->agenda->mom->company_id)->first();
                $from = $company->user()->whereHas('role', function ($query) {
                    $query->where('name', RoleSchema::ROOT)->orWhere('name', RoleSchema::ADMIN)->orWhere('name', RoleSchema::DIRECTOR);
                })->first();

                $to = $dailyTask->agenda->mom->meeting->participantRelasion ? $dailyTask->agenda->mom->meeting->participantRelasion->pluck('id')->push($dailyTask->agenda->mom->user_id)->unique() : [$dailyTask->agenda->mom->user_id]; 
                $url = route('external.task.view', $dailyTask->token);
                $messageInbox = "Seseorang Melakukan Pekerjaan  ".$dailyTask->title." Pada Mom ".$dailyTask->agenda->mom->name;

            }

            if($request->method == \App\Schemas\ParamSchema::DOING)
            {         
    
                $attachment = null;
                if($request->hasFile('attachment'))
                {
                    $attachment = $request->file('attachment')->store('mom/report/task/external', 'public');
                }

                $dailyTask->update([
                    'task_status_id' => TaskStatus::where('name',ParamSchema::INREVIEW)->firstOrFail()->id,
                    'external_note' => $request->description,
                    'attachment' => $attachment,
                ]);
                
                $message = 'Laporan Berhasil Dikirimkan';

                // Sent Message
                $company = Company::where('id', $dailyTask->agenda->mom->company_id)->first();
                $from = $company->user()->whereHas('role', function ($query) {
                    $query->where('name', RoleSchema::ROOT)->orWhere('name', RoleSchema::ADMIN)->orWhere('name', RoleSchema::DIRECTOR);
                })->first();

                $to = $dailyTask->agenda->mom->meeting->participantRelasion ? $dailyTask->agenda->mom->meeting->participantRelasion->pluck('id')->push($dailyTask->agenda->mom->user_id)->unique() : [$dailyTask->agenda->mom->user_id]; 
                $url = route('external.task.view', $dailyTask->token);
                $messageInbox = "Seseorang Membuat Laporan Pekerjaan  ".$dailyTask->title." Pada Mom ".$dailyTask->agenda->mom->name;


            }

            $status = "success";
            $this->sentMessage($to, $from->id, $messageInbox, $url);

        } catch (\Throwable $th) {
            dd($th);
            Log::error($th->getMessage());
            $meetings = $th->getMessage();
            $status = "error";
        }

        return [
            'status' => $status,
            'message' => $message
        ];
    }

    protected function sentMessage($to, $from , $message, $directUrl)
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
