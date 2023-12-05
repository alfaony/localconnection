<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'npwp_number' => 'required|string|max:255',
            'director' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:15',
            'password' => 'required|string|min:6|confirmed',
        ];

        // Jika request adalah untuk operasi update, hanya izinkan untuk mengupdate 'name'
        if ($this->isMethod('put')) {
            $rules = [
                'company_name' => 'required|string|max:255',
            ];
        }

        // dd($rules);
        return $rules;
    }

    public function messages()
    {
        return [
            'company_name.required' => 'Nama perusahaan harus diisi.',
            'company_name.max' => 'Nama perusahaan tidak boleh lebih dari :max karakter.',
            'name.required' => 'Nama User harus diisi.',
            'name.max' => 'Nama User tidak boleh lebih dari :max karakter.',
            'address.required' => 'Alamat perusahaan harus diisi.',
            'address.max' => 'Alamat perusahaan tidak boleh lebih dari :max karakter.',
            'npwp_number.required' => 'Nomor NPWP harus diisi.',
            'npwp_number.max' => 'Nomor NPWP tidak boleh lebih dari :max karakter.',
            'director.required' => 'Nama direktur harus diisi.',
            'director.max' => 'Nama direktur tidak boleh lebih dari :max karakter.',
            'email.required' => 'Alamat email harus diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan.',
            'phone.required' => 'Nomor telepon harus diisi.',
            'phone.max' => 'Nomor telepon tidak boleh lebih dari :max karakter.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal :min karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ];
    }
}
