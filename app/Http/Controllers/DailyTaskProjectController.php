<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\DailyTask;
use App\Models\TaskStatus;
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
        return view('daily_task_project.create',compact('statusSelect'));
    }

    public function show($slug)
    {
        $project = DailyTaskProject::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $statusSelect = config('custom.statusSelect');
        return view('daily_task_project.show',compact('statusSelect','project'));
    }

    public function showproject(Request $request, $slug)
    {
        // Ambil filter dari request
        $userFilter = $request->input('user');
        $statusFilter = $request->input('status');
        $search = $request->input('task_name');
        
        $users = User::byCompany(Auth::user()->company_id)->get(); // Ambil semua user, bisa disesuaikan
        $taskStatuss = TaskStatus::all(); // Ambil semua status tugas
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

        $tasks = $query->where('daily_task_project_id', $project->id)->with(['user', 'customFieldValues'])->paginate(10);

        return view('daily_task_project.show_project', compact('tasks', 'customFields', 'project', 'users', 'taskStatuss'));
    }

    
    public function update(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project = DailyTaskProject::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

        $project->update([
            'name' => $request->name,
        ]);

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
            'custom_field_type' => 'required|string|in:single_select,multi_select',
            'custom_field_value' => 'required|array',
            'custom_field_value.*' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $customField = DailyTaskProjectCustomField::findOrFail($id);
            $customField->update([
                'name' => $request->custom_field_name,
                'type' => $request->custom_field_type,
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
            // dd($th);
            // return redirect()->route('daily_task_project.show', $customField->daily_task_project->slug)->with('error', 'An error occurred: ' . $th->getMessage());
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
    
        if ($dailyTaskId) {
            $dailyTask = DailyTask::with('customFieldValues')->find($dailyTaskId);
            foreach ($dailyTask->customFieldValues as $value) {
                $selectedValues[$value->custom_field_id][] = $value->custom_field_value_id;
            }
        }
    
        return view('partials.custom-fields', compact('customFields', 'selectedValues','index'));
    }

}
