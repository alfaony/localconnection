<?php

namespace App\Http\Controllers\API;

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

use Carbon\Carbon;

class DailyTaskProjectController extends BaseController
{
    public function getcustomfield(Request $request, $projectId)
    {
        $request->validate([
            'dailyTaskId' => 'nullable|uuid|exists:daily_tasks,id',
            'index'       => 'nullable|integer|min:0',
        ]);

        $project = DailyTaskProject::byCompany(Auth::user()->company_id)
            ->with([
                // relasi ke data proyek (list yang di <select> Data Proyek)
                // 'projects:id,title,daily_task_project_id',
                // relasi ke custom fields + values
                'customFields.values' => function ($q) {
                    $q->orderBy('ordering');
                },
            ])
            ->findOrFail($projectId);

        $selectedValues = [];
        $selectedDataProjectId = null;

        if ($request->filled('dailyTaskId')) {
            $dailyTask = DailyTask::with('customFieldValues')
                ->byCompany(Auth::user()->company_id)
                ->findOrFail($request->dailyTaskId);

            // “Data Proyek” yang sudah tersimpan di task
            $selectedDataProjectId = $dailyTask->project_id;

            // map custom field yang sudah dipilih
            foreach ($dailyTask->customFieldValues as $val) {
                $selectedValues[$val->custom_field_id][] = $val->custom_field_value_id;
            }
        }

        // Susun payload “Data Proyek” (opsi select)
        $dataProjects = $project->projects->map(function ($p) use ($selectedDataProjectId) {
            return [
                'id'       => (string) $p->id,
                'title'    => $p->title,
                'selected' => $selectedDataProjectId && $selectedDataProjectId == $p->id,
            ];
        })->values();

        // Susun payload custom fields
        $customFields = $project->customFields->map(function ($field) use ($selectedValues) {
            $isMulti = $field->type !== 'single_select';

            return [
                'id'       => (string) $field->id,
                'name'     => $field->name,
                'type'     => $field->type,           // 'single_select' / 'multi_select' / dll
                'multiple' => $isMulti,
                'options'  => $field->values->map(function ($opt) use ($field, $selectedValues) {
                    $selectedForField = $selectedValues[$field->id] ?? [];
                    return [
                        'id'        => (string) $opt->id,
                        'value'     => $opt->value,
                        'ordering'  => $opt->ordering,
                        'selected'  => in_array($opt->id, $selectedForField),
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'index'          => (int) ($request->input('index', 0)),
                'project'        => [
                    'id'   => (string) $project->id,
                    'name' => $project->name ?? $project->title ?? null,
                ],
                'data_projects'  => $dataProjects,     // pengganti <select name="data_project_id[]">
                'custom_fields'  => $customFields,     // pengganti loop @foreach($customFields as $field)
                'is_empty'       => [
                    'data_projects' => $dataProjects->isEmpty(),
                    'custom_fields' => $customFields->isEmpty(),
                ],
            ],
        ]);
    }
}
