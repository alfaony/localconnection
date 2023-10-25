<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgreementLetterRequest extends FormRequest
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
        return [
            'date' => 'required|date',
            'quote' => 'required|uuid|exists:quotes,id', // Asumsi bahwa Anda memiliki tabel quotes dengan kolom uuid
            'payment_term' => 'nullable|string',
            'period_term' => 'nullable|string',
            'other_term' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'date.required' => 'Tanggal diperlukan.',
            'quote.required' => 'Kutipan diperlukan.',
            'quote.exists' => 'Kutipan tidak valid.',
        ];
    }
}
