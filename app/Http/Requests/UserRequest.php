<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MatchOldPassword;
use App\Models\User;

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
        $userId = $this->route('user'); // Mendapatkan ID customer dari route parameter

        if(!$userId)
        {
            return [
                'ip_addresses.*' => 'nullable|ip',
                'name'  =>  'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'role' => 'required|uuid|exists:roles,id',
                'phone' => ['nullable','regex:/^(\+62|0|62)[0-9]{9,13}$/'],
                'password' => 'required|min:6',
                'confirmPassword' => 'required|same:password',
                'is_checkin' => 'nullable|boolean',
                'manual_checkin' => 'nullable|boolean',
                'requires_photo' => 'nullable|boolean',
                'requires_location' => 'nullable|boolean',
                'start_time' => 'required_if:is_checkin,1|nullable|date_format:H:i',
                'end_time' => 'required_if:is_checkin,1|nullable|date_format:H:i|after:start_time',
                'rest_time' => 'required_if:is_checkin,1|nullable|date_format:H:i',
                'end_rest_time' => 'required_if:is_checkin,1|nullable|date_format:H:i|after:rest_time',
                'custom_rest_times' => 'nullable|array',
                'custom_rest_times.*.start' => 'nullable|date_format:H:i',
                'custom_rest_times.*.end' => 'nullable|date_format:H:i|after:custom_rest_times.*.start',
                'company_access' => 'nullable|array',
                'company_access.*' => 'required|uuid|exists:companies,id',
            ];
        }else
        {
            // $user = User::where('slug',$userId)->first();
            $rules = [
                'ip_addresses.*' => 'nullable|ip',
                'name'  =>  'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $userId->id,
                'phone' => ['nullable','regex:/^(\+62|0|62)[0-9]{9,13}$/'],
            ];   

            if ($this->filled('oldPassword')) 
            {
                $rules['oldPassword'] = ['required',new MatchOldPassword];
                $rules['newPassword'] = 'required|min:6';
                $rules['confirmPassword'] = 'required|same:newPassword';
            }

            return $rules;
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'newPassword.required' => 'Password baru harus diisi.',
            'newPassword.min' => 'Password baru minimal 6 karakter.',
            'confirmPassword.required' => 'Konfirmasi password harus diisi.',
            'confirmPassword.same' => 'Konfirmasi password harus sama dengan password.',

            'is_checkin.boolean' => 'Status check-in harus berupa nilai boolean.',
            'manual_checkin.boolean' => 'Status check-in manual harus berupa nilai boolean.',
            'requires_photo.boolean' => 'Status membutuhkan foto check-in harus berupa nilai boolean.',
            'requires_location.boolean' => 'Status membutuhkan lokasi check-in harus berupa nilai boolean.',
            'start_time.required_if' => 'Jam mulai harus diisi jika check-in diaktifkan.',
            'start_time.date_format' => 'Format jam mulai tidak valid. Gunakan format HH:mm.',
            'end_time.required_if' => 'Jam selesai harus diisi jika check-in diaktifkan.',
            'end_time.date_format' => 'Format jam selesai tidak valid. Gunakan format HH:mm.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
            'rest_time.required_if' => 'Waktu istirahat harus diisi jika check-in diaktifkan.',
            'rest_time.date_format' => 'Format waktu istirahat tidak valid. Gunakan format HH:mm.',
            'end_rest_time.required_if' => 'Waktu istirahat selesai harus diisi jika check-in diaktifkan.',
            'end_rest_time.date_format' => 'Format waktu istirahat selesai tidak valid. Gunakan format HH:mm.',
            'end_rest_time.after' => 'Waktu istirahat selesai harus setelah waktu istirahat mulai.',
            'custom_rest_times.*.start.date_format' => 'Format jam mulai tidak valid. Gunakan format HH:mm.',
            'custom_rest_times.*.end.date_format' => 'Format jam selesai tidak valid. Gunakan format HH:mm.',
            'custom_rest_times.*.end.after' => 'Jam selesai harus setelah jam mulai.',
            'ip_addresses.*.ip' => 'Alamat IP tidak valid.',
        ];
    }
}
