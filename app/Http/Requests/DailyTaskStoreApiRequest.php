<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\DailyTaskType;
use App\Schemas\ParamSchema;

class DailyTaskStoreApiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $objectiveId   = $this->input('objective');        // uuid objectives.id
        $projectId     = $this->input('daily_task_project_id');       // uuid/str daily_task_projects.id
        // $companyId     = optional($this->user())->company_id; // kalau mau batasi per company

        return [
             'objective' => [
                'required',
                'uuid',
                Rule::exists('objectives', 'id')
                    // ->when($companyId, fn($q) => $q->where('company_id', $companyId)),
            ],

            // key_result harus array of uuid yang semuanya punya objective_id = objective
            'key_result'   => ['required','array','min:1'],
            'key_result.*' => [
                'uuid',
                Rule::exists('objective_key_results', 'id')
                    // ->where(fn($q) => $q->where('objective_id', $objectiveId))
            ],

            // daily_task_project yang dipilih (opsional contoh pakai exists + scope company)
            'daily_task_project_id' => [
                'nullable',
                'uuid',
                Rule::exists('daily_task_projects', 'id')
                    // ->when($companyId, fn($q) => $q->where('company_id', $companyId)),
            ],

            // data_project_id harus benar2 milik project_id di atas
            'project_id' => [
                'nullable',
                'uuid',
                Rule::exists('projects', 'id')
                    ->where(fn($q) => $q->where('daily_task_project_id', $projectId))
            ],
            
            'recurring' => [
            'array',
            'min:1',
                Rule::requiredIf(function () {
                    $type = DailyTaskType::find($this->input('type_id'));
                    return $type && $type->name === ParamSchema::RECURRING;
                }),
            ],

            // Karena kamu pakai 1 object (bukan list) di controller, bisa validasi langsung object-nya:
            'recurring.frequency'   => 'required_with:recurring|in:DAILY,WEEKLY,MONTHLY,YEARLY',
            'recurring.until'       => 'nullable|date',

            // Opsional sesuai frequency
            'recurring.by_day'        => 'nullable|array',
            'recurring.by_day.*'      => 'string|in:MO,TU,WE,TH,FR,SA,SU',

            'recurring.by_month_day'   => 'nullable|array',
            'recurring.by_month_day.*' => 'integer|min:1|max:31',

            'recurring.by_month'       => 'nullable|array',
            'recurring.by_month.*'     => 'integer|min:1|max:12',

            'objective' => 'required|uuid|exists:objectives,id',
            'key_result' => 'required|array',
            'key_result.*' => 'uuid|exists:objective_key_results,id',

            'daily_task_project_id' => 'nullable|uuid|exists:daily_task_projects,id',
            'project_id' => 'nullable|uuid|exists:projects,id',

            'custom_field_values' => 'nullable|array',

            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'required|uuid|exists:users,id',
            'assignment_user_id' => 'required|uuid|exists:users,id',
            'category_id' => 'required|string|exists:daily_task_categories,id',
            'type_id' => 'required|uuid|exists:daily_task_types,id',

            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            // 'recurring' => 'nullable|array',
            // 'attachments.*' => 'nullable|file|max:10240',
            // 'days' => 'nullable|string|in:MO,TU,WE,TH,FR,SA,SU',
            // 'recurring_frequency' => 'nullable|string|in:DAILY,WEEKLY,MONTHLY,YEARLY',
        ];
    }

    public function messages()
    {
        return [
            'objective.required' => 'Objective harus diisi.',
            'objective.uuid' => 'Objective harus berupa UUID yang valid.',

            'key_result.required' => 'Key Result harus diisi.',
            'key_result.array' => 'Key Result harus berupa array.',
            'key_result.*.uuid' => 'Setiap Key Result harus UUID valid.',

            'project_id.uuid' => 'Project ID harus UUID yang valid.',
            'data_project_id.uuid' => 'Data Project ID harus UUID yang valid.',

            'start_date.required' => 'Tanggal mulai harus diisi.',
            'start_date.date' => 'Tanggal mulai harus berupa tanggal.',
            'end_date.required' => 'Tanggal selesai harus diisi.',
            'end_date.date' => 'Tanggal selesai harus berupa tanggal.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',

            'assignment_user_id.required' => 'User penugasan harus diisi.',
            'assignment_user_id.uuid' => 'User penugasan harus UUID yang valid.',

            'category_id.required' => 'Kategori harus diisi.',
            'category_id.string' => 'Kategori harus berupa string.',

            'type_id.required' => 'Tipe tugas harus diisi.',
            'type_id.uuid' => 'Tipe tugas harus UUID yang valid.',

            'name.required' => 'Nama tugas harus diisi.',
            'name.string' => 'Nama tugas harus berupa string.',
            'name.max' => 'Nama tugas tidak boleh lebih dari 255 karakter.',

            'description.string' => 'Deskripsi harus berupa string.',
            'recurring.array' => 'Recurring harus berupa array.',

            'attachments*.*.file' => 'Attachments harus berupa file.',
            'attachments*.*.max' => 'Attachments tidak boleh lebih dari 10 MB.'
        ];
    }
}