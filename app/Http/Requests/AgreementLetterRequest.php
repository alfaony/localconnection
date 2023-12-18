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
            'rent_address'       => 'nullable|string',
            'rent_start_duration' => 'nullable|date',
            'rent_end_duration'   => 'nullable|date|after_or_equal:rent_start_duration',
            'rent_price'         => 'nullable|numeric',
            'commission_name'    => 'nullable|string',
            'commission_phone'   => 'nullable|string',
            'commission_address' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'date.required' => 'Tanggal diperlukan.',
            'quote.required' => 'Kutipan diperlukan.',
            'quote.exists' => 'Kutipan tidak valid.',
            'rent_start_duration.date'     => 'Tanggal awal harus berupa tanggal yang valid.',
            'rent_end_duration.date'       => 'Tanggal akhir harus berupa tanggal yang valid.',
            'rent_end_duration.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
            'rent_price.numeric'           => 'Harga sewa harus berupa angka.',

        ];
    }
}
