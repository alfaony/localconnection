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
            'start_date' => 'required|array',
            'start_date.*' => 'required|date',
            'end_date' => 'required|array',
            'end_date.*' => 'required|date|after_or_equal:start_date.*',
            'assignment_user_id' => 'required|array',
            'assignment_user_id.*' => 'required|uuid',
            'category_id' => 'required|array',
            'category_id.*' => 'required|string',
            'type_id' => 'required|array',
            'type_id.*' => 'required|uuid',
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string',
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
            'name.required' => 'Nama harus diisi.',
            'name.array' => 'Nama harus berupa array.',
            'name.*.required' => 'Setiap nama harus diisi.',
            'name.*.string' => 'Setiap nama harus berupa string.',
            'name.*.max' => 'Setiap nama tidak boleh lebih dari 255 karakter.',
            'description.array' => 'Deskripsi harus berupa array.',
            'description.*.string' => 'Setiap deskripsi harus berupa string.',
        ];
    }
}

