<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\MomStoreRequest;
use App\Schemas\ParamSchema;

use App\Models\Mom;
use App\Models\User;
use App\Models\Project;
use App\Models\Meeting;
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
                        'title' => $taskData['title'],
                        'description' => $agendaData['discussion_notes'],
                        'start_date' => $taskData['start_date'] ?? null,
                        'end_date' => $taskData['end_date'] ?? null,
                        'external_email' => $taskData['external_email'] ?? null,
                        'token' => $taskData['external_email'] ? Str::uuid() : null,
                        'daily_task_id' => $dailyTask ? $dailyTask->id : null
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('mom.index')->with('success', 'MoM berhasil disimpan!');
        } catch (\Exception $e) {
            Log::error($e);
            // dd($e);
            DB::rollBack();
            return back()->withErrors('Terjadi kesalahan saat menyimpan MoM.');
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
        return view('mom.show', compact('mom'));
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
    protected function storeDailytask(Request $request)
    {
        try {
            
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


                $this->message($dailyTask->id,'create',' Membuat Tugas '.$dailyTask->name);
                $this->statusrecord($dailyTask, $status);

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
}
