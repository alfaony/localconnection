<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return 
        [
            'work_order' => 'required|uuid|exists:work_orders,id',
            'title' => 'required|string|max:255',
            'budget' => 'nullable|integer|min:0',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'work_order.required' => 'Proyek wajib diisi.',

            'title.required' => 'Judul proyek wajib diisi.',
            'title.string' => 'Judul proyek harus berupa teks.',
            'title.max' => 'Judul proyek tidak boleh melebihi 255 karakter.',

            'budget.required' => 'Anggaran proyek wajib diisi.',
            'budget.integer' => 'Anggaran proyek harus berupa angka bulat.',
            'budget.min' => 'Anggaran proyek tidak boleh kurang dari 0.',

            'start_date.required' => 'Tanggal mulai proyek wajib diisi.',
            'start_date.date' => 'Tanggal mulai proyek harus berupa format tanggal yang valid.',
            'start_date.before_or_equal' => 'Tanggal mulai proyek harus sebelum atau sama dengan tanggal selesai.',

            'end_date.required' => 'Tanggal selesai proyek wajib diisi.',
            'end_date.date' => 'Tanggal selesai proyek harus berupa format tanggal yang valid.',
            'end_date.after_or_equal' => 'Tanggal selesai proyek harus setelah atau sama dengan tanggal mulai.',

            'description.string' => 'Deskripsi proyek harus berupa teks.'
        ];
    }
}
