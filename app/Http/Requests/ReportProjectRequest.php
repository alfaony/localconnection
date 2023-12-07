<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportProjectRequest extends FormRequest
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
        $rules = 
        [
            'date' => 'required|date',
            'work_order' => 'required|string',
            'project' => 'required|string',
            'name' => 'required|array',
            'link' => 'required|array',
            'file' => 'required|array',
            'name.*' => 'required|string',
            'link.*' => 'required|url',
            'file.*' => 'required|file|max:5000', // Contoh validasi untuk file PDF dengan maksimum 5MB.
        ];

        if ($this->isMethod('patch')) 
        {
            $rules = 
            [
                'file' => 'nullable|array',
                'date' => 'required|date',
                'work_order' => 'required|string',
                'project' => 'required|string',
                'link.*' => 'required|url',
                'file.*' => 'nullable|file|max:5000', // Contoh validasi untuk file PDF dengan maksimum 5MB.
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'date.required' => 'Tanggal diperlukan.',
            'date.date' => 'Format tanggal tidak valid.',
            
            'work_order.required' => 'Nomor Work Order diperlukan.',
            'work_order.string' => 'Nomor Work Order harus dalam format teks.',
            
            'project.required' => 'Proyek diperlukan.',
            'project.string' => 'Proyek harus dalam format teks.',
            
            'link.required' => 'Link laporan diperlukan.',
            'link.url' => 'Format link laporan tidak valid.',
            
            'file.required' => 'File laporan diperlukan.',
            'file.file' => 'Mohon unggah file yang valid.',
            'file.max' => 'Ukuran file maksimal adalah 5MB.',
        ];
    }

}
