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

        $objectives = $query->with('divisions')->byCompany(Auth::user()->company_id)->paginate(10);
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
        $divisions = Division::byCompany(Auth::user()->company_id)->get();
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
            foreach ($request->objective_name as $index => $fieldName)
            {
                $divisionIds = $request->division_id[$index] ?? [];
                // Create custom field
                $objective = Objective::create([
                    'division_id' => $divisionIds[0] ?? null,
                    'mission_id' => $request->mission_id[$index],
                    'user_id' => Auth::user()->id,
                    'start_date' => $request->start_date_objective[$index] ?? null,
                    'end_date' => $request->end_date_objective[$index] ?? null,
                    'name' => $fieldName,
                ]);

                $objective->divisions()->sync($divisionIds);
    
                // Key Result dibuat di halaman Show Objective
            }

            DB::commit();
            return redirect()->route('objective.index')->with('store',true);
            //code...
        } catch (\Throwable $th) {
            //throw $th;

            // dd($th);
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
        $objective = Objective::with(['divisions', 'keyResults.division'])
            ->byCompany(Auth::user()->company_id)
            ->where('slug', $slug)
            ->firstOrFail();

        $keyResultsByDivision = $objective->keyResults->groupBy('division_id');

        return view('objective.show', compact('objective', 'keyResultsByDivision'));
    }

    public function storeKeyResult(Request $request, Objective $objective)
    {
        $request->validate([
            'division_id'              => 'nullable|uuid|exists:divisions,id',
            'key_results'              => 'required|array|min:1',
            'key_results.*.result'     => 'required|string|max:190',
            'key_results.*.start_date' => 'nullable|date',
            'key_results.*.end_date'   => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $created = [];
            foreach ($request->key_results as $kr) {
                $keyResult = ObjectiveKeyResult::create([
                    'objective_id' => $objective->id,
                    'division_id'  => $request->division_id ?: null,
                    'result'       => $kr['result'],
                    'start_date'   => $kr['start_date'] ?? null,
                    'end_date'     => $kr['end_date']   ?? null,
                ]);
                $created[] = [
                    'id'         => $keyResult->id,
                    'result'     => $keyResult->result,
                    'date_show'  => $keyResult->dateShow,
                    'task_count' => 0,
                    'slug'       => $keyResult->slug,
                ];
            }
            DB::commit();
            return response()->json(['success' => true, 'data' => $created]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th);
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function updateKeyResult(Request $request, ObjectiveKeyResult $keyResult)
    {
        $request->validate([
            'result'      => 'required|string|max:190',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date',
            'division_id' => 'nullable|uuid|exists:divisions,id',
        ]);

        $data = [
            'result'     => $request->result,
            'start_date' => $request->start_date ?: null,
            'end_date'   => $request->end_date   ?: null,
        ];

        // Hanya update division_id jika dikirim (untuk migrasi legacy KR)
        if ($request->has('division_id')) {
            $data['division_id'] = $request->division_id;
        }

        $keyResult->update($data);
        $keyResult->load('division');

        return response()->json(['success' => true, 'data' => [
            'id'            => $keyResult->id,
            'result'        => $keyResult->result,
            'date_show'     => $keyResult->dateShow,
            'division_id'   => $keyResult->division_id,
            'division_name' => optional($keyResult->division)->name,
        ]]);
    }

    public function destroyKeyResult(ObjectiveKeyResult $keyResult)
    {
        $keyResult->delete();
        return response()->json(['success' => true]);
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
        $divisions = Division::byCompany(Auth::user()->company_id)->get();
        $missions = Mission::byCompany(Auth::user()->company_id)->get();

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
            'division_ids'         => 'required|array',
            'division_ids.*'       => 'required|uuid|exists:divisions,id',
            'mission_id'           => 'required|exists:missions,id',
            'start_date_objective' => 'nullable|date',
            'end_date_objective'   => 'nullable|date|after_or_equal:start_date_objective',
            'objective_name'       => 'required|string|max:190',
        ]);

        DB::beginTransaction();
        try {
            $objective = Objective::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            $divisionIds = $request->division_ids ?? [];
            $objective->update([
                'division_id' => $divisionIds[0] ?? null,
                'mission_id'  => $request->mission_id,
                'start_date'  => $request->start_date_objective ?? null,
                'end_date'    => $request->end_date_objective   ?? null,
                'name'        => $request->objective_name,
            ]);

            $objective->divisions()->sync($divisionIds);

            DB::commit();
            return redirect()->route('objective.index')->with('update', true);
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
        $divisionId = $request->division_id;
        $index      = $request->index ?? 0;
        $dailyTaskId = $request->dailyTaskId;
        $selectedKeyResults = [];
        $hasHead = false;

        // Filter KR berdasarkan division_id yang dipilih;
        // legacy KR (null division) selalu ikut tampil agar tidak breaking
        $query = $objective->keyResults();
        if ($divisionId) {
            $query->where('division_id', $divisionId);
        }
        $keyResult = $query->get();

        if ($dailyTaskId) {
            $dailyTask = DailyTask::with('keyResults')->find($dailyTaskId);
            if ($dailyTask) {
                $selectedKeyResults = $dailyTask->keyResults->pluck('id')->toArray();
                $hasHead = $dailyTask->head ? true : false;
            }
        }

        return view('partials.keyresult-fields', compact('keyResult', 'selectedKeyResults', 'index', 'hasHead'));
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
