<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function rules()
    {
        return [
            'project' => 'required|string|uuid',
            'name' => 'required|string|max:255',
            'phone' => ['nullable','regex:/^(\+62|0|62)[0-9]{9,13}$/'],
            'date' => 'required|date',
            'description' => 'required|array',
            'description.*' => 'required|string|max:255',
            'price.*' => 'required|numeric|min:0',
            'qty.*' => 'required|integer|min:0',
            'sub_total.*' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'project.required' => 'Project wajib diisi.',
            'project.string' => 'Project harus berupa string.',
            'project.uuid' => 'Project harus berupa format UUID yang valid.',
            
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa string.',
            'name.max' => 'Nama tidak boleh melebihi 255 karakter.',
            
            'phone.regex' => 'Nomor telepon harus berupa nomor Indonesia yang valid.',

            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Tanggal harus berupa tanggal yang valid.',
            
            'description.*.required' => 'Deskripsi wajib diisi untuk setiap item.',
            'description.*.string' => 'Deskripsi harus berupa string untuk setiap item.',
            'description.*.max' => 'Deskripsi untuk setiap item tidak boleh melebihi 255 karakter.',
            
            'price.*.required' => 'Harga wajib diisi untuk setiap item.',
            'price.*.numeric' => 'Harga untuk setiap item harus berupa angka.',
            'price.*.min' => 'Harga untuk setiap item tidak boleh kurang dari 0.',
            
            'qty.*.required' => 'Kuantitas wajib diisi untuk setiap item.',
            'qty.*.integer' => 'Kuantitas untuk setiap item harus berupa bilangan bulat.',
            'qty.*.min' => 'Kuantitas untuk setiap item tidak boleh kurang dari 0.',
            
            'sub_total.*.required' => 'Sub total wajib diisi untuk setiap item.',
            'sub_total.*.numeric' => 'Sub total untuk setiap item harus berupa angka.',
            'sub_total.*.min' => 'Sub total untuk setiap item tidak boleh kurang dari 0.',
            
            'total.required' => 'Total wajib diisi.',
            'total.numeric' => 'Total harus berupa angka.',
            'total.min' => 'Total tidak boleh kurang dari 0.',
        ];
    }

}
