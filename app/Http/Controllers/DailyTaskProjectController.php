<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Project;
use App\Models\Objective;
use App\Models\DailyTask;
use App\Models\TaskStatus;
use App\Models\DailyTaskCategory;
use App\Models\DailyTaskType;
use App\Models\DailyTaskProject;
use App\Models\DailyTaskProjectCustomField;
use App\Models\DailyTaskProjectCustomFieldValue;

use App\Schemas\ParamSchema;

class DailyTaskProjectController extends Controller
{
    public function index()
    {
        $projects = DailyTaskProject::byCompany(Auth::user()->company_id)->paginate(10);
        return view('daily_task_project.index', compact('projects'));
    }

    public function store(Request $request)
    {
        // Validate the request inputs
        $request->validate([
            'project_name' => 'required|string|max:255',
            'projects' => 'required|array',
            'projects.*' => 'required|exists:projects,id',
            'custom_field_name' => 'nullable|array',
            'custom_field_name.*' => 'required_with:custom_field_name|string|max:255',
            'custom_field_type' => 'nullable|array',
            'custom_field_type.*' => 'required_with:custom_field_name|string|in:single_select,multi_select',
            'custom_field_value' => 'nullable|array',
            'custom_field_value.*' => 'required_with:custom_field_name|array',
            'custom_field_value.*.*' => 'required_with:custom_field_name|string|max:255',
        ]);
        
        // Create the project

        $project = DailyTaskProject::create([
            'user_id' => auth()->id(),
            'name' => $request->project_name,
        ]);
        

        // Attach the project to the selected projects
        $project->projects()->attach($request->projects);

        // Check if there are custom fields
        if ($request->has('custom_field_name')) {
            foreach ($request->custom_field_name as $index => $fieldName) {
                // Create custom field
                $customField = DailyTaskProjectCustomField::create([
                    'daily_task_project_id' => $project->id,
                    'name' => $fieldName,
                    'type' => $request->custom_field_type[$index],
                ]);
    
                // Create options
                $ordering = 1;
                foreach ($request->custom_field_value[$index] as $value) {
                    DailyTaskProjectCustomFieldValue::create([
                        'custom_field_id' => $customField->id,
                        'value' => $value,
                        'ordering' => $ordering++
                    ]);
                }
            }
        }
    
        // Redirect back with success message
        return redirect()->route('daily_task_project.index')->with('success', 'Project created successfully.');
    }

    public function create()
    {
        $statusSelect = config('custom.statusSelect');
        $projects = Project::byCompany(Auth::user()->company_id)->whereDoesntHave('dailyTaskProjects')->get();
        
        return view('daily_task_project.create',compact('statusSelect', 'projects'));
    }

    public function edit($slug)
    {
        $statusSelect = config('custom.statusSelect');
        $dailyTaskProject = DailyTaskProject::with('projects')->byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $assignedProjectIds = DailyTaskProject::with('projects')->get()->pluck('projects.*.id')->flatten()->unique()->toArray();
        $projects = Project::byCompany(Auth::user()->company_id)->whereNotIn('id', $assignedProjectIds)->orWhereHas('dailyTaskProjects', function ($query) use ($dailyTaskProject) {
            $query->where('daily_task_project_id', $dailyTaskProject->id);
        })->get();
        
        return view('daily_task_project.edit',compact('dailyTaskProject', 'projects','statusSelect'));
    }

    public function show($slug)
    {
        $project = DailyTaskProject::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $statusSelect = config('custom.statusSelect');
        return view('daily_task_project.show',compact('statusSelect','project'));
    }

    public function kanban($slug)
    {
        $project = DailyTaskProject::where('slug', $slug)->firstOrFail();
        $statuses = TaskStatus::orderBy('sort')->get();
        $users = User::byCompany(Auth::user()->company_id)->get();

        $tasks = DailyTask::where('daily_task_project_id', $project->id)
                            ->with('taskStatus')
                            ->get()
                            ->sortBy('taskStatus.sort')
                            ->groupBy('taskStatus.name');
        
        $tasksByStatus = $statuses->mapWithKeys(function ($status) use ($tasks) {
            return [$status->name => $tasks->get($status->name, collect())];
        });
        
        return view('daily_task_project.kanban', compact('project', 'tasksByStatus', 'statuses', 'users'));
    }
    public function updateTaskFields(Request $request)
    {
        $task = DailyTask::find($request->taskId);
        $task->start_date = $request->startDate;
        $task->end_date = $request->endDate;
        $task->assign_user_id = $request->assignUserId;
        $task->task_status_id = TaskStatus::where('name', $request->newStatus)->first()->id;
        $task->save();

        return response()->json(['success' => true, 'task' => $task->load('assign')]);
    }

    public function updatestatus(Request $request)
    {
        $task = DailyTask::find($request->taskId);
        $task->task_status_id = TaskStatus::where('name', $request->newStatus)->first()->id;
        $task->save();

        return response()->json(['success' => true]);
    }

    public function showproject(Request $request, $slug)
    {
        // Ambil filter dari request
        $userFilter = $request->input('user');
        $statusFilter = $request->input('status');
        $search = $request->input('task_name');
        $customFieldvalue = $request->input('custom_field_value');
        
        $users = User::byCompany(Auth::user()->company_id)->get(); // Ambil semua user, bisa disesuaikan
        $taskStatuss = TaskStatus::bySort(true)->get(); // Ambil semua status tugas
        $project = DailyTaskProject::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $customFields = $project->customFields;

        $query = DailyTask::query();

        if ($userFilter) 
        {   
            if($userFilter != ParamSchema::ALL)
            {
                $query->whereHas('assign', function ($q) use ($userFilter) 
                {
                    $q->where('name', $userFilter);
                });
            }
        }

        if ($customFieldvalue) 
        {
                $query->whereHas('customFieldValues', function ($q) use ($customFieldvalue) 
                {
                    $q->where('custom_field_value_id', $customFieldvalue);
                });
        }

        // Filter berdasarkan status
        if ($statusFilter) 
        {
            $query->whereHas('taskStatus', function ($q) use ($statusFilter) {
                $q->where('name', $statusFilter);
            });
        }

        if ($search) 
        {
            $query->where('name', 'like', "%{$search}%"); // Add other fields as necessary
        }

        $tasks = $query->where('daily_task_project_id', $project->id)->with(['user', 'customFieldValues'])->orderBy('created_at','desc')->paginate(10);

        return view('daily_task_project.show_project', compact('tasks', 'customFields', 'project', 'users', 'taskStatuss'));
    }

    
    public function update(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'projects' => 'required|array',
            'projects.*' => 'exists:projects,id',
        ]);

        $project = DailyTaskProject::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

        $project->update([
            'name' => $request->name,
        ]);

        $project->projects()->sync($request->projects);

        return redirect()->route('daily_task_project.index')->with('success', 'Project updated successfully.');
    }

    public function destroy($slug)
    {
        $project = DailyTaskProject::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $project->delete();
        return redirect()->route('daily_task_project.index')->with('success', 'Project deleted successfully.');
    }

    // Daily Daily Project Custom Field
    public function customfieldstore(Request $request, $slug)
    {
        $request->validate([
            'custom_field_name' => 'required|string|max:255',
            'custom_field_type' => 'required|string|in:single_select,multi_select',
            'custom_field_value' => 'required|array',
            'custom_field_value.*' => 'required|string|max:255',
        ]);
        
        DB::beginTransaction();
        try {
            $project = DailyTaskProject::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

            $customField = DailyTaskProjectCustomField::create([
                'daily_task_project_id' => $project->id,
                'name' => $request->custom_field_name,
                'type' => $request->custom_field_type,
            ]);

            foreach ($request->custom_field_value as $index => $value) {
                DailyTaskProjectCustomFieldValue::create([
                    'custom_field_id' => $customField->id,
                    'value' => $value,
                    'ordering' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Custom field created successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            // dd($th);
            return redirect()->route('daily_task_project.show', $slug)->with('error', 'An error occurred: ' . $th->getMessage());
        }
    }

    public function customfieldupdate(Request $request, $id)
    {
        $request->validate([
            'custom_field_name' => 'required|string|max:255',
            'custom_field_value' => 'required|array',
            'custom_field_value.*' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $customField = DailyTaskProjectCustomField::findOrFail($id);
            $customField->update([
                'name' => $request->custom_field_name,
            ]);

            $existingValueIds = [];
            foreach ($request->custom_field_value as $index => $value) {
                if (isset($request->custom_field_value_id[$index])) {
                    $customFieldValue = DailyTaskProjectCustomFieldValue::findOrFail($request->custom_field_value_id[$index]);
                    $customFieldValue->update([
                        'value' => $value,
                        'ordering' => $index,
                    ]);
                    $existingValueIds[] = $customFieldValue->id;
                } else {
                    $newValue = DailyTaskProjectCustomFieldValue::create([
                        'custom_field_id' => $customField->id,
                        'value' => $value,
                        'ordering' => $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $existingValueIds[] = $newValue->id;
                }
            }

            $customField->values()->whereNotIn('id', $existingValueIds)->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Custom field updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());
            return redirect()->back();
        }
    }

    public function customfielddestroy($id)
    {
        DB::beginTransaction();
        try {
            $customField = DailyTaskProjectCustomField::findOrFail($id);
            $projectSlug = $customField->daily_task_project->slug;
            $customField->values()->delete();
            $customField->delete();

            DB::commit();
            return redirect()->route('daily_task_project.show', $projectSlug)->with('success', 'Custom field deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('daily_task_project.show', $projectSlug)->with('error', 'An error occurred: ' . $th->getMessage());
        }
    }

    public function getcustomfield(Request $request, $projectId) 
    {
        $project = DailyTaskProject::byCompany(Auth::user()->company_id)->where('id', $projectId)->firstOrFail();
        $customFields = $project->customFields;
        $selectedValues = [];
        $dailyTaskId = $request->dailyTaskId;
        $index = $request->index;
        $dataProyek = NULL;
    
        if ($dailyTaskId) {
            $dailyTask = DailyTask::with('customFieldValues')->find($dailyTaskId);
            $dataProyek = $dailyTask->project_id;
            foreach ($dailyTask->customFieldValues as $value) {
                $selectedValues[$value->custom_field_id][] = $value->custom_field_value_id;
            }
        }
    
        return view('partials.custom-fields', compact('customFields', 'selectedValues','index','project','dataProyek'));
    }

    public function createdailytask($slug)
    {
        $categories = DailyTaskCategory::byCompany(Auth::user()->company_id)->get();
        $childTasks = DailyTask::byCompany(Auth::user()->company_id)->get();
        $types = DailyTaskType::get();
        $users = User::byCompany(Auth::user()->company_id)->get(); // Ambil semua user, bisa disesuaikan
        $taskStatuss = TaskStatus::bySort()->get(); // Ambil semua status tugas
        $project = DailyTaskProject::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $customFields = $project->customFields;

        $user = Auth::user(); // Get the current authenticated user
        $divisionIds = $user->divisions->pluck('id');
        $projects = DailyTaskProject::byCompany(Auth::user()->company_id)->get();

        if ($divisionIds->isEmpty()) {
            // Handle the case where the user does not belong to any divisions
            // You can return an empty collection or a message, or redirect
            return redirect()->route('daily_task_project.showproject',$slug)->with('error', 'Anda tidak tergabung dalam divisi manapun. Hubungi admin atau manager Anda.');
        } else {
            // Proceed with fetching objectives related to the user's divisions
            $objectives = Objective::whereHas('division', function ($query) use ($divisionIds) {
                $query->whereIn('id', $divisionIds);
            })->get();
        }

        return view('daily_task_project.create_daily_task', compact('project', 'users', 'taskStatuss', 'objectives', 'projects', 'categories', 'childTasks', 'types', 'customFields'));
    }

}
