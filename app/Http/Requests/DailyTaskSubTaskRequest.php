<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DailyTaskSubTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'assignment_user_id' => 'nullable|uuid',
            'child_daily_task_id' => 'nullable|uuid',
            'category_id' => 'nullable|string',
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

