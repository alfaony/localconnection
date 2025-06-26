<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\MomStoreRequest;

use App\Models\Mom;
use App\Models\User;
use App\Models\Project;
use App\Models\Meeting;

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
        $users = User::byCompany(Auth::user()->company_id)->get();

        return view('mom.createOrEdit', compact('projects', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MomStoreRequest $request)
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
                    $agenda->tasks()->create([
                        'title' => $taskData['title'],
                        'description' => null,
                        'start_date' => null,
                        'end_date' => $taskData['end_date'] ?? null,
                        'external_email' => $taskData['external_email'] ?? null,
                        'token' => $taskData['external_email'] ? Str::uuid() : null,
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
    

    protected function storeDailyTask()
    {
        $dailyTask = new DailyTask();
        $dailyTask->user_id = Auth::user()->id;
        $dailyTask->task_status_id = $status->id;
        $dailyTask->start_date = $startDates[$i] ?? NULL; 
        $dailyTask->end_date = $endDates[$i] ?? NULL;
        $dailyTask->assignment_user_id = $assignmentUserIds[$i] ?? NULL;
        $dailyTask->daily_task_category_id = $categoryIds[$i];
        $dailyTask->daily_task_type_id = $typeIds[$i];
        $dailyTask->project_id = $dataProjects[$i] ?? NULL;
        $dailyTask->daily_task_project_id = $projectIds[$i] ?? NULL;
        $dailyTask->name = $names[$i];
        $dailyTask->description = $descriptions[$i] ?? null;
        $dailyTask->point = 0; // Assuming default value is 0
        $dailyTask->objective_id = $objectives[$i] ?? NULL;

    }
}
