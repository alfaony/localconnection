<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DailyTaskStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_date' => 'nullable|array',
            'start_date.*' => 'nullable|date',
            'end_date' => 'nullable|array',
            'end_date.*' => 'nullable|date|after_or_equal:start_date.*',
            'assignment_user_id' => 'nullable|array',
            'assignment_user_id.*' => 'nullable|uuid|exists:users,id',
            'category_id' => 'required|array',
            'category_id.*' => 'required|string',
            'type_id' => 'required|array',
            'type_id.*' => 'required|uuid',
            'project_id' => 'nullable|array',
            'project_id.*' => 'nullable|uuid|exists:daily_task_projects,id',
            'data_project_id' => 'nullable|array',
            'data_project_id.*' => 'nullable|uuid|exists:projects,id',
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string',

            'recurring' => 'nullable|array',
            'recurring.*.frequency' => 'required|string|in:DAILY,WEEKLY,MONTHLY,YEARLY',
            'recurring.*.until' => 'required|date',

            // WEEKLY: require by_day with values MO..SU
            'recurring.*.by_day' => 'required_if:recurring.*.frequency,WEEKLY|array|min:1',
            'recurring.*.by_day.*' => 'required_with:recurring.*.by_day|string|in:MO,TU,WE,TH,FR,SA,SU',

            // MONTHLY: require by_month_day (1-31). by_month optional (1-12)
            'recurring.*.by_month_day' => 'required_if:recurring.*.frequency,MONTHLY|array|min:1',
            'recurring.*.by_month_day.*' => 'required_with:recurring.*.by_month_day|integer|min:1|max:31',
            'recurring.*.by_month' => 'nullable|array',
            'recurring.*.by_month.*' => 'required_with:recurring.*.by_month|integer|min:1|max:12',

            // YEARLY: require by_month (1-12)
            'recurring.*.by_month' => 'required_if:recurring.*.frequency,YEARLY|array|min:1',
            'recurring.*.by_month.*' => 'required_if:recurring.*.frequency,YEARLY|integer|min:1|max:12',

            'attachments_*.*' => 'nullable|file|max:10240' // 10 MB max
        ];
    }

    public function messages()
    {
        return [
            'start_date.*.required' => 'Tanggal mulai harus diisi.',
            'start_date.*.date' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'end_date.*.required' => 'Tanggal selesai harus diisi.',
            'end_date.*.date' => 'Tanggal selesai harus berupa tanggal yang valid.',
            'end_date.*.after_or_equal' => 'The end date must be a date after or equal to start date.',
            'assignment_user_id.required' => 'penugasan harus diisi.',
            'assignment_user_id.array' => 'penugasan harus berupa array.',
            'assignment_user_id.*.required' => 'Setiap penugasan harus diisi.',
            'assignment_user_id.*.uuid' => 'Setiap penugasan harus berupa UUyang valid.',
            'category_id.required' => 'kategori harus diisi.',
            'category_id.array' => 'kategori harus berupa array.',
            'category_id.*.required' => 'Setiap kategori harus diisi.',
            'category_id.*.uuid' => 'Setiap kategori harus berupa UUyang valid.',
            'type_id.required' => 'tipe harus diisi.',
            'type_id.array' => 'tipe harus berupa array.',
            'type_id.*.required' => 'Setiap tipe harus diisi.',
            'type_id.*.uuid' => 'Setiap tipe harus berupa UUyang valid.',
            'project_id.required' => 'Project harus diisi.',
            'project_id.array' => 'Project harus berupa array.',
            'project_id.*.required' => 'Setiap Project harus diisi.',
            'project_id.*.uuid' => 'Setiap Project harus berupa UUyang valid.',
            'data_project_id.required' => 'Data Project harus diisi.',
            'data_project_id.array' => 'Data Project harus berupa array.',
            'data_project_id.*.required' => 'Setiap Data Project harus diisi.',
            'data_project_id.*.uuid' => 'Setiap Data Project harus berupa UUyang valid.',
            'name.required' => 'Nama harus diisi.',
            'name.array' => 'Nama harus berupa array.',
            'name.*.required' => 'Setiap nama harus diisi.',
            'name.*.string' => 'Setiap nama harus berupa string.',
            'name.*.max' => 'Setiap nama tidak boleh lebih dari 255 karakter.',
            'description.array' => 'Deskripsi harus berupa array.',
            'description.*.string' => 'Setiap deskripsi harus berupa string.',
            'attachments_*.*.file' => 'Attachments harus berupa file.',
            'attachments_*.*.max' => 'Attachments tidak boleh lebih dari 10 MB.',
            'recurring.array' => 'Recurring harus berupa array.',
            'recurring.*.frequency.required' => 'Frequency wajib diisi.',
            'recurring.*.frequency.in' => 'Frequency harus salah satu dari: DAILY, WEEKLY, MONTHLY, YEARLY.',
            'recurring.*.until.required' => 'Tanggal akhir (until) wajib diisi.',
            'recurring.*.until.date' => 'Tanggal akhir (until) tidak valid.',
            
            'recurring.*.by_day.required_if' => 'Field by_day wajib diisi untuk frequency WEEKLY.',
            'recurring.*.by_day.array' => 'by_day harus berupa array.',
            'recurring.*.by_day.*.in' => 'by_day hanya boleh berisi: MO, TU, WE, TH, FR, SA, SU.',
            
            'recurring.*.by_month_day.required_if' => 'Field by_month_day wajib diisi untuk frequency MONTHLY.',
            'recurring.*.by_month_day.array' => 'by_month_day harus berupa array.',
            'recurring.*.by_month_day.*.integer' => 'by_month_day harus berupa angka.',
            'recurring.*.by_month_day.*.min' => 'by_month_day minimal 1.',
            'recurring.*.by_month_day.*.max' => 'by_month_day maksimal 31.',
            
            'recurring.*.by_month.required_if' => 'Field by_month wajib diisi untuk frequency YEARLY.',
            'recurring.*.by_month.array' => 'by_month harus berupa array.',
            'recurring.*.by_month.*.integer' => 'by_month harus berupa angka.',
            'recurring.*.by_month.*.min' => 'by_month minimal 1.',
            'recurring.*.by_month.*.max' => 'by_month maksimal 12.',
        ];
    }
}

