<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DailyTaskStoreApiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'objective' => 'required|uuid|exists:objectives,id',
            'key_result' => 'required|array',
            'key_result.*' => 'uuid|exists:objective_key_results,id',

            'project_id' => 'nullable|uuid|exists:daily_task_projects,id',
            'data_project_id' => 'nullable|uuid|exists:projects,id',

            'custom_field_values' => 'nullable|array',

            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',

            'assignment_user_id' => 'required|uuid|exists:users,id',
            'category_id' => 'required|string|exists:daily_task_categories,id',
            'type_id' => 'required|uuid|exists:daily_task_types,id',

            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'recurring' => 'nullable|array',
            'attachments.*' => 'nullable|file|max:10240',
            'days' => 'nullable|string|in:MO,TU,WE,TH,FR,SA,SU',
            'recurring_frequency' => 'nullable|string|in:DAILY,WEEKLY,MONTHLY,YEARLY',
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