<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DailyTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $objectiveId   = $this->input('objective');        // uuid objectives.id
        $projectId     = $this->input('project_id');       // uuid/str daily_task_projects.id
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
            'project_id' => [
                'nullable',
                'uuid',
                Rule::exists('daily_task_projects', 'id')
                    // ->when($companyId, fn($q) => $q->where('company_id', $companyId)),
            ],

            // data_project_id harus benar2 milik project_id di atas
            'data_project_id' => [
                'nullable',
                'uuid',
                Rule::exists('projects', 'id')
                    ->where(fn($q) => $q->where('daily_task_project_id', $projectId))
            ],

            'user_id' => 'required|uuid|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'assignment_user_id' => 'nullable|uuid|exists:users,id',
            'child_daily_task_id' => 'nullable|uuid',
            'category_id' => 'required|string',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'poin' => 'nullable|integer'
        ];
    }

    public function messages()
    {
        return [
            'start_date.required' => 'Tanggal mulai harus diisi.',
            'start_date.date' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'end_date.required' => 'Tanggal selesai harus diisi.',
            'end_date.date' => 'Tanggal selesai harus berupa tanggal yang valid.',
            'user_id.required' => 'ID pengguna harus diisi.',
            'user_id.uuid' => 'ID pengguna harus berupa UUID yang valid.',
            'assignment_user_id.uuid' => 'ID pengguna penugasan harus berupa UUID yang valid.',
            'assign_user_id.exists' => 'ID pengguna penugasan tidak valid.',
            'child_daily_task_id.uuid' => 'ID tugas harian anak harus berupa UUID yang valid.',
            'category_id.required' => 'ID kategori harus diisi.',
            'category_id.uuid' => 'ID kategori harus berupa UUID yang valid.',
            'name.required' => 'Nama harus diisi.',
            'name.string' => 'Nama harus berupa string.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'description.string' => 'Deskripsi harus berupa string.',
            'poin.integer' => 'Poin harus berupa angka integer.'
        ];
    }
}
