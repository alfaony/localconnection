<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Customer;

class CustomerRequest extends FormRequest
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
        $customerId = $this->route('customer'); // Mendapatkan ID customer dari route parameter

        if($customerId)
        {
            $customer = Customer::where('slug',$customerId)->first();

            return [
                'name' => 'required|string|max:255',
                'director' => 'required|string|max:255',
                'pic' => 'required|string|max:255',
                'assignor' => 'required|string|max:255',
                'address' => 'required|string',
                'city' => 'nullable|string',
                'industry' => 'nullable|string',
                'phone' => 'required|regex:/^[0-9]{10,15}$/',
                'email' => 'required|email',
            ];
        }else
        {
            return [
                'name' => 'required|string|max:255',
                'director' => 'required|string|max:255',
                'pic' => 'required|string|max:255',
                'assignor' => 'required|string|max:255',
                'address' => 'required|string',
                'phone' => 'required|regex:/^[0-9]{10,15}$/',
                'email' => 'required|email',
                'city' => 'nullable|string',
                'industry' => 'nullable|string',
            ];
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'director.required' => 'Direktur wajib diisi.',
            'pic.required' => 'PIC wajib diisi.',
            'assignor.required' => 'Penugasan wajib diisi.',
            'address.required' => 'Alamat wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
        ];
    }
}
