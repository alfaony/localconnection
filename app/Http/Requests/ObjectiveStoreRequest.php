<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ObjectiveStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function rules()
    {
        return [
            'objective_name' => 'required|array',
            'objective_name.*' => 'required|string|max:190',
            'division_id' => 'required|array',
            'division_id.*' => 'required|array',
            'division_id.*.*' => 'required|uuid|exists:divisions,id',
            'mission_id' => 'required|array',
            'mission_id.*' => 'required|uuid|exists:missions,id',
            'start_date_objective' => 'nullable|array',
            'start_date_objective.*' => 'nullable|date',
            'end_date_objective' => 'nullable|array',
            'end_date_objective.*' => 'nullable|date|after_or_equal:start_date_objective.*',
            'key_result' => 'required|array',
            'key_result.*' => 'required|array',
            'key_result.*.*' => 'required|string|max:190',
            'start_date' => 'nullable|array',
            'start_date.*' => 'nullable|array',
            'start_date.*.*' => 'nullable|date',
            'end_date' => 'nullable|array',
            'end_date.*' => 'nullable|array',
            'end_date.*.*' => 'nullable|date|after_or_equal:start_date.*.*',
        ];
    }

    public function messages()
    {
        return [
            'objective_name.required' => 'Nama objective harus diisi.',
            'objective_name.*.required' => 'Setiap nama objective harus diisi.',
            'objective_name.*.string' => 'Nama objective harus berupa string.',
            'objective_name.*.max' => 'Nama objective maksimal 190 karakter.',
            'division_id.required' => 'Divisi harus diisi.',
            'division_id.*.required' => 'Setiap divisi harus diisi.',
            'division_id.*.*.required' => 'Setiap divisi harus diisi.',
            'division_id.*.*.uuid' => 'ID divisi harus berupa UUID.',
            'division_id.*.*.exists' => 'Divisi tidak ditemukan.',
            'mission_id.required' => 'Misi harus diisi.',
            'mission_id.*.required' => 'Setiap misi harus diisi.',
            'mission_id.*.uuid' => 'ID misi harus berupa UUID.',
            'mission_id.*.exists' => 'Misi tidak ditemukan.',
            'start_date_objective.*.date' => 'Tanggal mulai objective harus berupa tanggal yang valid.',
            'end_date_objective.*.date' => 'Tanggal akhir objective harus berupa tanggal yang valid.',
            'end_date_objective.*.after_or_equal' => 'Tanggal akhir objective harus setelah atau sama dengan tanggal mulai.',
            'key_result.required' => 'Key result harus diisi.',
            'key_result.*.required' => 'Setiap key result harus diisi.',
            'key_result.*.*.required' => 'Setiap key result harus diisi.',
            'key_result.*.*.string' => 'Key result harus berupa string.',
            'key_result.*.*.max' => 'Key result maksimal 190 karakter.',
            'start_date.*.*.date' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'end_date.*.*.date' => 'Tanggal akhir harus berupa tanggal yang valid.',
            'end_date.*.*.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
        ];
    }
}
