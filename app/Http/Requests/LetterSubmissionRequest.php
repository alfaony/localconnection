<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LetterSubmissionRequest extends FormRequest
{
    
    public function authorize()
    {
        // Mengizinkan semua pengguna untuk mengakses request ini.
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'nullable|uuid|exists:users,id', // UUID bersifat nullable dan harus ada di tabel users
            'start_date' => 'nullable|date', // Tanggal bersifat nullable
            'end_date' => 'nullable|date|after_or_equal:start_date', // Tanggal akhir nullable dan harus >= tanggal mulai
            'letter_type_id' => 'required|uuid|exists:letter_types,id', // UUID harus ada di tabel letter_types
        ];
    }

    public function messages()
    {
        return [
            'letter_type_id.required' => 'Kolom Jenis Surat wajib diisi.',
            'letter_type_id.exists' => 'Kolom Jenis Surat harus ada di tabel letter_types.',
            'letter_type_id.uuid' => 'Kolom Jenis Surat harus berisi UUID yang valid,',
            'user_id.uuid' => 'Kolom User harus berisi UUID yang valid.',
            'user_id.exists' => 'Kolom User harus ada di tabel users.',
            'user_id.uuid' => 'Kolom User harus berisi UUID yang valid.',
            'start_date.date' => 'Kolom Tanggal Mulai harus berupa tanggal yang valid.',
            'end_date.date' => 'Kolom Tanggal Selesai harus berupa tanggal yang valid.',
            'end_date.after_or_equal' => 'Kolom Tanggal Selesai harus sama atau setelah Tanggal Mulai.',
        ];
    }
}
