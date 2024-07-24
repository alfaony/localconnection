<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Schemas\RoleSchema;

use App\Http\Requests\ObjectiveStoreRequest;

use App\Models\Objective;
use App\Models\Division;
use App\Models\DailyTask;
use App\Models\User;
use App\Models\ObjectiveKeyResult;
use App\Models\TaskStatus;
use App\Models\Mission;


class ObjectiveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userRoleName = Auth::user()->role->name;
        $query = Objective::query();
        
        if( ($userRoleName != RoleSchema::ROOT )  && ( $userRoleName != RoleSchema::ADMIN ) && ( $userRoleName != RoleSchema::DIRECTOR ))
        {
            $query->byUserDivisions(Auth::user()->id);
        }

        $objectives = $query->byCompany(Auth::user()->company_id)->paginate(10);
        $divisions = Division::byCompany(Auth::user()->company_id)->get();
        return view('objective.index', compact('objectives', 'divisions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = User::with('divisions')->find(Auth::user()->id);
        $divisions = $user->divisions()->get();
        $missions = Mission::where('company_id',Auth::user()->company_id)->get();

        return view('objective.create', compact('divisions','missions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ObjectiveStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            // dd($request->all());
            foreach ($request->objective_name as $index => $fieldName) 
            {
                // Create custom field
                $objective = Objective::create([
                    'division_id' => $request->division_id[$index],
                    'mission_id' => $request->mission_id[$index],
                    'user_id' => Auth::user()->id,
                    'start_date' => $request->start_date_objective[$index] ?? null,
                    'end_date' => $request->end_date_objective[$index] ?? null,
                    'name' => $fieldName,
                ]);
    
                // Create Key Result
                foreach ($request->key_result[$index] as $indexs => $value) {
                    ObjectiveKeyResult::create([
                        'objective_id' => $objective->id,
                        'result' => $value,
                        'start_date' => $request->start_date[$index][$indexs] ?? null,
                        'end_date' => $request->end_date[$index][$indexs] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('objective.index')->with('store',true);
            //code...
        } catch (\Throwable $th) {
            //throw $th;

            DB::rollback();
            Log::error($th->getMessage());
            return redirect()->route('objective.index')->with('store',false);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Objective  $objective
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $objective = Objective::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        return view('objective.show', compact('objective'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Objective  $objective
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $objective = Objective::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $user = User::with('divisions')->find(Auth::user()->id);
        $divisions = $user->divisions()->get();
        $missions = Mission::where('company_id',Auth::user()->company_id)->get();

        return view('objective.edit', compact('divisions','objective', 'missions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Objective  $objective
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'mission_id' => 'required|exists:missions,id',
            'start_date_objective' => 'nullable|date',
            'end_date_objective' => 'nullable|date|after_or_equal:start_date_objective',
            'objective_name' => 'required|string|max:255',
            'key_result' => 'nullable|array',
            'key_result.*' => 'required|string|max:255',
            'start_date' => 'nullable|array',
            'start_date.*' => 'nullable|date',
            'end_date' => 'nullable|array',
            'end_date.*' => 'nullable|date|after_or_equal:start_date.*',
        ]);
        
        DB::beginTransaction();
        try {
            $objective = Objective::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            $objective->update([
                'division_id' => $request->division_id,
                'mission_id' => $request->mission_id,
                'start_date' => $request->start_date_objective ?? null,
                'end_date' => $request->end_date_objective ?? NULL,
                'name' => $request->objective_name,
            ]);
                        // Create Key Result 
            $existingValueIds = [];
            foreach ($request->key_result as $index => $value) {
                if (isset($request->key_result_id[$index])) {
                    $keyResult = ObjectiveKeyResult::findOrFail($request->key_result_id[$index]);
                    $keyResult->update([
                        'result' => $value,
                        'start_date' => $request->start_date[$index] ?? null,
                        'end_date' => $request->end_date[$index] ?? null,
                    ]);
                    $existingValueIds[] = $keyResult->id;
                } else {
                    $newValue =
                    ObjectiveKeyResult::create([
                        'objective_id' => $objective->id,
                        'result' => $value,
                        'start_date' => $request->start_date[$index] ?? null,
                        'end_date' => $request->end_date[$index] ?? null,
                    ]);

                    $existingValueIds[] = $newValue->id;
                }
            }
    
            $objective->keyResults()->whereNotIn('id', $existingValueIds)->delete();
                
            DB::commit();
            return redirect()->route('objective.index')->with('update',true);
        } catch (\Throwable $th) {
            //throw $th;

            DB::rollback();
            Log::error($th->getMessage());
            // dd($th);
            return redirect()->route('objective.index')->with('update',false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Objective  $objective
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $objective = Objective::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $objective->delete();

        return redirect()->route('objective.index')->with('delete',true);
    }


    // Custom

    public function getresult(Request $request, Objective $objective)
    {
        $keyResult = $objective->keyResults;
        $index = $request->index ?? 0;
        $dailyTaskId = $request->dailyTaskId;
        $selectedKeyResults = [];
        $hasHead = false;

        if ($dailyTaskId) {
            $dailyTask = DailyTask::with('keyResults')->find($dailyTaskId);
            if ($dailyTask) {
                $selectedKeyResults = $dailyTask->keyResults->pluck('id')->toArray();
                $hasHead = $dailyTask->head ? true : false;
            }
        }

        return view('partials.keyresult-fields', compact('keyResult', 'selectedKeyResults', 'index' ,'hasHead'));
    }

    public function showtask(Request $request, $slug)
    {
        $keyResult = ObjectiveKeyResult::where('slug', $slug)->firstOrFail();

        // Start the query to get tasks related to the Key Result
        $tasksQuery = $keyResult->dailyTasks();

        // Apply search filtering for task name
        if ($request->filled('task_name')) 
        {
            $tasksQuery->where('name', 'like', '%' . $request->task_name . '%');
        }

        // Apply filtering for assignee
        if ($request->filled('user') && $request->user  != 'all') {
            $tasksQuery->whereHas('assign', function($query) use ($request) {
                $query->where('name', 'like', '%' . $request->user . '%');
            });
        }

        // Apply filtering for status
        if ($request->filled('status') && $request->status) {
            $tasksQuery->whereHas('taskStatus', function($query) use ($request) {
                $query->where('name', $request->status);
            });
        }

        // Pagination of the tasks
        $tasks = $tasksQuery->paginate(10);

        $users = User::byCompany(Auth::user()->company_id)->get();
        $taskStatuss = TaskStatus::bySort()->get();

        return view('objective.show_task', compact('keyResult', 'tasks', 'users', 'taskStatuss'));
    }


}
