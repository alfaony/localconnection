<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            // 'phone' => ['nullable','regex:/^(\+62|0|62)[0-9]{9,13}$/'],
            'salary_monthly' => 'required|integer|min:0',
            'salary_daily' => 'required|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama harus diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama tidak boleh lebih dari :max karakter.',
            // 'phone.required' => 'Nomor telepon harus diisi.',
            // 'phone.regex' => 'Nomor telepon harus berupa nomor Indonesia yang valid.',
            'salary_monthly.required' => 'Gaji bulanan harus diisi.',
            'salary_monthly.integer' => 'Gaji bulanan harus berupa angka.',
            'salary_monthly.min' => 'Gaji bulanan tidak boleh kurang dari :min.',
            'salary_daily.required' => 'Gaji harian harus diisi.',
            'salary_daily.integer' => 'Gaji harian harus berupa angka.',
            'salary_daily.min' => 'Gaji harian tidak boleh kurang dari :min.',
        ];
    }
}
