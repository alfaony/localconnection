<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManagerRequest extends FormRequest
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

   public function rules()
    {
        return 
        [
            'date' => 'required|date',
            'project' => 'required|uuid|exists:projects,id',
            'name' => 'required|string|max:255',
            'phone' => ['nullable','regex:/^(\+62|0|62)[0-9]{9,13}$/'],
            'payment_method.*' => ['required', Rule::in(['daily','monthly'])],
            'start_date.*' => 'required|date',
            'end_date.*' => 'required|date|after_or_equal:start_date.*',
            'total.*' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'date.required' => 'Tanggal wajib diisi.',
            'project.required' => 'Proyek wajib diisi.',
            'name.required' => 'Nama wajib diisi.',
            'phone.required' => 'No. Telepon wajib diisi.',
            'payment_method.*.required' => 'Metode pembayaran wajib diisi.',
            'start_date.*.required' => 'Tanggal mulai wajib diisi pada setiap baris.',
            'end_date.*.required' => 'Tanggal selesai wajib diisi pada setiap baris.',
            // 'work_time.*.required' => 'Waktu kerja wajib diisi pada setiap baris.',
            'total.*.required' => 'Total wajib diisi pada setiap baris.',
            // 'total_all.required' => 'Total keseluruhan wajib diisi.'
        ];
    }
}
