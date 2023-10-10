<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'email' => 'required|email|unique:users,email',
            'phone' => ['nullable','regex:/^(\+62|0|62)[0-9]{9,13}$/'],
            'password' => 'required|min:6',
            'confirmPassword' => 'required|same:password',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'confirmPassword.required' => 'Konfirmasi password harus diisi.',
            'confirmPassword.same' => 'Konfirmasi password harus sama dengan password.',
        ];
    }
}
