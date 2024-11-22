<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PassCheckingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Ubah menjadi true jika semua pengguna diizinkan
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->isMethod('POST')) 
        {
            $rules = [
                'date' => 'required|date|after_or_equal:today',
            ];
        } else {
            $rules = [
                'date' => 'required|date',
            ];
        }

        return array_merge($rules, [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'pictures.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'note' => 'nullable|string',
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Tanggal harus dalam format yang benar.',
            'date.after_or_equal' => 'Tanggal harus setidaknya hari ini.',
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'start_time.date_format' => 'Waktu mulai harus dalam format HH:mm.',
            'end_time.required' => 'Waktu selesai wajib diisi.',
            'end_time.date_format' => 'Waktu selesai harus dalam format HH:mm.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'pictures.*.image' => 'File harus berupa gambar.',
            'pictures.*.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif.',
            'pictures.*.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
            'remove_pictures.*.url' => 'URL gambar yang akan dihapus tidak valid.',
        ];
    }
}
